<?php

namespace App\Models\Waste;

/**
 * Plastic waste — uses default amounts, no special rules.
 */
class WastePlastic extends \App\Models\Waste
{
    public function typeLabel(): string
    {
        return 'plastic';
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
