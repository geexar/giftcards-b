<?php

namespace App\Exports\Excel;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class TopItemsExport implements
    FromCollection,
    WithMapping,
    WithHeadings,
    WithColumnWidths,
    WithStyles,
    WithColumnFormatting
{
    private Collection $items;

    public function __construct(Collection $items)
    {
        $this->items = $items;
    }

    public function collection(): Collection
    {
        return $this->items;
    }

    public function map($item): array
    {
        return [
            // A
            $item->product->name
                . ($item->variantValue ? ' - ' . $item->variantValue->value : ''),

            // B
            __($item->product->source->value),

            // C → NEW (Status)
            __($item->product->status->value),

            // D
            (int) $item->quantity_sold,

            // E
            $item->revenue,

            // F
            $item->net_revenue,

            // G
            $item->total_profit,
        ];
    }

    public function headings(): array
    {
        return [
            __('Product Name'),
            __('Source'),
            __('Status'),
            __('Quantity Sold'),
            __('Revenue'),
            __('Net Revenue'),
            __('Profit'),
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 60, // Product Name
            'B' => 20, // Source
            'C' => 18, // Status
            'D' => 15, // Quantity
            'E' => 20, // Revenue
            'F' => 20, // Net Revenue
            'G' => 20, // Profit
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $isArabic = app()->getLocale() === 'ar';

        // Header bold
        $sheet->getStyle('A1:G1')->getFont()->setBold(true);

        // Center all content
        $sheet->getStyle('A1:G' . ($this->items->count() + 1))
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // RTL for Arabic
        if ($isArabic) {
            $sheet->setRightToLeft(true);
        }
    }

    public function columnFormats(): array
    {
        return [
            'D' => NumberFormat::FORMAT_NUMBER,    // Quantity
            'E' => NumberFormat::FORMAT_NUMBER_00, // Revenue
            'F' => NumberFormat::FORMAT_NUMBER_00, // Net Revenue
            'G' => NumberFormat::FORMAT_NUMBER_00, // Profit
        ];
    }
}
