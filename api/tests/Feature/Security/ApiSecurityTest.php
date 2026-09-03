<?php

namespace Tests\Feature\Security;

use Tests\TestCase;

class ApiSecurityTest extends TestCase
{
    public function test_login_rejects_nosql_injection_payload(): void
    {
        $this->postJson('/api/auth/login', [
            'email' => ['$gt' => ''],
            'password' => 'x',
        ])->assertStatus(422);
    }

    public function test_products_index_requires_token(): void
    {
        $this->getJson('/api/products')->assertStatus(401);
    }

    public function test_users_index_requires_token(): void
    {
        $this->getJson('/api/users')->assertStatus(401);
    }

    public function test_profiles_index_requires_token(): void
    {
        $this->getJson('/api/profiles')->assertStatus(401);
    }

    public function test_product_store_requires_token(): void
    {
        $this->postJson('/api/products', [
            'name' => 'Aceite',
            'brand' => 'Castrol',
            'price' => 150,
        ])->assertStatus(401);
    }

    public function test_operator_cannot_list_users(): void
    {
        $login = $this->postJson('/api/auth/login', [
            'email' => 'operador@tap.local',
            'password' => 'Operador123!',
        ]);

        if ($login->status() !== 200) {
            $this->markTestSkipped('Seed demo users are not available in this environment.');
        }

        $this->withToken($login->json('token'))
            ->getJson('/api/users')
            ->assertStatus(403);
    }

    public function test_admin_product_price_over_999_is_rejected(): void
    {
        $login = $this->postJson('/api/auth/login', [
            'email' => 'admin@tap.local',
            'password' => 'Admin123!',
        ]);

        if ($login->status() !== 200) {
            $this->markTestSkipped('Seed demo users are not available in this environment.');
        }

        $this->withToken($login->json('token'))
            ->postJson('/api/products', [
                'name' => 'Aceite',
                'brand' => 'Castrol',
                'price' => 1000,
            ])
            ->assertStatus(422);
    }
}
