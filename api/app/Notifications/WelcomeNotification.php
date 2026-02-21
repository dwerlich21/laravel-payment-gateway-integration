<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Password;

class WelcomeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $token = Password::createToken($notifiable);
        $resetUrl = env('APP_URL', 'http://localhost:8080/') . 'recuperar-senha?token=' . $token . '&email=' . urlencode($notifiable->email);

        return (new MailMessage)
            ->subject('Bem-vindo ao Sistema - Defina sua senha')
            ->greeting('Olá, ' . $notifiable->name . '!')
            ->line('Seja bem-vindo(a) ao sistema de ' . config('app.name') . '.')
            ->line('Sua conta foi criada com sucesso. Para acessar o sistema, você precisa definir uma senha.')
            ->action('Definir Senha', $resetUrl)
            ->line('Este link de redefinição de senha expirará em 60 minutos.')
            ->line('Se você não solicitou a criação desta conta, nenhuma ação adicional é necessária.')
            ->salutation('Atenciosamente, ' . config('app.name'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'message' => 'Bem-vindo ao sistema',
        ];
    }
}
