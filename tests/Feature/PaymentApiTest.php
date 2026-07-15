<?php

namespace Tests\Feature;

use App\Models\Household;
use App\Models\Payment;

describe('Payment API', function () {
    beforeEach(function () {
        $this->baseUrl = '/api/v1/payments';
    });

    describe('POST /payments', function () {
        it('can create a manual payment', function () {
            $household = Household::factory()->create();

            $data = [
                'household_id' => (string) $household->_id,
                'amount' => 50000,
                'payment_date' => now()->toISOString(),
                'status' => 'pending',
            ];

            $response = $this->postJson($this->baseUrl, $data);

            $response->assertStatus(201)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => ['_id', 'household_id', 'amount', 'status', 'created_at', 'updated_at']
                ])
                ->assertJson([
                    'success' => true,
                    'message' => 'Payment created successfully',
                ])
                ->assertJsonPath('data.status', 'pending');
        });

        it('validates required fields', function () {
            $response = $this->postJson($this->baseUrl, []);

            $response->assertStatus(422)
                ->assertJsonStructure(['success', 'message', 'errors'])
                ->assertJson([
                    'success' => false,
                ]);
        });

        it('validates amount is required', function () {
            $household = Household::factory()->create();

            $response = $this->postJson($this->baseUrl, [
                'household_id' => (string) $household->_id,
            ]);

            $response->assertStatus(422)
                ->assertJsonPath('errors.amount.0', 'The amount field is required.');
        });

        it('validates household_id is required', function () {
            $response = $this->postJson($this->baseUrl, [
                'amount' => 50000,
            ]);

            $response->assertStatus(422)
                ->assertJsonPath('errors.household_id.0', 'The household id field is required.');
        });
    });

    describe('GET /payments', function () {
        it('can list payments', function () {
            $response = $this->getJson($this->baseUrl);

            $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data'
                ]);
        });

        it('can filter by status', function () {
            $response = $this->getJson($this->baseUrl . '?status=pending');

            $response->assertStatus(200);
        });

        it('can filter by household_id', function () {
            $household = Household::factory()->create();

            $response = $this->getJson($this->baseUrl . '?household_id=' . (string) $household->_id);

            $response->assertStatus(200);
        });
    });

    describe('PUT /payments/{id}/confirm', function () {
        it('can confirm a pending payment', function () {
            $household = Household::factory()->create();
            $payment = Payment::factory()->create([
                'household_id' => (string) $household->_id,
                'status' => 'pending',
            ]);

            $response = $this->putJson("{$this->baseUrl}/{$payment->_id}/confirm");

            $response->assertStatus(200)
                ->assertJsonPath('data.status', 'paid');
        });

        it('rejects confirming an already paid payment (409)', function () {
            $household = Household::factory()->create();
            $payment = Payment::factory()->create([
                'household_id' => (string) $household->_id,
                'status' => 'paid',
            ]);

            $response = $this->putJson("{$this->baseUrl}/{$payment->_id}/confirm");

            $response->assertStatus(409)
                ->assertJson([
                    'success' => false,
                ]);
        });

        it('rejects confirming a failed payment (409)', function () {
            $household = Household::factory()->create();
            $payment = Payment::factory()->create([
                'household_id' => (string) $household->_id,
                'status' => 'failed',
            ]);

            $response = $this->putJson("{$this->baseUrl}/{$payment->_id}/confirm");

            $response->assertStatus(409)
                ->assertJson([
                    'success' => false,
                ]);
        });
    });
});