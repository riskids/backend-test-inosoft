<?php

namespace App\Models\Waste;

/**
 * Electronic waste — requires safety check before scheduling,
 * pays 100000 on completion.
 */
class WasteElectronic extends \App\Models\Waste
{
    public function typeLabel(): string
    {
        return 'electronic';
    }

    public function completionAmount(): int
    {
        return 100000;
    }

    public function autoCancelAfterDays(): ?int
    {
        return null;
    }

    public function requiresPreScheduleCheck(): bool
    {
        return true;
    }

    public function passesPreScheduleCheck(): bool
    {
        return (bool) $this->safety_check;
    }
}
