<?php

namespace Tests\Feature;

use App\Models\Household;
use App\Models\Payment;
use App\Models\Waste;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportApiTest extends TestCase
{
    public function test_can_get_waste_summary(): void
    {
        $response = $this->getJson('/api/v1/reports/waste-summary');
        $response->assertStatus(200)
                 ->assertJsonStructure(['success', 'message', 'data']);
    }

    public function test_can_get_payment_summary(): void
    {
        $response = $this->getJson('/api/v1/reports/payment-summary');
        $response->assertStatus(200)
                 ->assertJsonStructure(['success', 'message', 'data']);
    }

    public function test_can_get_household_history(): void
    {
        $household = Household::factory()->create();
        
        $response = $this->getJson("/api/v1/reports/households/{$household->id}/history");
        $response->assertStatus(200)
                 ->assertJsonStructure(['success', 'message', 'data' => ['household', 'pickups', 'payments']]);
    }
}
