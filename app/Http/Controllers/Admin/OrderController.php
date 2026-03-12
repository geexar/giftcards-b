<?php

namespace App\Http\Controllers\Admin;

use App\Exports\Excel\OrdersExport;
use App\Exports\Pdf\OrderActivityLogsExport;
use App\Exports\Pdf\OrderDetailsExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\GetOrdersRequest;
use App\Http\Requests\Admin\OrderNotesRequest;
use App\Http\Requests\Admin\UpdateOrderRequest;
use App\Http\Resources\Admin\OrderResource;
use App\Http\Resources\BaseCollection;
use App\Repositories\OrderRepository;
use App\Services\Admin\OrderService;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Maatwebsite\Excel\Facades\Excel;

class OrderController extends Controller implements HasMiddleware
{
    public function __construct(
        private OrderService $orderService,
        private OrderRepository $orderRepository,
    ) {}

    public static function middleware(): array
    {
        return [
            new Middleware('can:show orders', only: ['index', 'exportOrdersExcel']),
            new Middleware('can:view order', only: ['show', 'logs', 'exportPdf', 'exportOrderActivityLogsPdf']),
            new Middleware('can:update order', only: ['update']),
        ];
    }

    public function index(GetOrdersRequest $request)
    {
        $products = $this->orderRepository->getPaginatedOrders();

        return success(new BaseCollection($products, OrderResource::class));
    }

    public function show(string $id)
    {
        $order = $this->orderService->getOrder($id);

        return success(OrderResource::make($order));
    }

    public function logs(string $id)
    {
        $logs = $this->orderService->getOrderLogs($id);

        return success($logs);
    }

    public function update(string $id, UpdateOrderRequest $request)
    {
        $this->orderService->update($id, $request->validated());

        return success(true);
    }

    public function updateNotes(OrderNotesRequest $request, string $id)
    {
        $this->orderService->updateNotes($id, $request->notes);

        return success(true);
    }

    public function getNeedActionOrdersCount()
    {
        $count = $this->orderRepository->getNeedActionOrdersCount();

        return success(['count' => $count]);
    }

    public function exportOrdersExcel()
    {
        return Excel::download(new OrdersExport(), 'orders.xlsx');
    }

    public function exportOrderPdf(string $id, OrderDetailsExport $export)
    {
        $order = $this->orderService->getOrder($id);

        return $export->download($order);
    }

    public function exportOrderActivityLogsPdf(string $id, OrderActivityLogsExport $export)
    {
        $order = $this->orderService->getOrder($id);

        return $export->download($order);
    }
}
