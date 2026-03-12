<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\OrderRequest;
use App\Http\Requests\User\RateOrderRequest;
use App\Http\Resources\BaseCollection;
use App\Http\Resources\User\OrderResource;
use App\Repositories\OrderRepository;
use App\Services\User\OrderService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(
        private OrderService $orderService,
        private OrderRepository $orderRepository
    ) {}

    public function index(Request $request)
    {
        $orders = $this->orderRepository->getPaginatedUserOrders(auth('user')->id());

        return success(new BaseCollection($orders, OrderResource::class));
    }

    public function store(OrderRequest $request)
    {
        $response = $this->orderService->createOrder($request->validated());

        if ($request->payment_method == 'wallet') {
            $data['message'] = $response->message;
            $data['order_no'] = $response->order->order_no;
            $data['status'] = $response->order->status->value;
            $data['gifted_email'] = $response->order->gifted_email;
        }

        if ($request->payment_method == 'card') {
            $data['payment_url'] = $response->payment_url;
        }

        return success($data);
    }

    public function show(string $orderNo)
    {
        $order = $this->orderService->getOrder($orderNo);

        return success(new OrderResource($order));
    }

    public function cancelOrder(string $orderNo)
    {
        $this->orderService->cancelOrder($orderNo);

        return success(true);
    }

    public function cancelOrderItem(string $orderItemNo)
    {
        $this->orderService->cancelOrderItem($orderItemNo);

        return success(true);
    }

    public function rate(string $orderNo, RateOrderRequest $request)
    {
        $this->orderService->rateOrder($orderNo, $request->validated());

        return success(true);
    }

    public function statusInfo(string $orderNo)
    {
        $orderData = $this->orderService->orderStatusInfo($orderNo);

        return success($orderData);
    }
}
