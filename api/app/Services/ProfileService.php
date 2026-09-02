<?php

namespace App\Services;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

class ProfileService
{
    public function __construct(
        private readonly CodeGeneratorService $codeGenerator,
        private readonly AuditLogger $auditLogger,
    ) {}

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Profile::query()
            ->orderByDesc('created_at')
            ->paginate(min($perPage, 100));
    }

    public function find(string $id): Profile
    {
        $profile = Profile::query()->find($id);

        if ($profile === null) {
            throw new ModelNotFoundException('Perfil no encontrado.');
        }

        return $profile;
    }

    /** @param array{name: string, section_keys: list<string>} $data */
    public function create(array $data): Profile
    {
        $profile = Profile::query()->create([
            'code' => $this->codeGenerator->next('PRF'),
            'name' => $data['name'],
            'section_keys' => $data['section_keys'],
        ]);

        $this->auditLogger->record('profiles', (string) $profile->getKey(), 'created', null, $profile->toArray());

        return $profile;
    }

    /** @param array{name: string, section_keys: list<string>} $data */
    public function update(Profile $profile, array $data): Profile
    {
        $before = $profile->toArray();

        $profile->fill([
            'name' => $data['name'],
            'section_keys' => $data['section_keys'],
        ]);
        $profile->save();

        $this->auditLogger->record(
            'profiles',
            (string) $profile->getKey(),
            'updated',
            $before,
            $profile->fresh()->toArray(),
        );

        return $profile->fresh();
    }

    public function delete(Profile $profile): void
    {
        $assigned = User::query()
            ->where('profile_ids', (string) $profile->getKey())
            ->exists();

        if ($assigned) {
            throw ValidationException::withMessages([
                'profile' => ['No se puede eliminar un perfil asignado a usuarios.'],
            ]);
        }

        $before = $profile->toArray();
        $profile->delete();

        $this->auditLogger->record(
            'profiles',
            (string) $profile->getKey(),
            'deleted',
            $before,
            ['deleted_at' => now()->toIso8601String()],
        );
    }
}
