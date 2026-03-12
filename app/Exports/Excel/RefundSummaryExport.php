<?php

namespace App\Exports\Excel;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class RefundSummaryExport implements
    FromCollection,
    WithMapping,
    WithHeadings,
    WithColumnWidths,
    WithStyles
{
    private Collection $data;

    public function __construct(array $refundSummary)
    {
        $this->data = collect([
            [
                'label' => __('Total Refunds'),
                'value' => $refundSummary['total'],
            ],
            [
                'label' => __('Pending Refunds'),
                'value' => $refundSummary['pending'],
            ],
            [
                'label' => __('Processed Refunds'),
                'value' => $refundSummary['processed'],
            ],
            [
                'label' => __('Refund Rate'),
                'value' => $refundSummary['rate'],
            ],
        ]);
    }

    public function collection(): Collection
    {
        return $this->data;
    }

    public function map($row): array
    {
        return [
            $row['label'],
            $row['value'],
        ];
    }

    public function headings(): array
    {
        return [
            __('Metric'),
            __('Value'),
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 30,
            'B' => 20,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $isArabic = app()->getLocale() === 'ar';

        // Center all content
        $sheet->getStyle('A1:B' . ($this->data->count() + 1))
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Header bold
        $sheet->getStyle('A1:B1')->getFont()->setBold(true);

        // Optional: flip sheet for Arabic
        if ($isArabic) {
            $sheet->setRightToLeft(true);
        }
    }
}
