<?php

namespace App\Services\Admin;

use App\Models\Transaction;
use App\Repositories\TransactionRepository;
use Illuminate\Support\Str;

class TransactionService
{
    public function __construct(private TransactionRepository $transactionRepository) {}

    public function getActorType(Transaction $transaction)
    {
        if (is_null($transaction->actor_type)) {
            return 'system';
        }

        return strtolower(class_basename($transaction->actor_type));
    }

    public function getManualAdjustmentType(Transaction $transaction)
    {
        return $transaction->amount > 0 ? 'credit' : 'debit';
    }

    public function generateTransactionNo(): string
    {
        do {
            $code = 'TRX-' . strtoupper(Str::random(8));
        } while ($this->transactionRepository->getByTransactionNo($code));

        return $code;
    }
}
