<?php

namespace App\Models\Waste;

/**
 * Paper waste — uses default amounts, no special rules.
 */
class WastePaper extends \App\Models\Waste
{
    public function typeLabel(): string
    {
        return 'paper';
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
