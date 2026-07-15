<?php

namespace Tests\Feature;

use App\Models\Household;

describe('Report API', function () {
    beforeEach(function () {
        $this->baseUrl = '/api/v1/reports';
    });

    describe('GET /reports/waste-summary', function () {
        it('can get waste summary', function () {
            $response = $this->getJson($this->baseUrl . '/waste-summary');

            $response->assertStatus(200)
                ->assertJsonStructure(['success', 'message', 'data']);
        });
    });

    describe('GET /reports/payment-summary', function () {
        it('can get payment summary', function () {
            $response = $this->getJson($this->baseUrl . '/payment-summary');

            $response->assertStatus(200)
                ->assertJsonStructure(['success', 'message', 'data']);
        });
    });

    describe('GET /reports/households/{id}/history', function () {
        it('can get household history', function () {
            $household = Household::factory()->create();

            $response = $this->getJson("{$this->baseUrl}/households/{$household->_id}/history");

            $response->assertStatus(200)
                ->assertJsonStructure(['success', 'message', 'data' => ['household', 'pickups', 'payments']]);
        });
    });
});
