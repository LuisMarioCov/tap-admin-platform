<?php

namespace App\Services;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;

class UserService
{
    public function __construct(
        private readonly CodeGeneratorService $codeGenerator,
        private readonly AuditLogger $auditLogger,
        private readonly PhotoStorageService $photoStorage,
    ) {}

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return User::query()
            ->orderByDesc('created_at')
            ->paginate(min($perPage, 100));
    }

    public function find(string $id): User
    {
        $user = User::query()->find($id);

        if ($user === null) {
            throw new ModelNotFoundException('Usuario no encontrado.');
        }

        return $user;
    }

    /**
     * @param  array{
     *     name: string,
     *     email: string,
     *     password: string,
     *     phone?: string|null,
     *     country_code?: string|null,
     *     profile_ids: list<string>
     * }  $data
     */
    public function create(array $data, UploadedFile $photo): User
    {
        $this->assertProfilesExist($data['profile_ids']);

        $photoId = $this->photoStorage->store($photo);

        $user = User::query()->create([
            'code' => $this->codeGenerator->next('USR'),
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'phone' => $data['phone'] ?? null,
            'country_code' => $data['country_code'] ?? null,
            'profile_ids' => $data['profile_ids'],
            'photo_file_id' => $photoId,
        ]);

        $this->auditLogger->record('users', (string) $user->getKey(), 'created', null, $user->toArray());

        return $user;
    }

    /**
     * @param  array{
     *     name: string,
     *     email: string,
     *     password?: string|null,
     *     phone?: string|null,
     *     country_code?: string|null,
     *     profile_ids: list<string>
     * }  $data
     */
    public function update(User $user, array $data, ?UploadedFile $photo = null): User
    {
        $this->assertProfilesExist($data['profile_ids']);
        $before = $user->toArray();

        if ($photo !== null) {
            $this->photoStorage->delete($user->photo_file_id);
            $user->photo_file_id = $this->photoStorage->store($photo);
        }

        $user->fill([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'country_code' => $data['country_code'] ?? null,
            'profile_ids' => $data['profile_ids'],
        ]);

        if (! empty($data['password'])) {
            $user->password = $data['password'];
        }

        $user->save();

        $this->auditLogger->record(
            'users',
            (string) $user->getKey(),
            'updated',
            $before,
            $user->fresh()->toArray(),
        );

        return $user->fresh();
    }

    public function delete(User $user): void
    {
        $before = $user->toArray();
        $user->delete();

        $this->auditLogger->record(
            'users',
            (string) $user->getKey(),
            'deleted',
            $before,
            ['deleted_at' => now()->toIso8601String()],
        );
    }

    /** @param list<string> $profileIds */
    private function assertProfilesExist(array $profileIds): void
    {
        $count = Profile::query()->whereIn('_id', $profileIds)->count();

        if ($count !== count($profileIds)) {
            throw ValidationException::withMessages([
                'profile_ids' => ['Uno o más perfiles no existen.'],
            ]);
        }
    }
}
