<?php

namespace App\Exports\Excel;

use App\Repositories\RefundRepository;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class RefundsExport implements
    FromCollection,
    WithMapping,
    WithHeadings,
    WithColumnWidths,
    WithStyles,
    WithColumnFormatting
{
    private RefundRepository $refundRepository;

    public function __construct()
    {
        $this->refundRepository = app(RefundRepository::class);
    }

    private function displayWhen(bool $condition, $value)
    {
        return $condition ? ($value ?? '-') : '-';
    }

    /**
     * Get all refunds
     */
    public function collection()
    {
        return $this->refundRepository
            ->getRefundsQuery()
            ->get();
    }

    /**
     * Map each refund row
     */
    public function map($refund): array
    {
        $showProcessor = $refund->processed_by ?? false;

        return [
            $refund->refund_no,
            $refund->order->order_no,
            $refund->amount,
            __($refund->status->value),
            $refund->order->user->name ?? $refund->order->name,
            $this->displayWhen($showProcessor, $refund->processor?->name),
            $this->displayWhen($showProcessor, formatDateTime($refund->processed_at)),
            formatDateTime($refund->created_at),
            $refund->notes ?? '-',
        ];
    }

    /**
     * Column headings
     */
    public function headings(): array
    {
        return [
            __('Refund No'),
            __('Order No'),
            __('Amount'),
            __('Status'),
            __('Customer Name'),
            __('Processed By'),
            __('Processed At'),
            __('Created At'),
            __('Notes'),
        ];
    }

    /**
     * Column widths
     */
    public function columnWidths(): array
    {
        return [
            'A' => 20, // Refund No
            'B' => 20, // Order No
            'C' => 15, // Amount
            'D' => 12, // Status
            'E' => 25, // Customer Name
            'F' => 25, // Processed By
            'G' => 20, // Processed At
            'H' => 20, // Created At
            'I' => 40, // Notes
        ];
    }

    /**
     * Apply styles: bold header + center alignment + RTL support
     */
    public function styles(Worksheet $sheet)
    {
        $isArabic = app()->getLocale() === 'ar';

        // Bold headers
        $sheet->getStyle('A1:I1')->getFont()->setBold(true);

        // Center all content
        $sheet->getStyle('A1:I' . ($this->refundRepository->getRefundsQuery()->count() + 1))
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Flip sheet for Arabic
        if ($isArabic) {
            $sheet->setRightToLeft(true);
        }
    }

    /**
     * Force Excel to treat Amount as a real number
     */
    public function columnFormats(): array
    {
        return [
            'C' => NumberFormat::FORMAT_NUMBER_00,
        ];
    }
}
