<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/register",
     *     summary="Registrar un nuevo usuario",
     *     tags={"Autenticación"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "name", "password"},
     *             @OA\Property(property="email", type="string", example="usuario@correo.com"),
     *             @OA\Property(property="name", type="string", example="Juan Pérez"),
     *             @OA\Property(property="password", type="string", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Usuario registrado y token generado",
     *         @OA\JsonContent(
     *             @OA\Property(property="token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGci...")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Error de validación"),
     *     @OA\Response(response=500, description="Error del servidor")
     * )
     */
    public function register(Request $request)
    {
        try {
            $validated = $request->validate([
                'email' => 'required|email|unique:users',
                'name' => 'required|string|max:255',
                'password' => 'required|min:6',
            ]);

            $user = User::create([
                'email' => $validated['email'],
                'name' => $validated['name'],
                'password' => Hash::make($validated['password']),
            ]);

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json(['token' => $token], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Ocurrió un error al registrar el usuario',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/login",
     *     summary="Login de usuario y generación de token",
     *     tags={"Autenticación"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "password"},
     *             @OA\Property(property="email", type="string", example="usuario@correo.com"),
     *             @OA\Property(property="password", type="string", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Login exitoso y token generado",
     *         @OA\JsonContent(
     *             @OA\Property(property="token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGci...")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Credenciales inválidas"),
     *     @OA\Response(response=500, description="Error del servidor")
     * )
     */
    public function login(Request $request)
    {
        try {
            if (!auth()->attempt($request->only('email', 'password'))) {
                return response()->json(['message' => 'Credenciales inválidas'], 401);
            }

            $token = auth()->user()->createToken('auth_token')->plainTextToken;

            return response()->json(['token' => $token], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Ocurrió un error al loguearse.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/logout",
     *     summary="Cerrar sesión y revocar tokens",
     *     tags={"Autenticación"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Cierre de sesión exitoso",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Usuario cerró sesión")
     *         )
     *     ),
     *
     * )
     */
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json(['message' => 'Usuario cerró sesión']);
    }

}
