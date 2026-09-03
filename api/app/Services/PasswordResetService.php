<?php

namespace App\Services;

use App\Models\PasswordResetToken;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PasswordResetService
{
    /**
     * Sends reset mail via Resend HTTPS API (Render free blocks SMTP ports).
     */
    public function sendResetLink(string $email): void
    {
        $user = User::query()->where('email', $email)->first();

        if ($user === null) {
            return;
        }

        $apiKey = (string) config('services.resend.key');

        if ($apiKey === '') {
            throw ValidationException::withMessages([
                'email' => ['Falta RESEND_KEY en Render. Sin eso no se puede enviar el correo.'],
            ]);
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

        $fromAddress = (string) config('mail.from.address', 'onboarding@resend.dev');
        $fromName = (string) config('mail.from.name', 'TAP Admin');
        $html = view('emails.password-reset', [
            'userName' => (string) $user->name,
            'resetUrl' => $resetUrl,
        ])->render();

        $response = Http::withToken($apiKey)
            ->acceptJson()
            ->timeout(20)
            ->post('https://api.resend.com/emails', [
                'from' => "{$fromName} <{$fromAddress}>",
                'to' => [$email],
                'subject' => 'TAP Admin — ¿Quieres cambiar tu contraseña?',
                'html' => $html,
            ]);

        if (! $response->successful()) {
            Log::error('Resend API rejected password reset mail.', [
                'email' => $email,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw ValidationException::withMessages([
                'email' => [
                    'No se pudo enviar el correo (Resend). '.$this->summarizeResendError($response->body()),
                ],
            ]);
        }

        Log::info('Password reset email sent via Resend.', [
            'email' => $email,
            'id' => $response->json('id'),
        ]);
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

    private function summarizeResendError(string $body): string
    {
        $decoded = json_decode($body, true);

        if (is_array($decoded) && isset($decoded['message']) && is_string($decoded['message'])) {
            return $decoded['message'];
        }

        return 'Revisa RESEND_KEY y que MAIL_FROM_ADDRESS sea onboarding@resend.dev.';
    }
}
