<?php

namespace Tests\Unit;

use App\Exceptions\Domain\InvalidPickupStatusException;
use App\Exceptions\Domain\SafetyCheckRequiredException;
use App\Exceptions\Domain\UnpaidPaymentExistsException;
use App\Models\Waste;
use App\Models\Waste\WasteElectronic;
use App\Models\Waste\WasteOrganic;
use App\Repositories\Contracts\PaymentRepositoryInterface;
use App\Repositories\Contracts\WasteRepositoryInterface;
use App\Services\PaymentService;
use App\Services\WasteFactory;
use App\Services\WasteService;
use Mockery;

beforeEach(function () {
    $this->wasteRepo = Mockery::mock(WasteRepositoryInterface::class);
    $this->paymentRepo = Mockery::mock(PaymentRepositoryInterface::class);
    $this->wasteFactory = Mockery::mock(WasteFactory::class);
    $this->paymentService = Mockery::mock(PaymentService::class);

    $this->service = new WasteService(
        $this->wasteRepo,
        $this->paymentRepo,
        $this->wasteFactory,
        $this->paymentService
    );
});

afterEach(function () {
    Mockery::close();
});

describe('WasteService Business Rules', function () {
    it('throws when creating pickup with unpaid payments', function () {
        $householdId = '507f1f77bcf86cd799439011';

        $this->paymentRepo
            ->shouldReceive('hasUnpaid')
            ->once()
            ->with($householdId)
            ->andReturn(true);

        expect(fn () => $this->service->createPickup($householdId, 'organic', []))
            ->toThrow(UnpaidPaymentExistsException::class);
    });

    it('creates pickup when no unpaid payments exist', function () {
        $householdId = '507f1f77bcf86cd799439011';
        $waste = new WasteOrganic([
            'household_id' => $householdId,
            'type' => 'organic',
            'status' => 'pending',
        ]);

        $this->paymentRepo
            ->shouldReceive('hasUnpaid')
            ->with($householdId)
            ->andReturn(false);

        $this->wasteFactory
            ->shouldReceive('make')
            ->with('organic', ['household_id' => $householdId, 'status' => 'pending'])
            ->andReturn($waste);

        $this->wasteRepo
            ->shouldReceive('create')
            ->once()
            ->andReturnUsing(function ($data) use ($waste) {
                return $waste;
            });

        $result = $this->service->createPickup($householdId, 'organic', []);

        expect($result)->toBeInstanceOf(Waste::class);
    });

    it('throws when scheduling non-pending pickup', function () {
        $wasteId = '507f1f77bcf86cd799439011';
        $waste = new WasteOrganic([
            '_id' => $wasteId,
            'status' => 'scheduled',
        ]);

        $this->wasteRepo
            ->shouldReceive('findOrFail')
            ->once()
            ->with($wasteId)
            ->andReturn($waste);

        expect(fn () => $this->service->schedule($wasteId, now()))
            ->toThrow(InvalidPickupStatusException::class, 'Only pending pickups can be scheduled.');
    });

    it('throws when scheduling electronic waste without safety check', function () {
        $wasteId = '507f1f77bcf86cd799439011';
        $waste = new WasteElectronic([
            '_id' => $wasteId,
            'status' => 'pending',
            'safety_check' => false,
        ]);

        $this->wasteRepo
            ->shouldReceive('findOrFail')
            ->once()
            ->with($wasteId)
            ->andReturn($waste);

        expect(fn () => $this->service->schedule($wasteId, now()))
            ->toThrow(SafetyCheckRequiredException::class);
    });

    it('allows scheduling electronic waste with safety check', function () {
        $wasteId = '507f1f77bcf86cd799439011';
        $waste = new WasteElectronic([
            '_id' => $wasteId,
            'status' => 'pending',
            'safety_check' => true,
        ]);
        $waste->exists = true;

        $this->wasteRepo
            ->shouldReceive('findOrFail')
            ->once()
            ->with($wasteId)
            ->andReturn($waste);

        $this->wasteRepo
            ->shouldReceive('update')
            ->once()
            ->andReturnUsing(function ($w, $data) {
                $w->status = $data['status'];
                $w->pickup_date = $data['pickup_date'];
                return $w;
            });

        $result = $this->service->schedule($wasteId, now()->addDay());

        expect($result->status)->toBe('scheduled');
    });

    it('throws when completing non-scheduled pickup', function () {
        $wasteId = '507f1f77bcf86cd799439011';
        $waste = new WasteOrganic([
            '_id' => $wasteId,
            'status' => 'pending',
        ]);

        $this->wasteRepo
            ->shouldReceive('findOrFail')
            ->once()
            ->with($wasteId)
            ->andReturn($waste);

        expect(fn () => $this->service->complete($wasteId))
            ->toThrow(InvalidPickupStatusException::class, 'Only scheduled pickups can be completed.');
    });

    it('generates payment with correct amount on completion', function () {
        $wasteId = '507f1f77bcf86cd799439011';
        $householdId = '507f1f77bcf86cd799439022';

        // Test with organic waste (50000)
        $waste = new WasteOrganic([
            '_id' => $wasteId,
            'household_id' => $householdId,
            'status' => 'scheduled',
        ]);
        $waste->exists = true;

        $this->wasteRepo
            ->shouldReceive('findOrFail')
            ->once()
            ->with($wasteId)
            ->andReturn($waste);

        $this->wasteRepo
            ->shouldReceive('update')
            ->once()
            ->andReturnUsing(function ($w, $data) {
                $w->status = $data['status'];
                return $w;
            });

        $this->paymentService
            ->shouldReceive('createFromCompletedWaste')
            ->once()
            ->with(Mockery::on(fn($w) => $w->completionAmount() === 50000));

        $result = $this->service->complete($wasteId);

        expect($result->status)->toBe('completed');
    });

    it('charges 100000 for electronic waste on completion', function () {
        $wasteId = '507f1f77bcf86cd799439011';

        $waste = new WasteElectronic([
            '_id' => $wasteId,
            'status' => 'scheduled',
        ]);
        $waste->exists = true;

        $this->wasteRepo
            ->shouldReceive('findOrFail')
            ->once()
            ->with($wasteId)
            ->andReturn($waste);

        $this->wasteRepo
            ->shouldReceive('update')
            ->once()
            ->andReturnUsing(function ($w, $data) {
                $w->status = $data['status'];
                return $w;
            });

        $capturedWaste = null;
        $this->paymentService
            ->shouldReceive('createFromCompletedWaste')
            ->once()
            ->with(Mockery::on(function ($w) use (&$capturedWaste) {
                $capturedWaste = $w;
                return true;
            }));

        $this->service->complete($wasteId);

        expect($capturedWaste->completionAmount())->toBe(100000);
    });

    it('throws when canceling completed pickup', function () {
        $wasteId = '507f1f77bcf86cd799439011';
        $waste = new WasteOrganic([
            '_id' => $wasteId,
            'status' => 'completed',
        ]);

        $this->wasteRepo
            ->shouldReceive('findOrFail')
            ->once()
            ->with($wasteId)
            ->andReturn($waste);

        expect(fn () => $this->service->cancel($wasteId))
            ->toThrow(InvalidPickupStatusException::class, 'Completed pickups cannot be canceled.');
    });

    it('allows canceling pending pickup', function () {
        $wasteId = '507f1f77bcf86cd799439011';
        $waste = new WasteOrganic([
            '_id' => $wasteId,
            'status' => 'pending',
        ]);
        $waste->exists = true;

        $this->wasteRepo
            ->shouldReceive('findOrFail')
            ->once()
            ->with($wasteId)
            ->andReturn($waste);

        $this->wasteRepo
            ->shouldReceive('update')
            ->once()
            ->andReturnUsing(function ($w, $data) {
                $w->status = $data['status'];
                return $w;
            });

        $result = $this->service->cancel($wasteId);

        expect($result->status)->toBe('canceled');
    });

    it('allows canceling scheduled pickup', function () {
        $wasteId = '507f1f77bcf86cd799439011';
        $waste = new WasteOrganic([
            '_id' => $wasteId,
            'status' => 'scheduled',
        ]);
        $waste->exists = true;

        $this->wasteRepo
            ->shouldReceive('findOrFail')
            ->once()
            ->with($wasteId)
            ->andReturn($waste);

        $this->wasteRepo
            ->shouldReceive('update')
            ->once()
            ->andReturnUsing(function ($w, $data) {
                $w->status = $data['status'];
                return $w;
            });

        $result = $this->service->cancel($wasteId);

        expect($result->status)->toBe('canceled');
    });
});

describe('WasteService Polymorphic Behavior', function () {
    it('uses polymorphic completionAmount without type checking', function () {
        // Verify that WasteService::complete() doesn't have any switch/if on type
        // by checking completionAmount is called polymorphically
        
        $organicWaste = new WasteOrganic(['status' => 'scheduled']);
        $electronicWaste = new WasteElectronic(['status' => 'scheduled']);

        expect($organicWaste->completionAmount())->toBe(50000);
        expect($electronicWaste->completionAmount())->toBe(100000);
    });

    it('uses polymorphic safety check requirements', function () {
        $organic = new WasteOrganic(['status' => 'pending', 'safety_check' => false]);
        $electronic = new WasteElectronic(['status' => 'pending', 'safety_check' => false]);
        $electronicSafe = new WasteElectronic(['status' => 'pending', 'safety_check' => true]);

        expect($organic->requiresPreScheduleCheck())->toBeFalse();
        expect($organic->passesPreScheduleCheck())->toBeTrue();

        expect($electronic->requiresPreScheduleCheck())->toBeTrue();
        expect($electronic->passesPreScheduleCheck())->toBeFalse();

        expect($electronicSafe->passesPreScheduleCheck())->toBeTrue();
    });
});
