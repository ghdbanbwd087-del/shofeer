<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PaymentReviewStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RejectPaymentRequest;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PaymentController extends Controller
{
    /**
     * قائمة الدفعات بثلاثة تبويبات.
     */
    public function index(
        Request $request
    ): View {
        $allowedStatuses = [
            PaymentReviewStatus::Pending->value,
            PaymentReviewStatus::Approved->value,
            PaymentReviewStatus::Rejected->value,
        ];

        $status =
            $request
                ->string('status')
                ->toString();

        if (
            ! in_array(
                $status,
                $allowedStatuses,
                true
            )
        ) {
            $status =
                PaymentReviewStatus::Pending
                    ->value;
        }

        $payments =
            Payment::query()
                ->with([
                    'booking.user',
                    'booking.trip.fromCity',
                    'booking.trip.toCity',
                    'reviewer',
                ])
                ->where(
                    'status',
                    $status
                )
                ->latest(
                    'submitted_at'
                )
                ->paginate(20)
                ->withQueryString();

        $counts =
            Payment::query()
                ->select(
                    'status',
                    DB::raw(
                        'COUNT(*) as total'
                    )
                )
                ->groupBy('status')
                ->pluck(
                    'total',
                    'status'
                );

        return view(
            'admin.payments.index',
            compact(
                'payments',
                'status',
                'counts'
            )
        );
    }

    /**
     * عرض إثبات الدفع بشكل خاص.
     */
    public function proof(
        Payment $payment
    ): StreamedResponse {
        abort_unless(
            Storage::disk('local')
                ->exists(
                    $payment->proof_path
                ),
            404
        );

        $extension =
            pathinfo(
                $payment->proof_path,
                PATHINFO_EXTENSION
            );

        $filename =
            'payment-proof-'.
            $payment->id.
            '.'.
            $extension;

        return Storage::disk('local')
            ->response(
                $payment->proof_path,
                $filename,
                [
                    'Content-Disposition' => 'inline; filename="'.
                        $filename.
                        '"',
                ]
            );
    }

    /**
     * تأكيد الدفع.
     */
    public function approve(
        Request $request,
        Payment $payment,
        PaymentService $paymentService
    ): RedirectResponse {
        $reviewedPayment =
            $paymentService->approve(
                $payment,
                $request->user()
            );

        if (
            $reviewedPayment->status ===
            PaymentReviewStatus::Rejected
        ) {
            return back()
                ->withErrors([
                    'payment' => $reviewedPayment
                        ->rejection_reason
                        ?? 'تعذر اعتماد الدفع.',
                ]);
        }

        return back()->with(
            'success',
            'تم تأكيد الدفع والحجز بنجاح.'
        );
    }

    /**
     * رفض الدفع.
     */
    public function reject(
        RejectPaymentRequest $request,
        Payment $payment,
        PaymentService $paymentService
    ): RedirectResponse {
        $paymentService->reject(
            $payment,
            $request->user(),
            $request->validated()[
                'rejection_reason'
            ]
        );

        return back()->with(
            'success',
            'تم رفض الدفع وإتاحة المقعد من جديد.'
        );
    }
}
