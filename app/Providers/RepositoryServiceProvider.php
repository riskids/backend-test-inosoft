<?php

namespace App\Providers;

use App\Repositories\Contracts\HouseholdRepositoryInterface;
use App\Repositories\Eloquent\HouseholdRepository;
use App\Repositories\Contracts\WasteRepositoryInterface;
use App\Repositories\Eloquent\WasteRepository;
use App\Repositories\Contracts\PaymentRepositoryInterface;
use App\Repositories\Eloquent\PaymentRepository;
use Illuminate\Support\ServiceProvider;

/**
 * Binds Repository interfaces to their Eloquent implementations.
 *
 * Keeping persistence binding out of AppServiceProvider means the Repository
 * layer is one obvious place to look when wiring up a new module.
 */
class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * @var array<class-string, class-string>
     */
    public array $bindings = [
        HouseholdRepositoryInterface::class => HouseholdRepository::class,
        WasteRepositoryInterface::class     => WasteRepository::class,
        PaymentRepositoryInterface::class   => PaymentRepository::class,
    ];

    public function register(): void
    {
        //
    }
}
