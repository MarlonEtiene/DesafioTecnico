<?php
namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskCreated extends Notification implements ShouldQueue
{
    use Queueable;

    protected $task;

    public function __construct(Task $task)
    {
        $this->task = $task;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $companyName = $notifiable->company->name ?? config('app.name');

        return (new MailMessage)
            ->subject('Nova Tarefa Criada')
            ->line('Uma nova tarefa foi criada.')
            ->line('ID da Tarefa: ' . $this->task->id)
            ->line('Título da Tarefa: ' . $this->task->title)
            ->line('Obrigado por usar nossa aplicação!')
            ->salutation('Atenciosamente, Equipe ' . $companyName);
    }
}
