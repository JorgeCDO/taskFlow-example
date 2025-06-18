<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskLog;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class TaskController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/tasks",
     *     summary="Lista todas las tareas del usuario autenticado",
     *     tags={"Tareas"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Listado de tareas",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="success",
     *                 type="boolean",
     *                 example=true
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer"),
     *                 @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Task")),
     *                 @OA\Property(property="total", type="integer")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="No autorizado"),
     *     @OA\Response(response=500, description="Error en el servidor")
     * )
     */
    public function index()
    {
        try {
            $tareas = auth()->user()->tasks()->paginate(4);
            if ($tareas->total() === 0) {
                return response()->json([
                    'message' => 'No hay tareas registradas',
                    'success' => false,
                    'data' => []
                ], 200);
            }
            return response()->json([
                'success' => true,
                'data' => $tareas
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Ocurrió un error al listar las tareas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/tasks",
     *     summary="Crear una nueva tarea",
     *     tags={"Tareas"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title"},
     *             @OA\Property(property="title", type="string", example="Aprender nuevas tecnologias"),
     *             @OA\Property(property="description", type="string", example="Descripción opcional"),
     *             @OA\Property(property="status", type="string", enum={"pendiente","en progreso","completada"}, example="pendiente"),
     *             @OA\Property(property="expiration_date", type="string", format="date", example="2025-07-01")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Tarea añadida exitosamente",
     *         @OA\JsonContent(ref="#/components/schemas/Task")
     *     ),
     *     @OA\Response(response=403, description="No autorizado"),
     *     @OA\Response(response=422, description="Validación fallida"),
     *     @OA\Response(response=500, description="Error en el servidor")
     * )
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'status' => 'in:pendiente,en progreso,completada',
                'expiration_date' => 'nullable|date',
            ]);

            $task = auth()->user()->tasks()->create($validated);

            //guardamos en log cuando creamos una tarea nueva
            TaskLog::create([
                'task_id' => $task->id,
                'user_id' => auth()->id(),
                'log' => 'Se guardó tarea nueva',
            ]);

            return response()->json($task, 201);

            //verificamos que las validaciones estén correctas
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Ocurrió un error al guardar la tarea',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/tasks/{task}",
     *     summary="Mostrar detalles de una tarea por id",
     *     tags={"Tareas"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="task",
     *         in="path",
     *         description="ID de la tarea",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Detalles de la tarea",
     *         @OA\JsonContent(ref="#/components/schemas/Task")
     *     ),
     *     @OA\Response(response=403, description="Prohibido - usuario no autorizado"),
     *     @OA\Response(response=500, description="Error en el servidor")
     * )
     */
    public function show(Task $task)
    {
        //mostramos los datos de una tarea por id
        try {
            $this->authorizeTask($task);
            return $task;
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Ocurrió un error en el servidor',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/tasks/{task}",
     *     summary="Actualizar una tarea",
     *     tags={"Tareas"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="task",
     *         in="path",
     *         description="ID de la tarea",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string", example="Título actualizado"),
     *             @OA\Property(property="description", type="string", example="Descripción actualizada"),
     *             @OA\Property(property="status", type="string", enum={"pendiente","en progreso","completada"}, example="completada"),
     *             @OA\Property(property="expiration_date", type="string", format="date", example="2025-07-10")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tarea actualizada",
     *         @OA\JsonContent(ref="#/components/schemas/Task")
     *     ),
     *     @OA\Response(response=401, description="No autorizado"),
     *     @OA\Response(response=403, description="Prohibido - usuario no autorizado"),
     *     @OA\Response(response=422, description="Validación fallida"),
     *     @OA\Response(response=500, description="Error en el servidor")
     * )
     */
    public function update(Request $request, Task $task)
    {
        try {
            $this->authorizeTask($task);

            $validated = $request->validate([
                'title' => 'string|max:255',
                'description' => 'nullable|string',
                'status' => 'in:pendiente,en progreso,completada',
                'expiration_date' => 'nullable|date',
            ]);

            $task->update($validated);

            //registramos en Log cuando actualizamos una tarea
            TaskLog::create([
                'task_id' => $task->id,
                'user_id' => auth()->id(),
                'log' => 'Se actualizó tarea',
            ]);

            return response()->json($task);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Ocurrió un error al actualizar la tarea',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/tasks/{task}",
     *     summary="Eliminar una tarea",
     *     tags={"Tareas"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="task",
     *         in="path",
     *         description="ID de la tarea",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=204, description="Tarea eliminada exitosamente"),
     *     @OA\Response(response=401, description="No autorizado"),
     *     @OA\Response(response=403, description="Prohibido - usuario no propietario"),
     *     @OA\Response(response=500, description="Error en el servidor")
     * )
     */
    public function destroy(Task $task)
    {
        try {

            $this->authorizeTask($task);
            $task->delete();
            return response()->json(null, 204);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Ocurrió un error al borrar la tarea',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    protected function authorizeTask(Task $task)
    {
        //validamos que la tarea la haya registrado el usuario logueado
        if ($task->user_id !== auth()->id()) {
            abort(403, 'Usuario no autorizado');
        }
    }

    /**
     * @OA\Get(
     *     path="/api/tasks/filter",
     *     summary="Filtrar tareas por estado, fecha o orden",
     *     tags={"Tareas"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Status de la tarea",
     *         required=false,
     *         @OA\Schema(type="string", enum={"pendiente","en progreso","completada"})
     *     ),
     *     @OA\Parameter(
     *         name="expiration_date",
     *         in="query",
     *         description="Fecha de expiración <= (YYYY-MM-DD)",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="order_by",
     *         in="query",
     *         description="Ordenar por fecha de creación (asc o desc)",
     *         required=false,
     *         @OA\Schema(type="string", enum={"asc","desc"})
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Número de página para paginación",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Listado filtrado de tareas",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=401, description="No autorizado"),
     *     @OA\Response(response=500, description="Error en el servidor")
     * )
     */
    public function filter(Request $request)
    {
        try {

            $query = auth()->user()->tasks();

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('expiration_date')) {
                $query->whereDate('expiration_date', '<=', $request->expiration_date);
            }

            if ($request->filled('order_by')) {
                $query->orderBy('created_at', $request->order_by);
            }

            $resultado = $query->paginate(4);

            if ($resultado->total() === 0) {
                return response()->json([
                    'message' => 'No se encontraron tareas con esos filtros',
                    'success' => false,
                    'data' => []
                ], 200);
            }

            return response()->json([
                'success' => true,
                'data' => $resultado
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al filtrar las tareas',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
