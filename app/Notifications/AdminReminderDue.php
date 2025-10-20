<?php

namespace App\Notifications;

use App\Models\AdminReminder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminReminderDue extends Notification
{
    use Queueable;

    private $frequencyMap = [
        'daily' => 'Diaria',
        'weekly' => 'Semanal',
        'monthly' => 'Mensual',
        'quarterly' => 'Trimestral',
        'yearly' => 'Anual',
        'custom' => 'Personalizada',
    ];

    public function __construct(public AdminReminder $reminder){}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $frequency = $this->frequencyMap[$this->reminder->frequency] ?? $this->reminder->frequency;

        return (new MailMessage)
            ->subject('Recordatorio: '.$this->reminder->reminder_type)
            ->greeting('Hola '.$notifiable->name)
            ->line('Tienes un recordatorio pendiente:')
            ->line('Tipo: '.$this->reminder->reminder_type)
            ->line('Frecuencia: '.$frequency)
            ->line('Detalles:')
            ->line($this->reminder->task_details)
            ->line('Enviado automaticamente por el sistema de recordatorios.');
    }
}
