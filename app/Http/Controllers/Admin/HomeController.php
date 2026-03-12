<?php

namespace App\Http\Controllers\Admin;

use App\Exports\Excel\CustomerSegmentsExport;
use App\Exports\Excel\OrderStatusesCountExport;
use App\Exports\Excel\RefundSummaryExport;
use App\Exports\Excel\TopItemsExport;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\TopItemResource;
use App\Services\Admin\HomeService;
use Maatwebsite\Excel\Facades\Excel;

class HomeController extends Controller
{
    public function __construct(private HomeService $homeService) {}

    public function index()
    {
        $data = $this->homeService->getData();

        return success($data);
    }

    public function topProducts()
    {
        $items = $this->homeService->getTopItems();

        return success(TopItemResource::collection($items));
    }

    public function exportExcelOrderStatusCount()
    {
        $data = $this->homeService->getOrderStatuses();

        return Excel::download(new OrderStatusesCountExport($data), 'order_statuses.xlsx');
    }

    public function exportCustomerSegments()
    {
        $customers = $this->homeService->getCustomerSegments();

        return Excel::download(new CustomerSegmentsExport($customers), 'customer_segments.xlsx');
    }

    public function exportTopProducts()
    {
        $items = $this->homeService->getTopItems();

        return Excel::download(new TopItemsExport($items), 'top_products.xlsx');
    }

    public function exportRefundSummary()
    {
        $data = $this->homeService->getRefunds();

        return Excel::download(new RefundSummaryExport($data), 'refund_summary.xlsx');
    }
}
