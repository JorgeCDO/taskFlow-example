<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskLog;
use Illuminate\Http\Request;

class TaskController extends Controller
{
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
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Ocurrió un error al listar las tareas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

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

            TaskLog::create([
                'task_id' => $task->id,
                'user_id' => auth()->id(),
                'log' => 'Se guardó tarea nueva',
            ]);

            return response()->json($task, 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Ocurrió un error al guardar la tareas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Task $task)
    {
        try {
            $this->authorizeTask($task);
            return $task;
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Ocurrió un error: Usuario no autorizado',
                'error' => $e->getMessage()
            ], 500);
        }
    }

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

            TaskLog::create([
                'task_id' => $task->id,
                'user_id' => auth()->id(),
                'log' => 'Se actualizó tarea',
            ]);

            return response()->json($task);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Ocurrió un error al actualizar la tarea',
                'error' => $e->getMessage()
            ], 500);
        }
    }

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
        if ($task->user_id !== auth()->id()) {
            abort(403, 'Usuario no autorizado');
        }
    }

    public function filter(Request $request)
    {
        try {

            $query = auth()->user()->tasks();

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('expiration_date')) {
                $query->whereDate('expiration_date', $request->expiration_date);
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
