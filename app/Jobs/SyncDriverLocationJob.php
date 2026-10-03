<?php

namespace App\Jobs;

use App\Models\Driver;
use App\Models\Trip;
use App\Services\LocationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SyncDriverLocationJob implements ShouldQueue
{
    use Queueable;

    /**
     * @param array<string, mixed> $data
     */
    public function __construct(
        public readonly Driver $driver,
        public readonly Trip $trip,
        public readonly array $data
    ) {
        $this->onQueue(
            'realtime'
        );
    }

    public function handle(
        LocationService $locationService
    ): void {
        $locationService->record(
            driver: $this->driver,
            trip: $this->trip,
            data: $this->data,
        );
    }
}
