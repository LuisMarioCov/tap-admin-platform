<?php

namespace Tests\Unit;

use App\Rules\ScalarString;
use PHPUnit\Framework\TestCase;

class ScalarStringTest extends TestCase
{
    public function test_accepts_plain_string(): void
    {
        $failed = false;
        $rule = new ScalarString();
        $rule->validate('email', 'admin@tap.local', function () use (&$failed) {
            $failed = true;
        });

        $this->assertFalse($failed);
    }

    public function test_rejects_array_payload(): void
    {
        $failed = false;
        $rule = new ScalarString();
        $rule->validate('email', ['$gt' => ''], function () use (&$failed) {
            $failed = true;
        });

        $this->assertTrue($failed);
    }
}
