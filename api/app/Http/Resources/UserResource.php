<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\User */
class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (string) $this->getKey(),
            'code' => $this->code,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'country_code' => $this->country_code,
            'photo_file_id' => $this->photo_file_id,
            'profile_ids' => $this->profile_ids ?? [],
            'profiles' => ProfileResource::collection($this->profiles()),
            'allowed_sections' => $this->allowedSections(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
