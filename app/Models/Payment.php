<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

/**
 * Payment model — Day 1 stub.
 *
 * Real fields + business rules arrive in Day 2. The class is here today so
 * Household::payments() can resolve its `hasMany` target.
 */
class Payment extends Model
{
    use HasFactory;
    protected $connection = 'mongodb';
    protected $collection = 'payments';

    protected $fillable = [
        'household_id',
        'amount',
        'payment_date',
        'status',
    ];

    protected $casts = [
        'amount'        => 'integer',
        'payment_date'  => 'datetime',
        'created_at'    => 'datetime',
        'updated_at'    => 'datetime',
    ];

    public function household()
    {
        return $this->belongsTo(Household::class);
    }
}
