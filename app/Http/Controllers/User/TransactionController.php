<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\GetTransactionsRequest;
use App\Http\Resources\BaseCollection;
use App\Http\Resources\User\TransactionResource;
use App\Repositories\TransactionRepository;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function __construct(private TransactionRepository $transactionRepository) {}

    public function index(GetTransactionsRequest $request)
    {
        $transactions = $this->transactionRepository->getPaginatedTransactionsForUser();

        return success(new BaseCollection($transactions, TransactionResource::class));
    }
}
