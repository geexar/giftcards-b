<?php

namespace App\Exports\Excel;

use App\Repositories\OrderRepository;
use App\Services\Admin\PaymentMethodService;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class OrdersExport implements
    FromCollection,
    WithMapping,
    WithHeadings,
    WithColumnWidths,
    WithStyles,
    WithColumnFormatting
{
    private OrderRepository $orderRepository;
    private PaymentMethodService $paymentMethodService;

    public function __construct()
    {
        $this->orderRepository = app(OrderRepository::class);
        $this->paymentMethodService = app(PaymentMethodService::class);
    }

    public function collection()
    {
        return $this->orderRepository
            ->dashboardOrdersQuery()
            ->get();
    }

    public function map($order): array
    {
        return [
            $order->order_no,
            $order->user?->name ?? $order->name,
            $this->paymentMethodService->getPaymentMethodName($order->payment_method_id),
            $order->items->sum('quantity'),
            $order->total,
            __(ucwords(str_replace('_', ' ', strtolower($order->status->value)))),
            $order->refund?->status?->value ? __(ucwords(str_replace('_', ' ', strtolower($order->refund->status->value)))) : '-',
            formatRating($order->overallRating?->rating) ?? '-',
            formatDateTime($order->created_at),
            formatDateTime($order->updated_at),
        ];
    }

    public function headings(): array
    {
        return [
            __('Order No'),
            __('Customer Name'),
            __('Payment Method'),
            __('Items Count'),
            __('Total'),
            __('Status'),
            __('Refund Status'),
            __('Rating'),
            __('Created At'),
            __('Updated At'),
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 20,
            'B' => 25,
            'C' => 20,
            'D' => 15,
            'E' => 15,
            'F' => 15,
            'G' => 18,
            'H' => 10,
            'I' => 20,
            'J' => 20,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $isArabic = app()->getLocale() === 'ar';

        // Bold header
        $sheet->getStyle('A1:J1')->getFont()->setBold(true);

        // Center all content
        $sheet->getStyle('A1:J' . ($this->collection()->count() + 1))
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Flip sheet for Arabic
        if ($isArabic) {
            $sheet->setRightToLeft(true);
        }
    }

    public function columnFormats(): array
    {
        return [
            'E' => NumberFormat::FORMAT_NUMBER_00, // Total
        ];
    }
}
