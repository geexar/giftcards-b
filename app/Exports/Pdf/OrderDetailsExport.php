<?php

namespace App\Exports\Pdf;

use App\Models\Order;
use App\Services\Admin\OrderService;
use Mpdf\Mpdf;

class OrderDetailsExport
{
    private OrderService $orderService;

    public function __construct()
    {
        $this->orderService = app(OrderService::class);
    }

    public function download(Order $order)
    {
        $html = view('pdf.order-details', [
            'order' => $order,
        ])->render();

        $mpdf = new Mpdf(config('mpdf.options'));
        $mpdf->WriteHTML($html);

        $pdf = $mpdf->Output("order_{$order->order_no}.pdf", 'S');

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="order_' . $order->order_no . '.pdf"',
            'Cache-Control' => 'private, max-age=0, must-revalidate',
            'Pragma' => 'public',
        ]);
    }
}
