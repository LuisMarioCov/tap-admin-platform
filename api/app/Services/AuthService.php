<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function __construct(
        private readonly PasswordResetService $passwordResetService,
    ) {}

    /**
     * Scalar email + password only. Wrong credentials return the same 422 message (no user enumeration).
     *
     * @return array{token: string, user: User}
     */
    public function login(string $email, string $password): array
    {
        $user = User::query()->where('email', $email)->first();

        if ($user === null || ! Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Credenciales inválidas.'],
            ]);
        }

        $tokenResult = $user->createToken('api');

        return [
            'token' => $tokenResult->plainTextToken,
            'user' => $user,
        ];
    }

    public function logout(User $user): void
    {
        $token = $user->currentAccessToken();

        if ($token !== null) {
            $token->delete();
        }
    }

    public function forgotPassword(string $email): void
    {
        $this->passwordResetService->sendResetLink($email);
    }

    public function resetPassword(string $email, string $token, string $password): void
    {
        $reset = $this->passwordResetService->resetPassword($email, $token, $password);

        if (! $reset) {
            throw ValidationException::withMessages([
                'token' => ['El enlace de recuperación es inválido o expiró.'],
            ]);
        }
    }
}
