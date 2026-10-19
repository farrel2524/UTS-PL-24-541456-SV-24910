<?php

use App\Models\Spddata;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('membuat data', function () {
    Sanctum::actingAs(User::factory()->create());

    $this->postJson('/api/spddatas', [
        'Username' => 'oiiaa',
        'Time' => '1h 51m 21s',
        'Difficulty' => 'Hard',
    ])

        ->assertCreated()
        ->assertJsonFragment(['Username' => 'oiiaa']);

    $this->assertDatabaseHas('spddatas', ['Username' => 'oiiaa']);
});

it('menampilkan, mendupdate dan juga menghapus data', function () {
    Sanctum::actingAs(User::factory()->create());

    $spddatas = Spddata::create(['Username' => 'oiiaa', 'Time' => '100h 24m 12s', 'Difficulty' => 'Ultra easy']);

    $this->getJson("/api/spddatas/{$spddatas->id}")
        ->assertOk()
        ->assertJsonFragment(['Username' => 'oiiaa']);
    
    $this->putJson("/api/spddatas/{$spddatas->id}", ['Time' => '12h 1m 22s'])
        ->assertOk();
    $this->assertDatabaseHas('spddatas', ['id' => $spddatas->id, 'Time' => '12h 1m 22s']);

    $this->deleteJson("/api/spddatas/{$spddatas->id}")->assertNoContent();
    $this->assertDatabaseMissing('spddatas', ['id' => $spddatas->id]);
});

it('mereturn 422 ketika menambahkan dengan data invalid', function() {
    Sanctum ::actingAs(User::factory()->create());

    $this->postJson('/api/spddatas', ['Difficulty' => 12])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['Username', 'Difficulty']);
});

test('example', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
});
