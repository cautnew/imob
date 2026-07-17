<?php

use App\Models\User;

test('a request without a token cannot access a protected api route', function () {
    $response = $this->getJson('/api/user');

    $response->assertUnauthorized();
});

test('a valid sanctum token can access a protected api route', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/user');

    $response->assertOk();
    $response->assertJsonPath('id', $user->id);
});
