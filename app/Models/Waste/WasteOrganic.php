<?php

namespace App\Models\Waste;

/**
 * Organic waste — auto-cancels after 3 days if not scheduled.
 */
class WasteOrganic extends \App\Models\Waste
{
    public function typeLabel(): string
    {
        return 'organic';
    }

    public function completionAmount(): int
    {
        return 50000;
    }

    public function autoCancelAfterDays(): ?int
    {
        return 3;
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
