<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthController extends Controller
{
    // crear cuenta (puede ser usada por administrador desde el frontend)
    public function register(Request $request)
    {
        try {
            $data = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:6|confirmed',
            ]);

            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'], // El modelo lo hashea automáticamente
            ]);

            return response()->json(['user' => $user, 'message' => 'Usuario creado exitosamente'], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['message' => 'Error de validación', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al crear usuario: ' . $e->getMessage()], 500);
        }
    }

    // inicio de sesión y creación de token
    public function login(Request $request)
    {
        try {
            $validated = $request->validate([
                'email' => 'required|email',
                'password' => 'required|string',
            ]);

            if (!Auth::attempt($validated)) {
                return response()->json(['message' => 'Credenciales inválidas'], 401);
            }

            $user = Auth::user();
            $token = $user->createToken('api-token')->plainTextToken;
            
            return response()->json([
                'user' => $user, 
                'token' => $token,
                'message' => 'Sesión iniciada correctamente'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['message' => 'Error de validación', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al iniciar sesión: ' . $e->getMessage()], 500);
        }
    }

    // cerrar sesión (revocar token actual)
    public function logout(Request $request)
    {
        try {
            if ($request->user()) {
                $request->user()->currentAccessToken()->delete();
            }
            return response()->json(['message' => 'Sesión cerrada']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al cerrar sesión: ' . $e->getMessage()], 500);
        }
    }

    // datos del usuario autenticado
    public function user(Request $request)
    {
        return response()->json($request->user());
    }
}
