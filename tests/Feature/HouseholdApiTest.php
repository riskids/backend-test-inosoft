<?php

namespace Tests\Feature;

use App\Models\Household;

describe('Household API', function () {
    beforeEach(function () {
        $this->baseUrl = '/api/v1/households';
    });

    describe('POST /households', function () {
        it('can create a household', function () {
            $data = [
                'owner_name' => 'John Doe',
                'address' => '123 Main Street, Jakarta',
                'block' => 'A',
                'no' => '10',
            ];

            $response = $this->postJson($this->baseUrl, $data);

            $response->assertStatus(201)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => ['_id', 'owner_name', 'address', 'block', 'no', 'created_at', 'updated_at']
                ])
                ->assertJson([
                    'success' => true,
                    'message' => 'Household created successfully',
                ]);
        });

        it('validates required fields', function () {
            $response = $this->postJson($this->baseUrl, []);

            $response->assertStatus(422)
                ->assertJsonStructure(['success', 'message', 'errors'])
                ->assertJson([
                    'success' => false,
                ]);
        });

        it('validates owner_name is required', function () {
            $response = $this->postJson($this->baseUrl, [
                'address' => '123 Main Street',
            ]);

            $response->assertStatus(422)
                ->assertJsonPath('errors.owner_name.0', 'The owner name field is required.');
        });

        it('validates address is required', function () {
            $response = $this->postJson($this->baseUrl, [
                'owner_name' => 'John Doe',
            ]);

            $response->assertStatus(422)
                ->assertJsonPath('errors.address.0', 'The address field is required.');
        });
    });

    describe('GET /households', function () {
        it('can list households', function () {
            $response = $this->getJson($this->baseUrl);

            $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data'
                ]);
        });

        it('can filter by block', function () {
            $response = $this->getJson($this->baseUrl . '?block=A');

            $response->assertStatus(200);
        });

        it('can filter by no', function () {
            $response = $this->getJson($this->baseUrl . '?no=10');

            $response->assertStatus(200);
        });

        it('can search by owner_name', function () {
            $response = $this->getJson($this->baseUrl . '?search=Budi');

            $response->assertStatus(200);
        });
    });

    describe('GET /households/{id}', function () {
        it('can show a household', function () {
            $household = Household::factory()->create();

            $response = $this->getJson("{$this->baseUrl}/{$household->_id}");

            $response->assertStatus(200)
                ->assertJsonPath('data.owner_name', $household->owner_name);
        });

        it('returns 404 for non-existent household', function () {
            $response = $this->getJson($this->baseUrl . '/507f1f77bcf86cd799439011');

            $response->assertStatus(404)
                ->assertJson([
                    'success' => false,
                    'message' => 'Resource not found.',
                ]);
        });
    });

    describe('PUT /households/{id}', function () {
        it('can update a household', function () {
            $household = Household::factory()->create();

            $response = $this->putJson("{$this->baseUrl}/{$household->_id}", [
                'owner_name' => 'Updated Name',
                'address' => '456 Updated Street',
            ]);

            $response->assertStatus(200)
                ->assertJsonPath('data.owner_name', 'Updated Name');
        });

        it('returns 404 for non-existent household on update', function () {
            $response = $this->putJson($this->baseUrl . '/507f1f77bcf86cd799439011', [
                'owner_name' => 'Test',
            ]);

            $response->assertStatus(404);
        });
    });

    describe('DELETE /households/{id}', function () {
        it('can soft delete a household', function () {
            $household = Household::factory()->create();

            $response = $this->deleteJson("{$this->baseUrl}/{$household->_id}");

            $response->assertStatus(204);

            // Verify it's soft deleted (excluded from index)
            $indexResponse = $this->getJson($this->baseUrl);
            $indexResponse->assertStatus(200);
        });

        it('returns 404 for non-existent household on delete', function () {
            $response = $this->deleteJson($this->baseUrl . '/507f1f77bcf86cd799439011');

            $response->assertStatus(404);
        });
    });
});