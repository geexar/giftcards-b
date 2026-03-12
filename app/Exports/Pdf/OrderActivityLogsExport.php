<?php

namespace App\Exports\Pdf;

use App\Models\Order;
use App\Services\Admin\OrderService;
use Mpdf\Mpdf;

class OrderActivityLogsExport
{
    private OrderService $orderService;

    public function __construct()
    {
        $this->orderService = app(OrderService::class);
    }

    public function download(Order $order)
    {
        $logs = $this->orderService->getFormattedLogsForPdf($order->id);

        $html = view('pdf.order-logs', [
            'order' => $order,
            'logs' => $logs,
        ])->render();

        $mpdf = new Mpdf(config('mpdf.options'));
        $mpdf->WriteHTML($html);

        $pdf = $mpdf->Output("order_activity_logs_{$order->order_no}.pdf", 'S');

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="order_activity_logs_' . $order->order_no . '.pdf"',
            'Cache-Control' => 'private, max-age=0, must-revalidate',
            'Pragma' => 'public',
        ]);
    }
}
