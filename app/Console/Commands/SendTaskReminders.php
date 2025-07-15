<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Task;
use App\Notifications\TaskReminderNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SendTaskReminders extends Command
{
    protected $signature = 'tasks:send-reminders';
    protected $description = 'Envía notificaciones a los usuarios de tareas próximas a vencerse';

    public function handle()
    {
        $now = Carbon::now();
        
        // AGREGAR ESTAS LÍNEAS PARA DEBUG
        $this->info("=== INICIANDO PROCESO DE RECORDATORIOS ===");
        $this->info("Hora actual: " . $now->format('Y-m-d H:i:s'));
        
        // Contar todas las tareas con reminder_time
        $allTasksWithReminder = Task::whereNotNull('reminder_time')->count();
        $this->info("Total de tareas con reminder_time: " . $allTasksWithReminder);
        
        // Contar tareas cuyo reminder_time ya pasó
        $tasksTimeReached = Task::whereNotNull('reminder_time')
            ->where('reminder_time', '<=', $now)
            ->count();
        $this->info("Tareas cuyo reminder_time ya llegó: " . $tasksTimeReached);
        
        // Contar tareas que no han sido enviadas
        $tasksNotSent = Task::whereNotNull('reminder_time')
            ->where('reminder_time', '<=', $now)
            ->where('reminder_sent', false)
            ->count();
        $this->info("Tareas pendientes de envío: " . $tasksNotSent);

        $tasks = Task::whereNotNull('reminder_time')
            ->where('reminder_time', '<=', $now)
            ->where('reminder_sent', false)
            ->with('user')
            ->get();

        $this->info("Tareas encontradas para procesar: " . $tasks->count());
        Log::info("Tareas encontradas para procesar: " . $tasks->count());

        if ($tasks->count() === 0) {
            $this->info("No hay tareas para enviar recordatorios en este momento.");
            Log::info("No hay tareas para enviar recordatorios en este momento.");
            return;
        }

        foreach ($tasks as $task) {
            try {
                $this->info("Procesando tarea ID: {$task->id} - Reminder: {$task->reminder_time}");
                
                $task->user->notify(new TaskReminderNotification($task));

                $task->update(['reminder_sent' => true]);

                $this->info("✓ Recordatorio enviado para la tarea ID: {$task->id}");
                Log::info("✓ Recordatorio enviado para la tarea ID: {$task->id}");
            } catch (\Throwable $e) {
                $this->error("✗ Error enviando notificación de tarea {$task->id}: {$e->getMessage()}");
                Log::error("Error enviando notificación de tarea {$task->id}: {$e->getMessage()}");
            }
        }
        
        $this->info("=== PROCESO COMPLETADO ===");
    }
}