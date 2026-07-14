<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Waste;
use App\Repositories\Contracts\WasteRepositoryInterface;
use Carbon\Carbon;

class AutoCancelStaleWaste extends Command
{
    protected $signature = 'waste:auto-cancel';
    protected $description = 'Cancel stale waste pickups';

    public function handle(WasteRepositoryInterface $wasteRepo): void
    {
        $wastes = Waste::whereNotIn('status', ['completed', 'canceled'])
            ->get();

        foreach ($wastes as $waste) {
            if ($waste->autoCancelAfterDays() !== null) {
                $limit = Carbon::parse($waste->created_at)->addDays($waste->autoCancelAfterDays());
                if ($limit->isPast()) {
                    $wasteRepo->update($waste, ['status' => 'canceled']);
                    $this->info("Canceled waste: {$waste->id}");
                }
            }
        }
    }
}
