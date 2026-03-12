<?php

namespace App\Services\Admin;

use App\Enums\OrderStatus;
use App\Repositories\OrderItemRepository;
use App\Repositories\OrderRepository;
use App\Repositories\ProductRepository;
use App\Repositories\RefundRepository;
use App\Repositories\TransactionRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\Collection;

class HomeService
{
    public function __construct(
        private OrderRepository $orderRepository,
        private UserRepository $userRepository,
        private RefundRepository $refundRepository,
        private ProductRepository $productRepository,
        private TransactionRepository $transactionRepository,
        private OrderItemRepository $orderItemRepository
    ) {}

    // Main method
    public function getData(): array
    {
        return [
            'totals' => $this->getTotals(),
            'order_statuses' => $this->getOrderStatuses(),
            'refunds' => $this->getRefunds(),
            'users' => $this->getUsers(),
        ];
    }

    // Totals: orders, revenue, net revenue, profit
    public function getTotals(): array
    {
        $totalOrders = $this->orderRepository->totalOrdersCount();
        $totalRevenue = $this->orderRepository->totalPaidAmount();
        $totalProcessedRefundAmount = $this->refundRepository->totalProcessedRefundAmount();
        $netRevenue = $totalRevenue - $totalProcessedRefundAmount;
        $totalProfit = $this->transactionRepository->totalProfits();
        $totalUsers = $this->userRepository->totalUsersCount();
        $activeUsers = $this->userRepository->usersWithOrdersCount();

        return [
            'total_orders' => $totalOrders,
            'total_revenue' => (string) $totalRevenue,
            'total_processed_refunds_amount' => formatMoney($totalProcessedRefundAmount),
            'net_revenue' => formatMoney((string) $netRevenue),
            'total_profit' => formatMoney($totalProfit),
            'total_users' => $totalUsers,
            'active_users' => $activeUsers,
        ];
    }

    // Order statuses and percentages
    public function getOrderStatuses(): array
    {
        $totalOrders = $this->orderRepository->totalOrdersCount();

        // Order status counts
        $processingOrdersCount = $this->orderRepository->countProcessing();
        $completedOrdersCount = $this->orderRepository->countCompleted();
        $processedOrdersCount = $this->orderRepository->countProcessed();
        $partiallyCompletedOrdersCount = $this->orderRepository->countPartiallyCompleted();
        $pendingConfirmationOrdersCount = $this->orderRepository->countPendingConfirmation();
        $awaitingActionOrdersCount = $this->orderRepository->countAwaitingAction();
        $canceledOrdersCount = $this->orderRepository->countCanceled();
        $rejectedOrdersCount = $this->orderRepository->countRejected();
        $failedOrdersCount = $this->orderRepository->countFailed();

        // Order status percentages using general helper
        return [
            ['status' => OrderStatus::PROCESSING, 'count' => $processingOrdersCount, 'percentage' => formatPercentage($processingOrdersCount, $totalOrders)],
            ['status' => OrderStatus::COMPLETED, 'count' => $completedOrdersCount, 'percentage' => formatPercentage($completedOrdersCount, $totalOrders)],
            ['status' => OrderStatus::PROCESSED, 'count' => $processedOrdersCount, 'percentage' => formatPercentage($processedOrdersCount, $totalOrders)],
            ['status' => OrderStatus::PARTIALLY_COMPLETED, 'count' => $partiallyCompletedOrdersCount, 'percentage' => formatPercentage($partiallyCompletedOrdersCount, $totalOrders)],
            ['status' => OrderStatus::PENDING_CONFIRMATION, 'count' => $pendingConfirmationOrdersCount, 'percentage' => formatPercentage($pendingConfirmationOrdersCount, $totalOrders)],
            ['status' => OrderStatus::AWAITING_ACTION, 'count' => $awaitingActionOrdersCount, 'percentage' => formatPercentage($awaitingActionOrdersCount, $totalOrders)],
            ['status' => OrderStatus::CANCELED, 'count' => $canceledOrdersCount, 'percentage' => formatPercentage($canceledOrdersCount, $totalOrders)],
            ['status' => OrderStatus::REJECTED, 'count' => $rejectedOrdersCount, 'percentage' => formatPercentage($rejectedOrdersCount, $totalOrders)],
            ['status' => OrderStatus::FAILED, 'count' => $failedOrdersCount, 'percentage' => formatPercentage($failedOrdersCount, $totalOrders)],
        ];
    }

    // Refund counts and rate
    public function getRefunds(): array
    {
        $totalPendingRefundsCount = $this->refundRepository->totalPendingRefunds();
        $totalProcessedRefundsCount = $this->refundRepository->totalProcessedRefunds();
        $totalRefundsCount = $totalPendingRefundsCount + $totalProcessedRefundsCount;

        $totalOrders = $this->orderRepository->totalOrdersCount();
        $refundRate = formatPercentage($totalRefundsCount, $totalOrders);

        return [
            'total' => $totalRefundsCount,
            'pending' => $totalPendingRefundsCount,
            'processed' => $totalProcessedRefundsCount,
            'rate' => $refundRate,
        ];
    }

    // Users: returning and guest
    public function getUsers(): array
    {
        $returningUsersCount = $this->userRepository->returningUsersCount();
        $guestUsersCount = $this->orderRepository->guestOrdersCount();
        $newCustomers = $this->userRepository->getUsersCount();

        return [
            'returning' => $returningUsersCount,
            'guest' => $guestUsersCount,
            'new_customers' => $newCustomers,
        ];
    }

    public function getCustomerSegments(): Collection
    {
        $customers = collect();

        // New users
        $newUsers = $this->userRepository->getUsers();
        foreach ($newUsers as $user) {
            $customers->push((object)[
                'name' => $user->name,
                'email' => $user->email,
                'segment' => 'New Customer',
                'registration_date' => formatDate($user->created_at),
                'last_order_date' => formatDate($user->latestOrder?->created_at),
            ]);
        }

        // Returning users
        $returningUsers = $this->userRepository->getReturningUsers(); // collection of User models
        foreach ($returningUsers as $user) {
            $customers->push((object)[
                'name' => $user->name,
                'email' => $user->email,
                'segment' => 'Returning Customer',
                'registration_date' => formatDate($user->created_at),
                'last_order_date' => formatDate($user->latestOrder?->created_at),
            ]);
        }

        // Guest orders
        $guestOrders = $this->orderRepository->getGuestOrders(); // collection of Order models
        foreach ($guestOrders as $order) {
            $latestGuestOrder = $this->orderRepository->getLatestGuestOrder($order->email);
            $customers->push((object)[
                'name' => $order->name,
                'email' => $order->email,
                'segment' => 'Guest',
                'registration_date' => null,
                'last_order_date' => formatDate($latestGuestOrder->created_at),
            ]);
        }

        return $customers;
    }

    public function getTopItems()
    {
        $items = $this->orderItemRepository->getTopItems();

        return $items;
    }
}
