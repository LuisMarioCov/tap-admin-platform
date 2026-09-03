<?php

namespace App\Models;

use App\Concerns\HasMongoApiTokens;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\SoftDeletes;
use MongoDB\Laravel\Eloquent\Model;

class User extends Model implements AuthenticatableContract
{
    use Authenticatable;
    use HasMongoApiTokens;
    use SoftDeletes;

    protected $connection = 'mongodb';

    protected $collection = 'users';

    protected $fillable = [
        'code',
        'name',
        'email',
        'password',
        'phone',
        'country_code',
        'photo_file_id',
        'profile_ids',
    ];

    protected $hidden = [
        'password',
    ];

    protected function casts(): array
    {
        return [
            'profile_ids' => 'array',
            'password' => 'hashed',
            'deleted_at' => 'datetime',
        ];
    }

    public function hasSection(string $sectionKey): bool
    {
        $allowed = $this->allowedSections();

        return in_array($sectionKey, $allowed, true);
    }

    /** Union of section_keys from assigned profiles. Not stored on the user document. */
    public function allowedSections(): array
    {
        if (empty($this->profile_ids)) {
            return [];
        }

        $profiles = Profile::query()
            ->whereIn('_id', $this->profile_ids)
            ->get(['section_keys']);

        $sections = [];

        foreach ($profiles as $profile) {
            foreach ($profile->section_keys ?? [] as $key) {
                $sections[$key] = true;
            }
        }

        return array_keys($sections);
    }

    /** @return list<Profile> */
    public function profiles()
    {
        if (empty($this->profile_ids)) {
            return collect();
        }

        return Profile::query()->whereIn('_id', $this->profile_ids)->get();
    }
}
