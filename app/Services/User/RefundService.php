<?php

namespace App\Services\User;

use App\Enums\OrderItemStatus;
use App\Enums\RefundStatus;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Order;
use App\Models\Refund;
use App\Repositories\RefundRepository;
use App\Repositories\TransactionRepository;
use App\Services\Admin\RefundUpdateService;
use App\Services\Admin\TransactionService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class RefundService
{
    public function __construct(
        private RefundRepository $refundRepository,
        private TransactionService $transactionService,
        private TransactionRepository $transactionRepository,
        private RefundUpdateService $refundUpdateService
    ) {}

    /**
     * Handle refund for a given order.
     * Checks refundable items, calculates refund amount, and creates/updates refund and transaction.
     */
    public function handleRefund(Order $order)
    {
        // Get items eligible for refund
        $refundableItems = $this->getRefundableItems($order);

        // Skip if there are no refundable items
        if ($refundableItems->isEmpty()) {
            return;
        }

        // Calculate refund amount based on unfulfilled items
        $refundAmount = $this->calculateRefundAmount($refundableItems);

        // Create or update the refund record
        $refund = $this->getOrCreateRefund($order, $refundAmount);

        // Create or update associated refund transaction
        $this->handleRefundTransaction($order, $refund, $refundAmount);
    }

    /**
     * Filter order items that are eligible for refund.
     */
    private function getRefundableItems(Order $order)
    {
        $refundableStatuses = [
            OrderItemStatus::PARTIALLY_FULFILLED,
            OrderItemStatus::FAILED,
            OrderItemStatus::REJECTED,
            OrderItemStatus::CANCELED,
        ];

        return $order->items->whereIn('status', $refundableStatuses);
    }

    /**
     * Calculate the refund amount based on order totals.
     */
    private function calculateRefundAmount(Collection $refundableItems): float
    {
        return $refundableItems->sum(function ($item) {
            return $item->user_facing_price * ($item->quantity - $item->fulfilled_quantity);
        });
    }

    /**
     * Get existing refund or create a new one if it does not exist.
     */
    private function getOrCreateRefund(Order $order, float $refundAmount)
    {
        $refund = $order->refund;

        if ($refund) {
            $oldAmount = $refund->amount;

            $refund->update(['amount' => $refundAmount]);

            // Only create a log if the amount actually changed
            if ($oldAmount != $refundAmount) {
                $this->refundUpdateService->store($refund, $refund->status->value, $refund->status->value, $refundAmount);
            }
        } else {
            $refund = $this->refundRepository->create([
                'refund_no' => $this->generateRefundNo(),
                'order_id' => $order->id,
                'user_id' => $order->user_id,
                'amount' => $refundAmount,
                'status' => RefundStatus::PENDING,
            ]);

            $this->refundUpdateService->store($refund, null, RefundStatus::PENDING->value, $refund->amount);
        }

        return $refund;
    }

    /**
     * Create or update the refund transaction.
     * Ensures the refund is properly linked to a transaction for accounting.
     */
    private function handleRefundTransaction(Order $order, Refund $refund, float $refundAmount)
    {
        $transaction = $refund->transaction;

        if ($transaction) {
            $transaction->update(['amount' => $refundAmount]);
        } else {
            $this->transactionRepository->create([
                'transaction_no'  => $this->transactionService->generateTransactionNo(),
                'type'            => TransactionType::REFUND,
                'user_id'         => $order->user_id,
                'order_id'        => $order->id,
                'refund_id'       => $refund->id,
                'amount'          => $refundAmount,
                'affects_wallet'  => false,
                'status'          => TransactionStatus::PENDING,
            ]);
        }
    }

    /**
     * Generate a unique refund number.
     */
    private function generateRefundNo(): string
    {
        do {
            $code = 'RFD-' . strtoupper(Str::random(8));
            $exists = $this->refundRepository->getByRefundNo($code);
        } while ($exists);

        return $code;
    }
}
