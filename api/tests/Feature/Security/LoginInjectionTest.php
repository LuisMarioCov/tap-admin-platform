<?php

namespace Tests\Feature\Security;

use Tests\TestCase;

class LoginInjectionTest extends TestCase
{
    public function test_rejects_nosql_injection_payload_on_login(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => ['$gt' => ''],
            'password' => 'test',
        ]);

        $response->assertStatus(422);
    }

    public function test_products_requires_authentication(): void
    {
        $this->getJson('/api/products')->assertStatus(401);
    }
}
