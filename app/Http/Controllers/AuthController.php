<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        try {
            $data = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'unique:users,email'],
                'password' => ['required', 'string', 'min:6', 'confirmed'],
            ]);

            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'role' => 'resident',
            ]);

            event(new Registered($user));

            return response()->json([
                'message' => 'Usuario creado. Revisa tu correo para verificar tu cuenta.',
                'user' => $user,
                'requires_verification' => true,
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al crear usuario: '.$e->getMessage(),
            ], 500);
        }
    }

    public function login(Request $request)
    {
        try {
            $validated = $request->validate([
                'email' => ['required', 'email'],
                'password' => ['required', 'string'],
            ]);

            if (!Auth::attempt($validated)) {
                return response()->json([
                    'message' => 'Credenciales inválidas',
                ], 401);
            }

            /** @var User $user */
            $user = Auth::user();

            if (!$user->hasVerifiedEmail()) {
                return response()->json([
                    'message' => 'Debes verificar tu correo antes de iniciar sesión.',
                    'requires_verification' => true,
                    'email' => $user->email,
                ], 403);
            }

            $token = $user->createToken('api-token');

            return response()->json([
                'user' => $user,
                'token' => $token->plainTextToken,
                'role' => $user->role ?? 'resident',
                'message' => 'Sesión iniciada correctamente',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al iniciar sesión: '.$e->getMessage(),
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            if ($request->user() && $request->user()->currentAccessToken()) {
                $request->user()->currentAccessToken()->delete();
            }

            return response()->json([
                'message' => 'Sesión cerrada',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al cerrar sesión: '.$e->getMessage(),
            ], 500);
        }
    }

    public function user(Request $request)
    {
        $user = $request->user();

        return response()->json([
            ...$user->toArray(),
            'role' => $user->role ?? 'resident',
        ]);
    }

    public function resendVerificationEmail(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'message' => 'Usuario no encontrado.',
            ], 404);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'El correo ya fue verificado.',
            ], 200);
        }

        $user->sendEmailVerificationNotification();

        return response()->json([
            'message' => 'Correo de verificación reenviado.',
        ]);
    }
}