<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Crypt;

class ExportReadyNotification extends Notification
{
    use Queueable;

    protected $filePath;

    public function __construct(string $filePath)
    {
        $this->filePath = $filePath;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        $encryptedFilePath = Crypt::encryptString($this->filePath);

        $url = URL::temporarySignedRoute(
            'export.download', now()->addMinutes(60), [
                'filePath' => $encryptedFilePath,
            ]
        );

        return (new MailMessage)
            ->subject('Sua exportação de tarefas está pronta!')
            ->greeting('Olá, ' . $notifiable->name)
            ->line('Sua exportação de tarefas foi concluída com sucesso.')
            ->action('Baixar o arquivo', $url)
            ->line('O link de download expirará em 60 minutos.')
            ->line('Obrigado por usar a nossa aplicação!');
    }

    public function toDatabase($notifiable)
    {
        $encryptedFilePath = Crypt::encryptString($this->filePath);

        $url = URL::temporarySignedRoute(
            'export.download', now()->addMinutes(60), [
                'filePath' => $encryptedFilePath,
            ]
        );
        return [
            'message' => 'Sua exportação de tarefas está pronta!',
            'url' => $url,
        ];
    }
}
