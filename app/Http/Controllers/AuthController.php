<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

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
                'message' => 'Error al crear usuario: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function login(Request $request)
    {
        try {
            $validated = $request->validate([
                'email' => ['required', 'email'],
                'password' => ['required', 'string'],
                'device_name' => ['required', 'string', 'max:255'],
            ]);

            if (!Auth::attempt([
                'email' => $validated['email'],
                'password' => $validated['password'],
            ])) {
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

            // Un token por dispositivo:
            // si vuelve a entrar desde el mismo dispositivo, reemplaza ese token
            $user->tokens()
                ->where('name', $validated['device_name'])
                ->delete();

            $token = $user->createToken($validated['device_name']);

            return response()->json([
                'user' => $user,
                'token' => $token->plainTextToken,
                'device_name' => $validated['device_name'],
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
                'message' => 'Error al iniciar sesión: ' . $e->getMessage(),
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
                'message' => 'Error al cerrar sesión: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function logoutAll(Request $request)
    {
        try {
            $user = $request->user();

            if ($user) {
                $user->tokens()->delete();
            }

            return response()->json([
                'message' => 'Sesión cerrada en todos los dispositivos.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al cerrar sesiones: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function changePassword(Request $request)
    {
        try {
            $data = $request->validate([
                'current_password' => ['required', 'string'],
                'password' => ['required', 'string', 'min:6', 'confirmed'],
            ]);

            /** @var User $user */
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'message' => 'Usuario no autenticado.',
                ], 401);
            }

            if (!Hash::check($data['current_password'], $user->password)) {
                return response()->json([
                    'message' => 'La contraseña actual es incorrecta.',
                ], 422);
            }

            $user->password = $data['password'];
            $user->save();

            // Cierra sesión en todos los dispositivos
            $user->tokens()->delete();

            return response()->json([
                'message' => 'Contraseña actualizada correctamente. Se cerró la sesión en todos los dispositivos.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al cambiar la contraseña: ' . $e->getMessage(),
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