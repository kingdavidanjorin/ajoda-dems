<?php

declare(strict_types=1);

namespace App\Services\Payments;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\PurchaseStatus;
use App\Models\Payment;
use App\Models\Purchase;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class PaymentService
{
    public function record(
        Purchase $purchase,
        User $accountsOfficer,
        string $amount,
        PaymentMethod $paymentMethod,
        string $paidAt,
        ?string $transactionReference = null,
        ?string $notes = null,
    ): Payment {
        return DB::transaction(
            function () use ($purchase, $accountsOfficer, $amount, $paymentMethod, $paidAt, $transactionReference, $notes): Payment {
                $lockedPurchase = Purchase::query()
                    ->whereKey($purchase->getKey())
                    ->lockForUpdate()
                    ->firstOrFail();

                $this->ensurePurchaseCanReceivePayment(
                    $lockedPurchase,
                );

                $transactionReference =
                    $this->normalizeTransactionReference(
                        $transactionReference,
                    );

                if (
                    $transactionReference !== null
                    && Payment::query()
                        ->where(
                            'transaction_reference',
                            $transactionReference,
                        )
                        ->exists()
                ) {
                    throw ValidationException::withMessages([
                        'transaction_reference' => 'This transaction reference has already been recorded.',
                    ]);
                }

                $payment = Payment::query()->create([
                    'reference' => $this->generateReference(),
                    'purchase_id' => $lockedPurchase->id,
                    'customer_id' => $lockedPurchase->customer_id,
                    'recorded_by' => $accountsOfficer->id,
                    'status' => PaymentStatus::PendingVerification->value,
                    'amount' => $amount,
                    'payment_method' => $paymentMethod->value,
                    'transaction_reference' => $transactionReference,
                    'paid_at' => Carbon::parse($paidAt),
                    'notes' => $notes,
                ]);

                $lockedPurchase->forceFill([
                    'status' => PurchaseStatus::PaymentUnderReview->value,
                ])->save();

                return $payment->fresh([
                    'purchase',
                    'customer',
                    'recordedBy',
                ]);
            },
            3,
        );
    }

    public function verify(
        Payment $payment,
        User $accountsOfficer,
        ?string $verificationNotes = null,
    ): Payment {
        return DB::transaction(
            function () use ($payment, $accountsOfficer, $verificationNotes): Payment {
                $lockedPayment = Payment::query()
                    ->whereKey($payment->getKey())
                    ->lockForUpdate()
                    ->firstOrFail();

                if (
                    $lockedPayment->status
                    === PaymentStatus::Verified
                ) {
                    return $lockedPayment->fresh([
                        'purchase',
                        'customer',
                        'recordedBy',
                        'verifiedBy',
                    ]);
                }

                if (
                    $lockedPayment->status
                    !== PaymentStatus::PendingVerification
                ) {
                    throw ValidationException::withMessages([
                        'payment' => 'Only a pending payment can be verified.',
                    ]);
                }

                $purchase = Purchase::query()
                    ->whereKey($lockedPayment->purchase_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $this->ensurePurchaseCanReviewPayments(
                    $purchase,
                );

                $lockedPayment->forceFill([
                    'status' => PaymentStatus::Verified->value,
                    'verified_by' => $accountsOfficer->id,
                    'verification_notes' => $verificationNotes,
                    'verified_at' => now(),
                    'rejected_at' => null,
                ])->save();

                $this->refreshPurchasePaymentStatus(
                    $purchase,
                );

                return $lockedPayment->fresh([
                    'purchase',
                    'customer',
                    'recordedBy',
                    'verifiedBy',
                ]);
            },
            3,
        );
    }

    public function reject(
        Payment $payment,
        User $accountsOfficer,
        string $verificationNotes,
    ): Payment {
        return DB::transaction(
            function () use ($payment, $accountsOfficer, $verificationNotes): Payment {
                $lockedPayment = Payment::query()
                    ->whereKey($payment->getKey())
                    ->lockForUpdate()
                    ->firstOrFail();

                if (
                    $lockedPayment->status
                    === PaymentStatus::Rejected
                ) {
                    return $lockedPayment->fresh([
                        'purchase',
                        'customer',
                        'recordedBy',
                        'verifiedBy',
                    ]);
                }

                if (
                    $lockedPayment->status
                    !== PaymentStatus::PendingVerification
                ) {
                    throw ValidationException::withMessages([
                        'payment' => 'Only a pending payment can be rejected.',
                    ]);
                }

                $purchase = Purchase::query()
                    ->whereKey($lockedPayment->purchase_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $this->ensurePurchaseCanReviewPayments(
                    $purchase,
                );

                $lockedPayment->forceFill([
                    'status' => PaymentStatus::Rejected->value,
                    'verified_by' => $accountsOfficer->id,
                    'verification_notes' => $verificationNotes,
                    'rejected_at' => now(),
                    'verified_at' => null,
                ])->save();

                $this->refreshPurchasePaymentStatus(
                    $purchase,
                );

                return $lockedPayment->fresh([
                    'purchase',
                    'customer',
                    'recordedBy',
                    'verifiedBy',
                ]);
            },
            3,
        );
    }

    private function refreshPurchasePaymentStatus(
        Purchase $purchase,
    ): void {
        $hasPendingPayments = Payment::query()
            ->where('purchase_id', $purchase->id)
            ->where(
                'status',
                PaymentStatus::PendingVerification->value,
            )
            ->exists();

        if ($hasPendingPayments) {
            $purchase->forceFill([
                'status' => PurchaseStatus::PaymentUnderReview->value,
            ])->save();

            return;
        }

        $verifiedTotal = (string) Payment::query()
            ->where('purchase_id', $purchase->id)
            ->where(
                'status',
                PaymentStatus::Verified->value,
            )
            ->sum('amount');

        $verifiedAmountInKobo =
            $this->moneyToMinorUnits($verifiedTotal);

        $approvedPriceInKobo =
            $this->moneyToMinorUnits(
                (string) $purchase->approved_price,
            );

        $newStatus = $verifiedAmountInKobo
            >= $approvedPriceInKobo
            ? PurchaseStatus::ReadyForConfirmation
            : PurchaseStatus::AwaitingPayment;

        $purchase->forceFill([
            'status' => $newStatus->value,
        ])->save();
    }

    private function ensurePurchaseCanReceivePayment(
        Purchase $purchase,
    ): void {
        if (
            ! in_array(
                $purchase->status,
                [
                    PurchaseStatus::AwaitingPayment,
                    PurchaseStatus::PaymentUnderReview,
                ],
                true,
            )
        ) {
            throw ValidationException::withMessages([
                'purchase' => 'This purchase cannot accept additional payments.',
            ]);
        }
    }

    private function ensurePurchaseCanReviewPayments(
        Purchase $purchase,
    ): void {
        if (
            in_array(
                $purchase->status,
                [
                    PurchaseStatus::Confirmed,
                    PurchaseStatus::Cancelled,
                ],
                true,
            )
        ) {
            throw ValidationException::withMessages([
                'purchase' => 'Payments for this purchase can no longer be reviewed.',
            ]);
        }
    }

    private function normalizeTransactionReference(
        ?string $transactionReference,
    ): ?string {
        if ($transactionReference === null) {
            return null;
        }

        $transactionReference = trim(
            $transactionReference,
        );

        return $transactionReference === ''
            ? null
            : $transactionReference;
    }

    private function moneyToMinorUnits(
        string $amount,
    ): int {
        $amount = trim($amount);

        [$wholePart, $decimalPart] = array_pad(
            explode('.', $amount, 2),
            2,
            '0',
        );

        $decimalPart = substr(
            str_pad($decimalPart, 2, '0'),
            0,
            2,
        );

        return ((int) $wholePart * 100)
            + (int) $decimalPart;
    }

    private function generateReference(): string
    {
        do {
            $reference = sprintf(
                'AJD-PAY-%s-%s',
                now()->format('Ymd'),
                Str::upper(Str::random(6)),
            );
        } while (
            Payment::query()
                ->where('reference', $reference)
                ->exists()
        );

        return $reference;
    }
}
