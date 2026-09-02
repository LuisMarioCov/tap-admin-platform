<?php

namespace App\Services;

use App\Models\PasswordResetToken;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class PasswordResetService
{
    public function sendResetLink(string $email): void
    {
        $user = User::query()->where('email', $email)->first();

        if ($user === null) {
            return;
        }

        $plainToken = Str::random(64);

        PasswordResetToken::query()->where('email', $email)->delete();

        PasswordResetToken::query()->create([
            'email' => $email,
            'token_hash' => Hash::make($plainToken),
            'expires_at' => now()->addHour(),
            'created_at' => now(),
        ]);

        $resetUrl = rtrim((string) config('app.frontend_url'), '/')
            .'/auth/reset-password?token='.urlencode($plainToken)
            .'&email='.urlencode($email);

        Mail::raw(
            "Recibimos una solicitud para restablecer tu contraseña.\n\nEnlace (válido 60 min):\n{$resetUrl}",
            function ($message) use ($email): void {
                $message->to($email)
                    ->subject('Restablecer contraseña — TAP Admin');
            }
        );
    }

    public function resetPassword(string $email, string $token, string $password): bool
    {
        $record = PasswordResetToken::query()
            ->where('email', $email)
            ->orderByDesc('created_at')
            ->first();

        if ($record === null || $record->expires_at->isPast()) {
            return false;
        }

        if (! Hash::check($token, $record->token_hash)) {
            return false;
        }

        $user = User::query()->where('email', $email)->first();

        if ($user === null) {
            return false;
        }

        $user->password = $password;
        $user->save();

        PasswordResetToken::query()->where('email', $email)->delete();

        return true;
    }
}
