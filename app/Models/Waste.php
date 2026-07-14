<?php

namespace App\Models;

use App\Models\Waste\WasteElectronic;
use App\Models\Waste\WasteOrganic;
use App\Models\Waste\WastePaper;
use App\Models\Waste\WastePlastic;
use MongoDB\Laravel\Eloquent\Model;

/**
 * Waste base model with type-based polymorphic resolution.
 *
 * When any Waste record is loaded from MongoDB, newFromBuilder() automatically
 * hydrates it as the correct subclass based on the `type` discriminator field.
 * This is the foundation that lets WasteService work with waste polymorphically
 * without any switch/if statements on type.
 */
class Waste extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'wastes';

    protected $fillable = [
        'household_id',
        'type',
        'status',
        'pickup_date',
        'safety_check',
    ];

    protected $casts = [
        'pickup_date'  => 'datetime',
        'safety_check' => 'boolean',
        'created_at'   => 'datetime',
        'updated_at'   => 'datetime',
    ];

    /** Maps type string → fully-qualified class name for polymorphic hydration. */
    public const TYPE_MAP = [
        'organic'    => WasteOrganic::class,
        'plastic'    => WastePlastic::class,
        'paper'      => WastePaper::class,
        'electronic' => WasteElectronic::class,
    ];

    /**
     * Resolve every record read from Mongo into its real subclass.
     * This is the core of the polymorphism — services never need to check type.
     */
    public function newFromBuilder($attributes = [], $connection = null)
    {
        $attributes = (array) $attributes;
        $type = $attributes['type'] ?? null;
        $class = self::TYPE_MAP[$type] ?? static::class;

        $model = new $class([]);
        $model->exists = true;
        $model->setRawAttributes($attributes, true);
        $model->setConnection($connection ?: $this->getConnectionName());
        $model->fireModelEvent('retrieved', false);

        return $model;
    }

    public function household()
    {
        return $this->belongsTo(Household::class);
    }

    // ---- Polymorphic hooks — default implementations ----

    public function typeLabel(): string
    {
        return 'generic';
    }

    public function completionAmount(): int
    {
        return 50000;
    }

    public function autoCancelAfterDays(): ?int
    {
        return null;
    }

    public function requiresPreScheduleCheck(): bool
    {
        return false;
    }

    public function passesPreScheduleCheck(): bool
    {
        return true;
    }
}
