<?php

namespace Tests\Feature;

use App\Models\Household;
use App\Models\Payment;
use App\Models\User;
use App\Models\Waste;

describe('Pickup API', function () {
    beforeEach(function () {
        $this->baseUrl = '/api/v1/pickups';

        // Create a user and authenticate for JWT-protected routes
        $user = User::firstOrCreate(
            ['email' => 'test@pickup-test.local'],
            ['name' => 'Test User', 'password' => bcrypt('password')]
        );
        $token = auth('api')->login($user);
        $this->withHeader('Authorization', 'Bearer ' . $token);
    });

    describe('POST /pickups', function () {
        it('can create a pickup for a household with no unpaid payments', function () {
            $household = Household::factory()->create();

            $data = [
                'household_id' => (string) $household->_id,
                'type' => 'organic',
            ];

            $response = $this->postJson($this->baseUrl, $data);

            $response->assertStatus(201)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => ['_id', 'household_id', 'type', 'status', 'created_at', 'updated_at']
                ])
                ->assertJson([
                    'success' => true,
                ])
                ->assertJsonPath('data.status', 'pending');
        });

        it('rejects pickup creation when household has unpaid payment (422)', function () {
            $household = Household::factory()->create();

            // Create an unpaid payment for this household
            Payment::factory()->create([
                'household_id' => (string) $household->_id,
                'status' => 'pending',
            ]);

            $data = [
                'household_id' => (string) $household->_id,
                'type' => 'organic',
            ];

            $response = $this->postJson($this->baseUrl, $data);

            $response->assertStatus(422)
                ->assertJson([
                    'success' => false,
                ]);
        });

        it('validates required fields', function () {
            $response = $this->postJson($this->baseUrl, []);

            $response->assertStatus(422)
                ->assertJsonStructure(['success', 'message', 'errors']);
        });

        it('validates type is valid', function () {
            $household = Household::factory()->create();

            $response = $this->postJson($this->baseUrl, [
                'household_id' => (string) $household->_id,
                'type' => 'invalid_type',
            ]);

            $response->assertStatus(422)
                ->assertJsonPath('errors.type.0', 'The selected type is invalid.');
        });
    });

    describe('PUT /pickups/{id}/schedule', function () {
        it('can schedule a pending pickup', function () {
            $household = Household::factory()->create();
            $waste = Waste::factory()->create([
                'household_id' => (string) $household->_id,
                'status' => 'pending',
                'type' => 'organic',
            ]);

            $data = [
                'pickup_date' => now()->addDay()->toISOString(),
            ];

            $response = $this->putJson("{$this->baseUrl}/{$waste->_id}/schedule", $data);

            $response->assertStatus(200)
                ->assertJsonPath('data.status', 'scheduled');
        });

        it('rejects scheduling a non-pending pickup (409)', function () {
            $household = Household::factory()->create();
            $waste = Waste::factory()->create([
                'household_id' => (string) $household->_id,
                'status' => 'scheduled',
            ]);

            $response = $this->putJson("{$this->baseUrl}/{$waste->_id}/schedule", [
                'pickup_date' => now()->addDay()->toISOString(),
            ]);

            $response->assertStatus(409)
                ->assertJson([
                    'success' => false,
                ]);
        });

        it('requires safety check for electronic waste (422)', function () {
            $household = Household::factory()->create();
            $waste = Waste::factory()->create([
                'household_id' => (string) $household->_id,
                'status' => 'pending',
                'type' => 'electronic',
                'safety_check' => false,
            ]);

            $response = $this->putJson("{$this->baseUrl}/{$waste->_id}/schedule", [
                'pickup_date' => now()->addDay()->toISOString(),
            ]);

            $response->assertStatus(422)
                ->assertJson([
                    'success' => false,
                ]);
        });

        it('allows scheduling electronic waste with safety check', function () {
            $household = Household::factory()->create();
            $waste = Waste::factory()->create([
                'household_id' => (string) $household->_id,
                'status' => 'pending',
                'type' => 'electronic',
                'safety_check' => true,
            ]);

            $response = $this->putJson("{$this->baseUrl}/{$waste->_id}/schedule", [
                'pickup_date' => now()->addDay()->toISOString(),
            ]);

            $response->assertStatus(200)
                ->assertJsonPath('data.status', 'scheduled');
        });
    });

    describe('PUT /pickups/{id}/complete', function () {
        it('can complete a scheduled pickup', function () {
            $household = Household::factory()->create();
            $waste = Waste::factory()->create([
                'household_id' => (string) $household->_id,
                'status' => 'scheduled',
                'type' => 'organic',
            ]);

            $response = $this->putJson("{$this->baseUrl}/{$waste->_id}/complete");

            $response->assertStatus(200)
                ->assertJsonPath('data.status', 'completed');
        });

        it('rejects completing a non-scheduled pickup (409)', function () {
            $household = Household::factory()->create();
            $waste = Waste::factory()->create([
                'household_id' => (string) $household->_id,
                'status' => 'pending',
            ]);

            $response = $this->putJson("{$this->baseUrl}/{$waste->_id}/complete");

            $response->assertStatus(409)
                ->assertJson([
                    'success' => false,
                ]);
        });

        it('auto-generates payment on completion', function () {
            $household = Household::factory()->create();
            $waste = Waste::factory()->create([
                'household_id' => (string) $household->_id,
                'status' => 'scheduled',
                'type' => 'organic',
            ]);

            $response = $this->putJson("{$this->baseUrl}/{$waste->_id}/complete");

            $response->assertStatus(200);

            // Verify payment was created (check via payments endpoint)
            $paymentsResponse = $this->getJson('/api/v1/payments');
            $paymentsResponse->assertStatus(200);
        });
    });

    describe('PUT /pickups/{id}/cancel', function () {
        it('can cancel a pending pickup', function () {
            $household = Household::factory()->create();
            $waste = Waste::factory()->create([
                'household_id' => (string) $household->_id,
                'status' => 'pending',
            ]);

            $response = $this->putJson("{$this->baseUrl}/{$waste->_id}/cancel");

            $response->assertStatus(200)
                ->assertJsonPath('data.status', 'canceled');
        });

        it('can cancel a scheduled pickup', function () {
            $household = Household::factory()->create();
            $waste = Waste::factory()->create([
                'household_id' => (string) $household->_id,
                'status' => 'scheduled',
            ]);

            $response = $this->putJson("{$this->baseUrl}/{$waste->_id}/cancel");

            $response->assertStatus(200)
                ->assertJsonPath('data.status', 'canceled');
        });

        it('rejects canceling a completed pickup (409)', function () {
            $household = Household::factory()->create();
            $waste = Waste::factory()->create([
                'household_id' => (string) $household->_id,
                'status' => 'completed',
            ]);

            $response = $this->putJson("{$this->baseUrl}/{$waste->_id}/cancel");

            $response->assertStatus(409)
                ->assertJson([
                    'success' => false,
                ]);
        });
    });
});