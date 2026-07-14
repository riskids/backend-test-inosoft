<?php

namespace App\Providers;

use App\Repositories\Contracts\HouseholdRepositoryInterface;
use App\Repositories\Eloquent\HouseholdRepository;
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
    ];

    public function register(): void
    {
        //
    }
}
