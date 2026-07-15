<?php

namespace Tests\Unit;

use App\Models\Waste;
use App\Models\Waste\WasteElectronic;
use App\Models\Waste\WasteOrganic;
use App\Models\Waste\WastePaper;
use App\Models\Waste\WastePlastic;
use App\Repositories\Contracts\WasteRepositoryInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;

beforeEach(function () {
    $this->wasteRepo = app(WasteRepositoryInterface::class);
});

describe('Waste Repository Polymorphism', function () {
    it('hydrates the correct waste subclass from type discriminator', function () {
        // Create an electronic waste directly in the collection
        $electronicWaste = $this->wasteRepo->create([
            'household_id' => (string) new \MongoDB\BSON\ObjectId(),
            'type' => 'electronic',
            'status' => 'pending',
            'safety_check' => true,
        ]);

        // Fetch it back - should be hydrated as WasteElectronic
        $fetched = $this->wasteRepo->findOrFail($electronicWaste->_id);

        expect($fetched)->toBeInstanceOf(WasteElectronic::class)
            ->and($fetched->type)->toBe('electronic')
            ->and($fetched->typeLabel())->toBe('electronic')
            ->and($fetched->completionAmount())->toBe(100000)
            ->and($fetched->requiresPreScheduleCheck())->toBeTrue();
    });

    it('hydrates organic waste correctly', function () {
        $waste = $this->wasteRepo->create([
            'household_id' => (string) new \MongoDB\BSON\ObjectId(),
            'type' => 'organic',
            'status' => 'pending',
        ]);

        $fetched = $this->wasteRepo->findOrFail($waste->_id);

        expect($fetched)->toBeInstanceOf(WasteOrganic::class)
            ->and($fetched->type)->toBe('organic')
            ->and($fetched->autoCancelAfterDays())->toBe(3)
            ->and($fetched->completionAmount())->toBe(50000);
    });

    it('hydrates plastic waste correctly', function () {
        $waste = $this->wasteRepo->create([
            'household_id' => (string) new \MongoDB\BSON\ObjectId(),
            'type' => 'plastic',
            'status' => 'pending',
        ]);

        $fetched = $this->wasteRepo->findOrFail($waste->_id);

        expect($fetched)->toBeInstanceOf(WastePlastic::class)
            ->and($fetched->type)->toBe('plastic')
            ->and($fetched->autoCancelAfterDays())->toBeNull();
    });

    it('hydrates paper waste correctly', function () {
        $waste = $this->wasteRepo->create([
            'household_id' => (string) new \MongoDB\BSON\ObjectId(),
            'type' => 'paper',
            'status' => 'pending',
        ]);

        $fetched = $this->wasteRepo->findOrFail($waste->_id);

        expect($fetched)->toBeInstanceOf(WastePaper::class)
            ->and($fetched->type)->toBe('paper');
    });
});

describe('Waste Repository CRUD Operations', function () {
    it('creates a new waste record', function () {
        $householdId = (string) new \MongoDB\BSON\ObjectId();

        $waste = $this->wasteRepo->create([
            'household_id' => $householdId,
            'type' => 'organic',
            'status' => 'pending',
        ]);

        expect($waste)->toBeInstanceOf(Waste::class)
            ->and($waste->household_id)->toBe($householdId)
            ->and($waste->type)->toBe('organic')
            ->and($waste->status)->toBe('pending');
    });

    it('updates waste status', function () {
        $waste = $this->wasteRepo->create([
            'household_id' => (string) new \MongoDB\BSON\ObjectId(),
            'type' => 'plastic',
            'status' => 'pending',
        ]);

        $updated = $this->wasteRepo->update($waste, [
            'status' => 'scheduled',
            'pickup_date' => now()->addDay(),
        ]);

        expect($updated->status)->toBe('scheduled')
            ->and($updated->pickup_date)->not->toBeNull();
    });
});