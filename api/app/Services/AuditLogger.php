<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Support\RedactsSensitiveFields;
use Illuminate\Support\Facades\Auth;

class AuditLogger
{
    /**
     * Append-only audit row. Passwords are stripped by RedactsSensitiveFields.
     * Create: $before is null. Update/delete: snapshot before vs after.
     *
     * @param  array<string, mixed>|null  $before
     * @param  array<string, mixed>|null  $after
     */
    public function record(string $entity, string $entityId, string $action, ?array $before, ?array $after): void
    {
        AuditLog::query()->create([
            'entity' => $entity,
            'entity_id' => $entityId,
            'action' => $action,
            'before' => RedactsSensitiveFields::redact($before),
            'after' => RedactsSensitiveFields::redact($after),
            'user_id' => Auth::id(),
            'created_at' => now(),
        ]);
    }
}
