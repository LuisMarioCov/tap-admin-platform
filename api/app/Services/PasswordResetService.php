<?php

namespace App\Services;

use App\Mail\PasswordResetMail;
use App\Models\PasswordResetToken;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
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

        if (app()->environment('production') && config('mail.default') === 'log') {
            throw new \RuntimeException('El envío de correo no está configurado en el servidor.');
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

        Mail::to($email)->send(new PasswordResetMail(
            userName: (string) $user->name,
            resetUrl: $resetUrl,
        ));

        Log::info('Password reset email sent.', ['email' => $email]);
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
