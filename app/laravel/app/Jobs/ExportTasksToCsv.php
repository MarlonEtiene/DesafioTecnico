<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\Task;
use App\Notifications\ExportReadyNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ExportTasksToCsv implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;
    protected $filters;

    public function __construct(User $user, array $filters = [])
    {
        $this->user = $user;
        $this->filters = $filters;
    }

    public function handle()
    {
        Log::info('Iniciando exportação de tasks para o usuário: ' . $this->user->id);

        try {
            $query = Task::where('company_id', $this->user->company_id);

            if (!empty($this->filters['status'])) {
                $query->where('status', $this->filters['status']);
            }

            if (!empty($this->filters['priority'])) {
                $query->where('priority', $this->filters['priority']);
            }

            Log::info('Consulta SQL para exportação de tasks: ' . $query->toSql());

            $tasks = $query->get();

            // Gera o conteúdo do CSV
            $csvData = "Título,Descrição,Status,Prioridade,Data de Vencimento\n";
            foreach ($tasks as $task) {
                $csvData .= "{$task->title},\"{$task->description}\",{$task->status},{$task->priority},{$task->due_date}\n";
            }
            $fileName = 'tasks-' . now()->format('Y-m-d-H-i-s') . '.csv';
            $filePath = 'exports/' . $this->user->company_id . '/' . $this->user->id . '/' . $fileName;

            Storage::disk('local')->put($filePath, $csvData);

            $this->user->notify(new ExportReadyNotification($filePath));

            Log::info('Exportação de tasks concluída para o usuário: ' . $this->user->id);
        } catch (\Exception $e) {
            Log::error('Erro na exportação de tasks: ' . $e->getMessage());
        }
    }
}
