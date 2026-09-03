<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $authService,
    ) {}

    /** Returns Sanctum token + UserResource. Password never appears in JSON. */
    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login(
            (string) $request->validated('email'),
            (string) $request->validated('password'),
        );

        return response()->json([
            'token' => $result['token'],
            'user' => new UserResource($result['user']),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $this->authService->logout($user);

        return response()->json(['message' => 'Sesión cerrada.']);
    }

    public function me(Request $request): UserResource
    {
        return new UserResource($request->user());
    }

    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $resetUrl = $this->authService->forgotPassword((string) $request->validated('email'));

        $payload = [
            'message' => 'Si el correo existe, enviamos instrucciones de recuperación.',
        ];

        // Sin SMTP en el examen: devolvemos el enlace para completar el flujo en pantalla.
        if ($resetUrl !== null) {
            $payload['reset_url'] = $resetUrl;
            $payload['message'] = 'Usa el enlace de abajo para elegir una nueva contraseña (válido 60 minutos).';
        }

        return response()->json($payload);
    }

    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $this->authService->resetPassword(
            (string) $request->validated('email'),
            (string) $request->validated('token'),
            (string) $request->validated('password'),
        );

        return response()->json(['message' => 'Contraseña actualizada correctamente.']);
    }
}
