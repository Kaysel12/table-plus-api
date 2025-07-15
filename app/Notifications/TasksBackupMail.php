<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class TasksBackupMail extends Notification
{
    public $xmlContent;

    public function __construct($xmlContent)
    {
        $this->xmlContent = $xmlContent;
    }

    public function build()
    {
        return (new MailMessage)
            ->subject('Backup de tus tareas')
                ->view('emails.tasks_backup')
                ->attachData($this->xmlContent, 'tasks_backup.xml', [
                    'mime' => 'application/xml',
                ]);
    }
}
