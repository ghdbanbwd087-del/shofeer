<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RefundStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProcessRefundRequest;
use App\Http\Requests\Admin\RejectRefundRequest;
use App\Jobs\ProcessRefundJob;
use App\Models\Refund;
use App\Services\RefundService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class RefundController extends Controller
{
    public function index(
        Request $request
    ): View {
        $allowedStatuses = [
            RefundStatus::Pending->value,
            RefundStatus::Processed->value,
            RefundStatus::Rejected->value,
        ];

        $status = $request
            ->string('status')
            ->toString();

        if (! in_array($status, $allowedStatuses, true)) {
            $status = RefundStatus::Pending->value;
        }

        $refunds = Refund::query()
            ->with([
                'payment',
                'booking.user',
                'booking.trip.fromCity',
                'booking.trip.toCity',
                'requester',
                'reviewer',
            ])
            ->where('status', $status)
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $counts = Refund::query()
            ->select(
                'status',
                DB::raw('COUNT(*) as total')
            )
            ->groupBy('status')
            ->pluck('total', 'status');

        return view(
            'admin.refunds.index',
            compact(
                'refunds',
                'status',
                'counts'
            )
        );
    }

    public function process(
        ProcessRefundRequest $request,
        Refund $refund
    ): RedirectResponse {
        $refund->loadMissing('payment');

        Gate::authorize(
            'refund',
            $refund->payment
        );

        ProcessRefundJob::dispatch(
            (string) $refund->id,
            (string) $request->user()->id,
            $request->validated('admin_reference')
        );

        return back()->with(
            'success',
            'تم إرسال عملية الاسترداد إلى طابور المعالجة.'
        );
    }

    public function reject(
        RejectRefundRequest $request,
        Refund $refund,
        RefundService $refundService
    ): RedirectResponse {
        $refund->loadMissing('payment');

        Gate::authorize(
            'refund',
            $refund->payment
        );

        $refundService->reject(
            $refund,
            $request->user(),
            $request->validated('rejection_reason')
        );

        return back()->with(
            'success',
            'تم رفض طلب الاسترداد.'
        );
    }
}
