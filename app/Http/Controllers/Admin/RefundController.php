<?php

namespace App\Http\Controllers\Admin;

use App\Exports\Excel\RefundsExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateRefundRequest;
use App\Http\Resources\Admin\RefundResource;
use App\Http\Resources\BaseCollection;
use App\Repositories\RefundRepository;
use App\Services\Admin\RefundService;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Maatwebsite\Excel\Facades\Excel;

class RefundController extends Controller implements HasMiddleware
{
    public function __construct(
        private RefundService $refundService,
        private RefundRepository $refundRepository
    ) {}

    public static function middleware(): array
    {
        return [
            new Middleware('can:show refunds', only: ['index', 'exportExcel']),
            new Middleware('can:view refund', only: ['show']),
            new Middleware('can:update refund', only: ['update']),
        ];
    }

    public function index()
    {
        $refunds = $this->refundRepository->getPaginatedRefunds();

        return success(new BaseCollection($refunds, RefundResource::class));
    }

    public function show(string $id)
    {
        $refund = $this->refundService->getRefund($id);

        return success(RefundResource::make($refund));
    }

    public function update(UpdateRefundRequest $request, string $id)
    {
        $this->refundService->update($id, $request->validated());

        return success(true);
    }

    public function exportExcel()
    {
        return Excel::download(new RefundsExport(), 'refunds.xlsx');
    }
}
