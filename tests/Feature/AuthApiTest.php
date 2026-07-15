<?php

namespace Tests\Feature;

use App\Models\User;

describe('Auth API (JWT login)', function () {
    beforeEach(function () {
        // Clean up the test user
        User::where('email', 'auth@test.local')->delete();
    });

    describe('POST /auth/login', function () {
        it('returns a JWT token for valid credentials', function () {
            User::create([
                'name' => 'Auth User',
                'email' => 'auth@test.local',
                'password' => bcrypt('secret123'),
            ]);

            $response = $this->postJson('/api/v1/auth/login', [
                'email' => 'auth@test.local',
                'password' => 'secret123',
            ]);

            $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'access_token',
                        'token_type',
                        'expires_in',
                    ],
                ])
                ->assertJson([
                    'success' => true,
                    'data' => ['token_type' => 'bearer'],
                ]);

            expect($response->json('data.access_token'))->toBeString()->not->toBeEmpty();
        });

        it('rejects invalid credentials with 401', function () {
            User::create([
                'name' => 'Auth User',
                'email' => 'auth@test.local',
                'password' => bcrypt('secret123'),
            ]);

            $response = $this->postJson('/api/v1/auth/login', [
                'email' => 'auth@test.local',
                'password' => 'wrong-password',
            ]);

            $response->assertStatus(401)
                ->assertJson([
                    'success' => false,
                ]);
        });

        it('validates required fields', function () {
            $response = $this->postJson('/api/v1/auth/login', []);

            $response->assertStatus(422)
                ->assertJsonStructure(['success', 'message', 'errors']);
        });
    });

    describe('Protected routes (no token)', function () {
        it('rejects POST /pickups without a token', function () {
            $response = $this->postJson('/api/v1/pickups', [
                'household_id' => 'fake',
                'type' => 'organic',
            ]);

            $response->assertStatus(401);
        });

        it('rejects GET /payments without a token', function () {
            $response = $this->getJson('/api/v1/payments');

            $response->assertStatus(401);
        });

        it('does NOT require auth on GET /households', function () {
            $response = $this->getJson('/api/v1/households');

            $response->assertStatus(200);
        });
    });
});
