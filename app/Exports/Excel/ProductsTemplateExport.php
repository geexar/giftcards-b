<?php

namespace App\Exports\Excel;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class ProductsTemplateExport implements FromArray, WithHeadings, WithStyles, WithEvents
{
    public function headings(): array
    {
        return [
            'Name (English)',
            'Name (Arabic)',
            'Short Description (English)',
            'Short Description (Arabic)',
            'Description (English)',
            'Description (Arabic)',
            'Category',
            'Delivery Type', 
            'Base Price',    
            'Has Discount',  
            'Discount Type',
            'Discount Value',
            'Has Custom Markup Fee',
            'Custom Markup Fee Type',
            'Custom Markup Fee Value',
        ];
    }

    public function array(): array
    {
        return [
            [
                'Product X',
                'منتج ْ',
                'Short text',
                'نص قصير',
                'Long description',
                'وصف طويل',
                'Electronics',
                'instant',
                '100',
                'No',
                'percentage',
                '0',
                'No',
                'fixed',
                '0'
            ]
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
            'A1:O100' => [ // Range updated to O
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                /**
                 * UPDATED COLUMN MAPPING:
                 * J: Has Discount
                 * M: Has Markup
                 */
                $boolColumns = ['J', 'M'];

                foreach ($boolColumns as $col) {
                    $this->addDropdown($sheet, $col, '"Yes,No"');
                }

                // H: Delivery Type
                $this->addDropdown($sheet, 'H', '"instant,requires confirmation"');

                // K: Discount Type | N: Markup Type
                $this->addDropdown($sheet, 'K', '"fixed,percentage"');
                $this->addDropdown($sheet, 'N', '"fixed,percentage"');

                $widths = [
                    'A' => 25, 'B' => 25, 
                    'C' => 30, 'D' => 30, 
                    'E' => 40, 'F' => 40, 
                    'G' => 20, 'H' => 25, 
                    'I' => 15, 'J' => 15,
                    'K' => 15, 'L' => 15, 
                    'M' => 24, 'N' => 24,
                    'O' => 24,
                ];

                foreach ($widths as $col => $width) {
                    $sheet->getColumnDimension($col)->setWidth($width);
                }
            },
        ];
    }

    private function addDropdown(Worksheet $sheet, string $column, string $options)
    {
        for ($i = 2; $i <= 100; $i++) {
            $validation = $sheet->getCell("{$column}{$i}")->getDataValidation();
            $validation->setType(DataValidation::TYPE_LIST);
            $validation->setErrorStyle(DataValidation::STYLE_INFORMATION);
            $validation->setAllowBlank(false);
            $validation->setShowInputMessage(true);
            $validation->setShowErrorMessage(true);
            $validation->setShowDropDown(true);
            $validation->setFormula1($options);
        }
    }
}