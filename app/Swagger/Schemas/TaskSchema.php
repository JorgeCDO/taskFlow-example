<?php

namespace App\Swagger\Schemas;

/**
 * @OA\Schema(
 *     schema="Task",
 *     type="object",
 *     title="Task",
 *     required={"title", "description", "status"},
 *     @OA\Property(property="title", type="string", example="Desarrollar prueba corta en Laravel"),
 *     @OA\Property(property="user_id", type="integer", example="1"),
 *     @OA\Property(property="description", type="string", example="Descripción de la tarea"),
 *     @OA\Property(property="status", type="string", example="pendiente"),
 *     @OA\Property(property="expiration_date", type="string", format="date", example="2025-06-20"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class TaskSchema
{

}
