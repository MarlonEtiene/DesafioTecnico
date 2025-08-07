<?php

namespace App\Jobs;

use App\Models\Task;
use App\Notifications\TaskCreated;
use App\Notifications\TaskCompleted;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendTaskNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $task;
    protected $type;

    public function __construct(Task $task, $type)
    {
        $this->task = $task;
        $this->type = $type;
    }

    public function handle()
    {
        $user = $this->task->user;

        if ($this->type === 'created') {
            $user->notify(new TaskCreated($this->task));
        } elseif ($this->type === 'completed') {
            $user->notify(new TaskCompleted($this->task));
        }
    }
}
