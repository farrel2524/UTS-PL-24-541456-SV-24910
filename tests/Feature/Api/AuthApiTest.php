<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('login dengan email dan password dan mereturn token', function () {
    $user = User::factory()->create();

    $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'password',
    ])

        ->assertOk()
        ->assertJsonStructure(['user', 'token', 'token_type']);
});

it('menolak login dengan email atau password yang salah', function () {
    $user = User::factory()->create();

    $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'passworifbhasvfjkd',
    ])

        ->assertStatus(422)
        ->assertJsonValidationErrors('email');
});

test('example', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
});
