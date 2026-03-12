<?php

namespace App\Services\User;

use App\Enums\OrderStatus;
use App\Enums\RefundStatus;
use App\Models\Order;
use App\Mail\{OrderCompletedMail, OrderProcessedMail, OrderFailedMail, OrderCanceledMail, RecipientGiftMail, RefundCompletedMail};
use App\Notifications\{OrderCompletedNotification, OrderProcessedNotification, OrderFailedNotification, OrderIsBeingProcessedNotification, RefundActionRequiredNotification, RefundCompletedNotification};
use App\Repositories\AdminRepository;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log;

class OrderCommunicationService
{
    public function __construct(private AdminRepository $adminRepository) {}

    /**
     * Send notifications based on order status transitions.
     * * @param Order $order The updated order model
     * @param mixed $oldStatus The status before the update (captured before transaction)
     */
    public function notifyOrderUpdates(Order $order, OrderStatus $oldStatus): void
    {
        // 1. Guard: Only proceed if the status actually changed
        if ($oldStatus === $order->status) {
            return;
        }

        // Ensure relations needed for emails are loaded
        $order->load(['user', 'refund', 'items.product', 'items.variantValue', 'items.codes']);

        $purchaser = $order->user_id ? $order->user : $order->email;

        // 1. COMPLETED
        if ($order->status == OrderStatus::COMPLETED) {
            if ($order->user_id) {
                $order->user->notify(new OrderCompletedNotification($order));
            }
            Mail::to($purchaser)->send(new OrderCompletedMail($order));
        }

        // 2. PROCESSED
        if ($order->status == OrderStatus::PROCESSED) {
            if ($order->user_id) {
                $order->user->notify(new OrderProcessedNotification($order));
            }
            Mail::to($purchaser)->send(new OrderProcessedMail($order));
        }

        // 3. FAILED
        if ($order->status == OrderStatus::FAILED) {
            if ($order->user_id) {
                $order->user->notify(new OrderFailedNotification($order));
            }
            Mail::to($purchaser)->send(new OrderFailedMail($order));
        }

        // 4. CANCELED
        if ($order->status == OrderStatus::CANCELED) {
            Mail::to($purchaser)->send(new OrderCanceledMail($order));
        }

        // 5. GIFT RECIPIENT
        if ($order->is_gifted && in_array($order->status, [OrderStatus::COMPLETED, OrderStatus::PROCESSED])) {
            Mail::to($order->gifted_email)->send(new RecipientGiftMail($order));
        }

        // 6. MID-PROCESS (Push Notifications only)
        if (in_array($order->status, [
            OrderStatus::PARTIALLY_COMPLETED,
            OrderStatus::PENDING_CONFIRMATION,
            OrderStatus::PROCESSING,
            OrderStatus::AWAITING_ACTION
        ])) {
            if ($order->user_id) {
                $order->user->notify(new OrderIsBeingProcessedNotification($order));
            }
        }

        // 7. ADMIN REFUND NOTIFICATIONS (Processed, Failed or Canceled)
        if (in_array($order->status, [OrderStatus::PROCESSED, OrderStatus::FAILED, OrderStatus::CANCELED])) {
            $admins = $this->adminRepository->getNotifiedAdmins('view refund');
            Notification::send($admins, new RefundActionRequiredNotification($order));
        }
    }

    public function notifyRefundCompleted(Order $order): void
    {
        // Only proceed if the refund is processed
        if ($order->refund->status != RefundStatus::PROCESSED) {
            return;
        }

        $purchaser = $order->user_id ? $order->user : $order->email;

        // Send refund completed email
        Mail::to($purchaser)->send(new RefundCompletedMail($order));

        // Send refund completed notification
        if ($order->user_id) {
            $order->user->notify(new RefundCompletedNotification($order));
        }
    }
}
