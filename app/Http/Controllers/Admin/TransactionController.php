<?php

namespace App\Http\Controllers\Admin;

use App\Exports\Excel\TransactionsExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\GetTransactionsRequest;
use App\Http\Resources\Admin\TransactionResource;
use App\Http\Resources\BaseCollection;
use App\Repositories\TransactionRepository;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Maatwebsite\Excel\Facades\Excel;

class TransactionController extends Controller implements HasMiddleware
{
    public function __construct(private TransactionRepository $transactionRepository) {}

    public static function middleware(): array
    {
        return [
            new Middleware('can:show transactions', only: ['index', 'exportExcel']),
        ];
    }

    public function index(GetTransactionsRequest $request)
    {
        $transactions = $this->transactionRepository->getPaginatedTransactions();

        return success(new BaseCollection($transactions, TransactionResource::class));
    }

    public function exportExcel()
    {
        return Excel::download(new TransactionsExport(), 'transactions.xlsx');
    }
}
