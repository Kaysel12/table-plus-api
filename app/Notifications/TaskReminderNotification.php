<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\Task;

class TaskReminderNotification extends Notification
{
    public function __construct(public Task $task) {}

    public function via($notifiable)
    {
        // Solo enviar por email
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Recordatorio de Tarea')
            ->line("Título: {$this->task->title}")
            ->line("Descripción: {$this->task->description}")
            ->line("Fecha de ejecución: {$this->task->reminder_time}")
            ->action('Ver tarea', url("/tasks/{$this->task->id}"));
    }
}