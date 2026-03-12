<?php

namespace App\Services\Admin;

use App\Enums\RefundStatus;
use App\Enums\TransactionStatus;
use App\Models\Refund;
use App\Repositories\RefundRepository;
use App\Services\User\OrderCommunicationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class RefundService
{
    public function __construct(
        private RefundRepository $refundRepository,
        private RefundUpdateService $refundUpdateService,
        private OrderCommunicationService $orderCommunicationService
    ) {}

    /**
     * Retrieves a Refund model by its ID or throws a 404 exception.
     */
    public function getRefund(string $id): Refund
    {
        $refund = $this->refundRepository->getById($id);

        if (!$refund) {
            throw new NotFoundHttpException('Refund not found');
        }

        return $refund;
    }

    /**
     * Updates the status of a refund, delegating the specific state change logic.
     */
    public function update(string $id, array $data): void
    {
        $refund = $this->getRefund($id);
        $newStatus = $data['status'];
        $notes = $data['notes'] ?? null;

        DB::transaction(function () use ($refund, $newStatus, $notes) {

            $oldStatus = $refund->status;

            if ($newStatus === RefundStatus::PENDING->value && $refund->status === RefundStatus::PROCESSED) {
                $this->handleRevertToPending($refund, $notes);
            }

            if ($newStatus === RefundStatus::PROCESSED->value && $refund->status === RefundStatus::PENDING) {
                $this->handleProcessRefund($refund, $notes);
            }

            // Store status update
            $this->refundUpdateService->store($refund, $oldStatus->value, $newStatus, $refund->amount, auth('admin')->user());
        });

        // notify refund update if needed
        $this->orderCommunicationService->notifyRefundCompleted($refund->order);
    }

    /**
     * Handles the specific logic to revert a processed refund back to PENDING.
     */
    private function handleRevertToPending(Refund $refund, ?string $notes): void
    {
        // 1. Update Refund State
        $this->refundRepository->update($refund, [
            'status' => RefundStatus::PENDING,
            'processed_by' => null,
            'processed_at' => null,
            'notes' => $notes
        ]);

        // 2. Update Transaction State
        $refund->transaction->update(['status' => TransactionStatus::PENDING]);
    }

    /**
     * Handles the specific logic to approve a pending refund and set it to PROCESSED.
     */
    private function handleProcessRefund(Refund $refund, ?string $notes): void
    {
        // BEnsure the order is in a final state before processing the refund
        if (!$refund->can_make_processed) {
            throw new NotFoundHttpException(__("Refund cannot be processed until order is in a final state"));
        }

        // 1. Update Refund State
        $this->refundRepository->update($refund, [
            'status' => RefundStatus::PROCESSED,
            'processed_by' => auth('admin')->id(),
            'processed_at' => now(),
            'notes' => $notes
        ]);

        // 2. Mark the linked transaction as successful
        $refund->transaction->update(['status' => TransactionStatus::SUCCESS]);
    }

    /**
     * Generates a unique refund number (e.g., RFD-A4B5C6D7).
     */
    public function generateRefundNo(): string
    {
        do {
            $code = 'RFD-' . strtoupper(Str::random(8));
            $exists = $this->refundRepository->getByRefundNo($code);
        } while ($exists);

        return $code;
    }
}
