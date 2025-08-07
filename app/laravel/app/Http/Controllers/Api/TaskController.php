<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\ExportTasksToCsv;
use App\Jobs\SendTaskNotification;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Validator;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        $query = Task::with('user');

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        if ($request->has('priority')) {
            $query->where('priority', $request->priority);
        }

        $tasks = $query
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json($tasks);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255|min:5',
            'description' => 'nullable|string',
            'status' => 'required|in:pending,in_progress,completed',
            'priority' => 'required|in:low,medium,high',
            'due_date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = auth()->user();

        $task = Task::create([
            'title' => $request->title,
            'description' => $request->description,
            'status' => $request->status,
            'priority' => $request->priority,
            'due_date' => $request->due_date,
            'company_id' => $user->company_id,
            'user_id' => $user->id,
        ]);

        dispatch(new SendTaskNotification($task, 'created'));
        return response()->json($task->load('user'), 201);
    }

    public function show($id)
    {
        $user = auth()->user();

        $task = Task::with('user')
            ->findOrFail($id);

        return response()->json($task);
    }

    public function update(Request $request, $id)
    {
        $user = auth()->user();
        $task = Task::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|min:5|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:pending,in_progress,completed',
            'priority' => 'required|in:low,medium,high',
            'due_date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $oldStatus = $task->status;

        $task->update($request->all());

        if ($oldStatus !== 'completed' && $request->status === 'completed') {
            dispatch(new SendTaskNotification($task, 'completed'));
        }

        return response()->json($task->load('user'));
    }

    public function destroy($id)
    {
        $user = auth()->user();

        $task = Task::findOrFail($id);

        $task->delete();

        return response()->json(['message' => 'Tarefa excluída com sucesso.']);
    }

    public function complete($id)
    {
        $user = auth()->user();

        $task = Task::findOrFail($id);

        $task->update(['status' => 'completed']);
        dispatch(new SendTaskNotification($task, 'completed'));

        return response()->json($task->load('user'));
    }

    public function export(Request $request)
    {
        $filters = $request->only(['status', 'priority']);
        ExportTasksToCsv::dispatch(auth()->user(), $filters);
        return response()->json(['message' => 'Exportação iniciada com sucesso. Você será notificado por e-mail quando estiver pronta!']);
    }

    public function downloadExport(Request $request)
    {
        Log::info('Requisição de download recebida.');

        $encryptedFilePath = $request->input('filePath');
        Log::info('Caminho encriptado recebido: ' . $encryptedFilePath);

        try {
            $filePath = Crypt::decryptString($encryptedFilePath);
            Log::info('Caminho decriptado com sucesso: ' . $filePath);
        } catch (\Exception $e) {
            Log::error('Falha na decriptação: ' . $e->getMessage());
            abort(404, 'Link inválido ou expirado.');
        }

        if (!Storage::disk('local')->exists($filePath)) {
            Log::error('Arquivo não encontrado no caminho: ' . $filePath);
            abort(404, 'Arquivo não encontrado.');
        }
        Log::info('Arquivo encontrado! Iniciando download.');

        return Storage::download($filePath);
    }
}
