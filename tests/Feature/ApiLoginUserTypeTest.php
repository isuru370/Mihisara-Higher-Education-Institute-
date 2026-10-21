<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiLoginUserTypeTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_response_includes_user_type_code(): void
    {
        $type = UserType::create(['name' => 'Admin', 'code' => 'ADMIN', 'is_active' => true]);

        $user = new User();
        $user->forceFill([
            'name' => 'Test Admin',
            'email' => 'admin@example.com',
            'password' => 'secret-pass',
            'user_type_id' => $type->id,
            'is_active' => true,
        ])->save();

        $this->postJson('/api/login', [
            'email' => 'admin@example.com',
            'password' => 'secret-pass',
        ])
            ->assertOk()
            ->assertJsonPath('user.user_type.code', 'ADMIN');
    }
}
