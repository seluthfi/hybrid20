<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ConsoleUserCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_hybrid_create_user_command_creates_user(): void
    {
        $this->artisan('hybrid:create-user', [
            'name' => 'Bima Satria',
            'email' => 'bima@hybrid20.id',
            '--password' => 'secret1234',
        ])
        ->expectsOutput('Pengguna [Bima Satria] (bima@hybrid20.id) berhasil dibuat.')
        ->assertExitCode(0);

        $this->assertDatabaseHas('users', [
            'email' => 'bima@hybrid20.id',
            'name' => 'Bima Satria',
        ]);

        $user = User::where('email', 'bima@hybrid20.id')->first();
        $this->assertTrue(Hash::check('secret1234', $user->password));
    }

    public function test_hybrid_create_user_command_validates_input(): void
    {
        User::factory()->create(['email' => 'existing@hybrid20.id']);

        $this->artisan('hybrid:create-user', [
            'name' => 'Duplikat User',
            'email' => 'existing@hybrid20.id',
            '--password' => 'secret1234',
        ])
        ->expectsOutput('Email sudah terdaftar.')
        ->assertExitCode(1);
    }

    public function test_hybrid_reset_password_command_updates_password(): void
    {
        $user = User::factory()->create([
            'email' => 'reset@hybrid20.id',
            'password' => Hash::make('oldpassword'),
        ]);

        $this->artisan('hybrid:reset-password', [
            'email' => 'reset@hybrid20.id',
            '--password' => 'newsecretpassword',
        ])
        ->assertExitCode(0);

        $user->refresh();
        $this->assertTrue(Hash::check('newsecretpassword', $user->password));
    }
}
