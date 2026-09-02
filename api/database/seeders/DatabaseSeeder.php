<?php

namespace Database\Seeders;

use App\Models\Profile;
use App\Models\Section;
use App\Models\User;
use App\Services\CodeGeneratorService;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $sections = [
            ['key' => 'products', 'name' => 'Productos', 'route' => '/products'],
            ['key' => 'users', 'name' => 'Usuarios', 'route' => '/users'],
            ['key' => 'profiles', 'name' => 'Perfiles', 'route' => '/profiles'],
        ];

        foreach ($sections as $section) {
            Section::query()->updateOrCreate(['key' => $section['key']], $section);
        }

        $codeGenerator = app(CodeGeneratorService::class);

        $adminProfile = Profile::query()->updateOrCreate(
            ['name' => 'Administrador General'],
            [
                'code' => Profile::query()->where('name', 'Administrador General')->value('code')
                    ?? $codeGenerator->next('PRF'),
                'section_keys' => ['products', 'users', 'profiles'],
            ]
        );

        $productsProfile = Profile::query()->updateOrCreate(
            ['name' => 'Operador de Productos'],
            [
                'code' => Profile::query()->where('name', 'Operador de Productos')->value('code')
                    ?? $codeGenerator->next('PRF'),
                'section_keys' => ['products'],
            ]
        );

        User::query()->updateOrCreate(
            ['email' => 'admin@tap.local'],
            [
                'code' => User::query()->where('email', 'admin@tap.local')->value('code')
                    ?? $codeGenerator->next('USR'),
                'name' => 'Administrador TAP',
                'password' => 'Admin123!',
                'phone' => null,
                'country_code' => '+52',
                'profile_ids' => [(string) $adminProfile->getKey()],
                'photo_file_id' => null,
            ]
        );

        User::query()->updateOrCreate(
            ['email' => 'operador@tap.local'],
            [
                'code' => User::query()->where('email', 'operador@tap.local')->value('code')
                    ?? $codeGenerator->next('USR'),
                'name' => 'Operador Productos',
                'password' => 'Operador123!',
                'phone' => null,
                'country_code' => '+52',
                'profile_ids' => [(string) $productsProfile->getKey()],
                'photo_file_id' => null,
            ]
        );
    }
}
