<?php

namespace App\Services;

use App\DTO\Task\CreateTaskDto;
use App\DTO\Task\UpdateTaskDto;
use App\Exceptions\TaskException;
use App\Http\Requests\IndexTaskRequest;
use App\Models\Task;
use App\Notifications\TasksBackupMail;
use App\Utils\ApiResponse;
use App\Utils\CacheHelper;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;

class TaskService
{
    public function create(CreateTaskDto $dto)
    {
        try {
            $taskData = $dto->toArray();
            $taskData['user_id'] = auth()->guard()->id();
            
            if ($dto->image instanceof \Illuminate\Http\UploadedFile) {
                $this->validateImage($dto->image);
                $imagePath = $dto->image->store('tasks', 'public');
                $taskData['image'] = $imagePath;
            }

            $task = Task::create($taskData);

            // Caché
            CacheHelper::clearTaskCacheForUser($taskData['user_id']);

            if (!$task) {
                Log::info('Hay un error al momento de crear la tarea');
                throw TaskException::creationFailed('No se pudo guardar en la base de datos.');
            }
            
            Log::info('Se creó la tarea exitosamente');
            return ApiResponse::success($task, 'Tarea creada correctamente', 201);
        } catch (TaskException $e) {
            return ApiResponse::error($e->getMessage(), 422);
            Log::error('Error al crear tarea', ['error' => $e->getMessage()]);
        } catch (Exception $e) {
            return ApiResponse::error('Error al crear la tarea', 500, [
                'exception' => $e->getMessage(),
            ]);
            Log::error('Error al crear tarea', ['error' => $e->getMessage()]);
        }
    }

    // private function validateImage(\Illuminate\Http\UploadedFile $image)
    // {
    //     $maxSize = 2 * 1024 * 1024;
    //     if ($image->getSize() > $maxSize) {
    //         $sizeInMB = round($image->getSize() / (1024 * 1024), 2);
    //         throw TaskException::imageTooLarge($sizeInMB);
    //     }

    //     $allowedMimes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
    //     if (!in_array($image->getMimeType(), $allowedMimes)) {
    //         throw TaskException::invalidImageFormat($image->getMimeType());
    //     }
    // }

    private function validateImage(UploadedFile $image)
    {
        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'application/pdf'];
        $maxSizeMB = 2;
        $sizeInMB = $image->getSize() / 1024 / 1024;

        if (!in_array($image->getMimeType(), $allowedMimeTypes)) {
            throw TaskException::invalidImageFormat($image->getMimeType());
            Log::error('Error: Imagen de un tipo incorrecto.');
        }

        if ($sizeInMB > $maxSizeMB) {
            throw TaskException::imageTooLarge($sizeInMB);
            Log::error('Error: Imagen muy larga.');
        }
    }
    
    public function getAll()
    {
        try {
            $userId = auth()->guard()->id();

            $tasks = Cache::remember(
                "tasks_all_user_{$userId}",
                now()->addMinutes(5),
                function () use ($userId) {
                    return Task::where('user_id', $userId)->get();
                }
            );

            return ApiResponse::success($tasks, 'Lista de tareas (cacheada)');
        } catch (Exception $e) {
            return ApiResponse::error('Error al obtener tareas', 500, [
                'exception' => $e->getMessage(),
            ]);
        }
    }

    // public function getAllPaginated(int $perPage = 10, int $page = 1)
    // {
    //     try {
    //         $tasks = Task::orderBy('id', 'asc')
    //                     ->paginate($perPage, ['*'], 'page', $page);

    //         return ApiResponse::success([
    //             'data' => $tasks->items(),
    //             'total' => $tasks->total(),
    //         ], 'Lista paginada de tareas');

    //     } catch (Exception $e) {
    //         return ApiResponse::error('Error al obtener tareas', 500, [
    //             'exception' => $e->getMessage(),
    //         ]);
    //     }
    // }

    public function getAllPaginated(IndexTaskRequest $request)
    {
        try {
            $userId = auth()->guard()->id();
            $filters = [
                'title' => $request->input('title'),
                'description' => $request->input('description'),
                'status' => $request->input('status'),
                'deleted' => $request->boolean('deleted'),
                'created_at' => $request->input('created_at'),
                'perPage' => $request->query('perPage', 10),
                'page' => $request->query('page', 1),
            ];

            $cacheKey = 'tasks_paginated_user_' . $userId . '_' . md5(json_encode($filters));

            $tasks = Cache::remember($cacheKey, now()->addMinutes(5), function () use ($request, $userId) {
                $query = Task::where('user_id', $userId);

                if ($request->filled('title')) {
                    $query->where('title', 'like', '%' . $request->input('title') . '%');
                }

                if ($request->filled('description')) {
                    $query->where('description', 'like', '%' . $request->input('description') . '%');
                }

                if ($request->filled('status')) {
                    $query->where('status', $request->input('status'));
                }

                if ($request->boolean('deleted')) {
                    $query->onlyTrashed();
                }

                if ($request->filled('created_at')) {
                    $query->whereDate('created_at', $request->input('created_at'));
                }

                $perPage = $request->query('perPage', 10);
                $page = $request->query('page', 1);

                return $query->orderBy('id', 'asc')->paginate($perPage, ['*'], 'page', $page);
            });

            return ApiResponse::success([
                'data' => $tasks->items(),
                'total' => $tasks->total(),
            ], 'Lista paginada de tareas (cacheada)');
        } catch (\Exception $e) {
            return ApiResponse::error('Error al obtener tareas', 500, [
                'exception' => $e->getMessage(),
            ]);
        }
    }

    public function update(int $id, UpdateTaskDto $dto)
    {
        try {
            $userId = auth()->guard()->id();
            $task = Task::where('id', $id)
            ->where('user_id', $userId)
            ->first();

            if (!$task) {
                throw TaskException::notFound($id);
            }

            $task->update($dto->toArray());

            // Caché
            CacheHelper::clearTaskCacheForUser($userId);

            return ApiResponse::success($task, 'Tarea actualizada correctamente');
        } catch (TaskException $e) {
            return ApiResponse::error($e->getMessage(), 404);
        } catch (\Exception $e) {
            return ApiResponse::error('Error al actualizar la tarea', 500, [
                'exception' => $e->getMessage(),
            ]);
        }
    }

    public function delete(int $id)
    {
        try {
            $userId = auth()->guard()->id();
            $task = Task::where('id', $id)
                    ->where('user_id', $userId)
                    ->first();

            if (!$task) {
                throw TaskException::notFound($id);
            }

            $task->update(["deleted"=>true]);

            // Caché
            CacheHelper::clearTaskCacheForUser($userId);

            if (!$task->delete()) {
                throw TaskException::deleteFailed($id);
            }

            return ApiResponse::success($task, 'Tarea eliminada correctamente');
        } catch (TaskException $e) {
            return ApiResponse::error($e->getMessage(), 404);
        } catch (\Exception $e) {
            return ApiResponse::error('Error al eliminar la tarea', 500, [
                'exception' => $e->getMessage(),
            ]);
        }
    }

    public function exportXml()
    {
        $userId = auth()->guard()->id();
        $tasks = Task::where('user_id', $userId)->get();

        $xml = new \SimpleXMLElement('<tasks/>');

        foreach ($tasks as $task) {
            $taskNode = $xml->addChild('task');
            $taskNode->addChild('id', $task->id);
            $taskNode->addChild('title', htmlspecialchars($task->title));
            $taskNode->addChild('description', htmlspecialchars($task->description));
            $taskNode->addChild('status', $task->status);
            $taskNode->addChild('created_at', $task->created_at->toIso8601String());
            $taskNode->addChild('updated_at', $task->updated_at->toIso8601String());
        }

        return response($xml->asXML(), 200)
            ->header('Content-Type', 'application/xml')
            ->header('Content-Disposition', 'attachment; filename="tasks_backup.xml"');
    }

    public function restoreFromXml(Request $request)
    {  
        $request->validate([
            'file' => 'required|file|mimes:xml',
        ]);

        $xmlContent = file_get_contents($request->file('file')->getRealPath());

        $xml = simplexml_load_string($xmlContent);

        if (!$xml) {
            return ApiResponse::error('Archivo XML inválido', 400);
        }

        $userId = auth()->guard()->id();

        foreach ($xml->task as $taskNode) {
            $taskData = [
                'title' => (string) $taskNode->title,
                'description' => (string) $taskNode->description,
                'status' => (string) $taskNode->status,
                'created_at' => (string) $taskNode->created_at,
                'updated_at' => (string) $taskNode->updated_at,
                'user_id' => $userId,
            ];

            Task::updateOrCreate(
                [
                    'user_id' => $userId,
                    'title' => $taskData['title'],
                ],
                $taskData
            );
        }

        return ApiResponse::success(null, 'Tareas restauradas correctamente');
    }

}
