<?php

namespace App\Exports\Excel;

use App\Enums\TransactionType;
use App\Repositories\TransactionRepository;
use App\Services\Admin\PaymentMethodService;
use App\Services\Admin\TransactionService;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class TransactionsExport implements
    FromCollection,
    WithMapping,
    WithHeadings,
    WithColumnWidths,
    WithStyles,
    WithColumnFormatting
{
    private TransactionService $transactionService;
    private TransactionRepository $transactionRepository;
    private PaymentMethodService $paymentMethodService;

    public function __construct()
    {
        $this->transactionService = app(TransactionService::class);
        $this->transactionRepository = app(TransactionRepository::class);
        $this->paymentMethodService = app(PaymentMethodService::class);
    }

    private function displayWhen(bool $condition, $value)
    {
        return $condition ? ($value ?? '-') : '-';
    }

    public function collection()
    {
        return $this->transactionRepository
            ->getTransactionsQuery()
            ->get();
    }

    public function map($transaction): array
    {
        return [
            __(ucwords(str_replace('_', ' ', $transaction->type->value))),
            __($this->transactionService->getActorType($transaction)),
            $transaction->actor?->name ?? '-',
            $transaction->amount,
            $transaction->projected_profit,
            $transaction->actual_profit,
            $transaction->reference_id,
            $this->displayWhen(
                in_array($transaction->type, [TransactionType::PURCHASE, TransactionType::REFUND]),
                $transaction->order?->order_no
            ),
            $this->displayWhen(
                $transaction->type == TransactionType::TOPUP,
                $this->paymentMethodService->getPaymentMethodName($transaction->payment_method_id ?? 0)
            ),
            $this->displayWhen(
                $transaction->type == TransactionType::TOPUP && $transaction->payment_method_id == 3,
                $transaction->usdt_network
            ),
            $this->displayWhen(
                $transaction->type == TransactionType::MANUAL_ADJUSTMENT,
                __($this->transactionService->getManualAdjustmentType($transaction))
            ),
            $this->displayWhen(
                $transaction->type == TransactionType::MANUAL_ADJUSTMENT,
                $transaction->description
            ),
            __($transaction->status->value),
            formatDateTime($transaction->created_at),
        ];
    }

    public function headings(): array
    {
        return [
            __('Type'),
            __('Actor Type'),
            __('Actor'),
            __('Amount'),
            __('Projected Profit'),
            __('Actual Profit'),
            __('Reference ID'),
            __('Order No'),
            __('Payment Method'),
            __('USDT Network'),
            __('Manual Adjustment Type'),
            __('Manual Adjustment Reason'),
            __('Status'),
            __('Created At'),
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 20,
            'B' => 20,
            'C' => 25,
            'D' => 15,
            'E' => 18,
            'F' => 18,
            'G' => 30,
            'H' => 20,
            'I' => 20,
            'J' => 15,
            'K' => 20,
            'L' => 35,
            'M' => 15,
            'N' => 20,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $isArabic = app()->getLocale() === 'ar';

        // Bold header
        $sheet->getStyle('A1:N1')->getFont()->setBold(true);

        // Center all content
        $sheet->getStyle('A1:N' . ($this->collection()->count() + 1))
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // RTL flip for Arabic
        if ($isArabic) {
            $sheet->setRightToLeft(true);
        }
    }

    public function columnFormats(): array
    {
        return [
            'D' => NumberFormat::FORMAT_NUMBER_00,
            'E' => NumberFormat::FORMAT_NUMBER_00,
            'F' => NumberFormat::FORMAT_NUMBER_00,
        ];
    }
}
