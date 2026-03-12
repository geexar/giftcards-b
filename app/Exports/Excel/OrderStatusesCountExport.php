<?php

namespace App\Exports\Excel;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class OrderStatusesCountExport implements
    FromCollection,
    WithMapping,
    WithHeadings,
    WithColumnWidths,
    WithStyles,
    WithColumnFormatting
{
    private Collection $data;

    public function __construct(array $data)
    {
        $this->data = collect($data);
    }

    public function collection()
    {
        return $this->data;
    }

    public function map($row): array
    {
        return [
            __(ucwords(str_replace('_', ' ', strtolower($row['status']->value)))),
            $row['count'],
            $row['percentage'],
        ];
    }

    public function headings(): array
    {
        return [
            __('Status'),
            __('Count'),
            __('Percentage'),
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 25,
            'B' => 15,
            'C' => 20,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $isArabic = app()->getLocale() === 'ar';

        // Center all content
        $sheet->getStyle('A1:C' . ($this->data->count() + 1))
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Bold headers
        $sheet->getStyle('A1:C1')->getFont()->setBold(true);

        // Flip sheet for Arabic
        if ($isArabic) {
            $sheet->setRightToLeft(true);
        }
    }

    public function columnFormats(): array
    {
        return [
            'B' => NumberFormat::FORMAT_NUMBER,        // Force display of 0
            'C' => NumberFormat::FORMAT_PERCENTAGE_00,
        ];
    }
}
