<?php

namespace App\Jobs;

use App\Models\Refund;
use App\Models\User;
use App\Services\RefundService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessRefundJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /**
     * @var array<int, int>
     */
    public array $backoff = [10, 60, 300];

    public function __construct(
        public string $refundId,
        public string $adminId,
        public string $adminReference
    ) {
        $this->onQueue('payments');
    }

    public function handle(
        RefundService $refundService
    ): void {
        $refund = Refund::query()
            ->findOrFail($this->refundId);

        $admin = User::query()
            ->findOrFail($this->adminId);

        $refundService->process(
            $refund,
            $admin,
            $this->adminReference
        );
    }
}
