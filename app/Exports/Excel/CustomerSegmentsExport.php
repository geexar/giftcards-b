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

class CustomerSegmentsExport implements
    FromCollection,
    WithMapping,
    WithHeadings,
    WithColumnWidths,
    WithStyles
{
    private Collection $data;

    public function __construct(Collection $customers)
    {
        $this->data = $customers;
    }

    public function collection(): Collection
    {
        return $this->data;
    }

    public function map($customer): array
    {
        return [
            $customer->name,
            $customer->email,
            __($customer->segment),
            $customer->registration_date ?? '-',
            $customer->last_order_date ?? '-',
        ];
    }

    public function headings(): array
    {
        return [
            __('Name'),
            __('Email'),
            __('Segment'),
            __('Registration Date'),
            __('Last Order Date'),
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 25,
            'B' => 30,
            'C' => 20,
            'D' => 20,
            'E' => 20,
        ];
    }
    public function styles(Worksheet $sheet)
    {
        // Simple Arabic check
        $isArabic = app()->getLocale() === 'ar';

        // Center alignment for all columns
        $sheet->getStyle('A1:E' . ($this->data->count() + 1))
            ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Header bold
        $sheet->getStyle('A1:E1')->getFont()->setBold(true);

        // Flip sheet for Arabic
        if ($isArabic) {
            $sheet->setRightToLeft(true);
        }
    }
}
