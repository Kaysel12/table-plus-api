<?php

namespace App\Http\Controllers;

use App\DTO\Task\CreateTaskDto;
use App\DTO\Task\UpdateTaskDto;
use App\Http\Requests\IndexTaskRequest;
use App\Http\Requests\StoreTaskRequest;
use App\Services\TaskService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 *  @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 *  )
 */
class TaskController extends Controller
{
    public function __construct(private TaskService $taskService) {}
    
    /**
     * @OA\Get(
     *     path="/api/tasks",
     *     tags={"Tasks"},
     *     summary="Obtener lista de tareas con paginación",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="perPage",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(name="title", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="description", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="status", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="deleted", in="query", required=false, @OA\Schema(type="boolean")),
     *     @OA\Parameter(
     *         name="created_at",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string", format="date", example="2025-07-15")
     *      ),
     *     @OA\Response(response=200, description="Lista paginada de tareas"),
     * )
     */
    public function index(IndexTaskRequest $request)
    {
        return $this->taskService->getAllPaginated($request);
    }

    /**
     * @OA\Post(
     *     path="/api/tasks",
     *     tags={"Tasks"},
     *     summary="Crear nueva tarea",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"title", "priority_id"},
     *                 @OA\Property(property="title", type="string", example="Nueva tarea"),
     *                 @OA\Property(property="description", type="string", example="Descripción opcional"),
     *                 @OA\Property(property="status", type="string", example="pending"),
     *                 @OA\Property(property="priority_id", type="integer", example=2),
     *                 @OA\Property(property="image", type="file", description="Imagen de la tarea"),
     *                 @OA\Property(
     *                     property="reminder_time",
     *                     type="string",
     *                     format="date-time",
     *                     example="2025-07-15 14:00:00",
     *                     description="Fecha y hora para el recordatorio (formato Y-m-d H:i:s)"
     *                 ),
     *                 @OA\Property(
     *                     property="reminder_before",
     *                     type="integer",
     *                     example=10,
     *                     description="Cantidad de tiempo antes del recordatorio"
     *                 ),
     *                 @OA\Property(
     *                     property="reminder_unit",
     *                     type="string",
     *                     enum={"minutes", "hours", "days"},
     *                     example="minutes",
     *                     description="Unidad de tiempo para el recordatorio (minutes, hours, days)"
     *                 ),
     *             )
     *         )
     *     ),
     *     @OA\Response(response=201, description="Tarea creada correctamente"),
     *     @OA\Response(response=422, description="Datos inválidos"),
     *     @OA\Response(response=500, description="Error interno del servidor")
     * )
     */
    public function store(StoreTaskRequest $request)
    {
        Log::info('Llegó al controlador crear');
        $data = $request->validated();
        
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image');
        }
        
        $dto = new CreateTaskDto($data);

        return $this->taskService->create($dto);
    }

    /**
     * @OA\Patch(
     *     path="/api/tasks/{id}",
     *     tags={"Tasks"},
     *     summary="Actualizar una tarea existente",
     *     security={{"bearerAuth":{}}},
     *     description="Actualiza los datos de una tarea específica por su ID.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de la tarea a actualizar",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string", example="Tarea actualizada"),
     *             @OA\Property(property="description", type="string", example="Nueva descripción de la tarea"),
     *             @OA\Property(property="status", type="string", enum={"pending", "in_progress", "completed"}, example="in_progress"),
     *             @OA\Property(property="user_id", type="integer", example=1),
     *             @OA\Property(property="priority_id", type="integer", example=2)
     *         )
     *     ),
     *     @OA\Response(response=200, description="Tarea actualizada correctamente"),
     *     @OA\Response(response=405, description="Tarea actualizada correctamente"),
     * )
     */
    public function update(Request $request, int $id)
    {
        $dto = new UpdateTaskDto($request->all());
        return $this->taskService->update($id, $dto);
    }

    /**
     * @OA\Delete(
     *     path="/api/tasks/{id}",
     *     tags={"Tasks"},
     *     summary="Eliminar una tarea",
     *     security={{"bearerAuth":{}}},
     *     description="Realiza un soft delete de la tarea especificada por su ID.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de la tarea a eliminar",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Tarea eliminada correctamente"),
     *     @OA\Response(response=404, description="Tarea no encontrada"),
     *     @OA\Response(response=500, description="Error interno del servidor")
     * )
     */
    public function destroy(int $id)
    {
        return $this->taskService->delete($id);
    }

        /**
     * @OA\Get(
     *     path="/api/tasks/export-xml",
     *     tags={"Tasks"},
     *     summary="Exportar tareas a XML",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Archivo XML con tareas exportadas"),
     *     @OA\Response(response=401, description="No autorizado")
     * )
     */
    public function exportXml()
    {
        return $this->taskService->exportXml();
    }


    /**
     * @OA\Post(
     *     path="/api/tasks/restore-xml",
     *     tags={"Tasks"},
     *     summary="Restaurar tareas desde archivo XML",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"file"},
     *                 @OA\Property(
     *                     property="file",
     *                     type="file",
     *                     description="Archivo XML para restaurar tareas"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="Tareas restauradas correctamente"),
     *     @OA\Response(response=400, description="Archivo inválido"),
     *     @OA\Response(response=401, description="No autorizado")
     * )
     */
    public function restoreFromXml(Request $request)
    {
        return $this->taskService->restoreFromXml($request);
    }

}
