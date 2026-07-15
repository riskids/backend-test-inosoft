<?php

use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you pass to the pest function will be executed before every
| test. This allows you to set up any global state required by your tests.
|
*/

pest()->extend(TestCase::class)
    ->beforeEach(function () {
        // Truncate MongoDB collections before each test
        // MongoDB doesn't support transactions like SQL databases,
        // so we clean up manually
    })
    ->afterEach(function () {
        // Clean up after each test
    })
    ->in('Feature', 'Unit');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain
| conditions. Expectations allow you to define those conditions.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});