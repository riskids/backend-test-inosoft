<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

/**
 * Waste base model — Day 1 stub.
 *
 * Polymorphism (newFromBuilder, TYPE_MAP, subclass overrides) is implemented
 * in Day 2. Today we just need the class to exist so Household::wastes() can
 * resolve its `hasMany` target.
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

    public function household()
    {
        return $this->belongsTo(Household::class);
    }
}
