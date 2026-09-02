<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Profile */
class ProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (string) $this->getKey(),
            'code' => $this->code,
            'name' => $this->name,
            'section_keys' => $this->section_keys ?? [],
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
