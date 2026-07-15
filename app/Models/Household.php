<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Eloquent\SoftDeletes;

/**
 * Household model — maps to the `households` MongoDB collection.
 *
 * Implements soft deletes via the laravel-mongodb SoftDeletes trait. We never
 * physically remove a household — we only flip `deleted_at` — so that any
 * related waste / payment history stays referentially intact.
 *
 * @property string      $_id
 * @property string      $owner_name
 * @property string      $address
 * @property string|null $block
 * @property string|null $no
 */
class Household extends Model
{
    use HasFactory, SoftDeletes;

    protected $connection = 'mongodb';
    protected $collection = 'households';

    protected $fillable = [
        'owner_name',
        'address',
        'block',
        'no',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Wastes belonging to this household.
     */
    public function wastes()
    {
        return $this->hasMany(Waste::class, 'household_id');
    }

    /**
     * Payments belonging to this household.
     */
    public function payments()
    {
        return $this->hasMany(Payment::class, 'household_id');
    }
}
