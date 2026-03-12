<?php

namespace App\Http\Controllers\Admin;

use App\Exports\Excel\ProductsTemplateExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BulkStatusUpdateRequest;
use App\Http\Requests\Admin\ImportProductsRequest;
use App\Http\Requests\Admin\ProductRequest;
use App\Http\Resources\Admin\ProductResource;
use App\Http\Resources\BaseCollection;
use App\Imports\ProductsImport;
use App\Repositories\ProductRepository;
use App\Services\Admin\ProductService;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;

class ProductController extends Controller implements HasMiddleware
{
    public function __construct(
        private ProductService $productService,
        private ProductRepository $productRepository
    ) {}

    public static function middleware(): array
    {
        return [
            new Middleware('can:show products', only: ['index', 'importTemplate', 'import']),
            new Middleware('can:view product', only: ['show']),
            new Middleware('can:create product', only: ['store']),
            new Middleware('can:update product', only: ['update', 'bulkStatusUpdate', 'toggleStatus']),
            new Middleware('can:delete product', only: ['destroy']),
        ];
    }

    public function index()
    {
        $products = $this->productRepository->getPaginatedProductsForDashboard();

        return success(new BaseCollection($products, ProductResource::class));
    }

    public function store(ProductRequest $request)
    {
        $this->productService->create($request->validated());

        return success(true);
    }

    public function show(string $id)
    {
        $product = $this->productService->getProduct($id);

        return success(ProductResource::make($product));
    }

    public function update(ProductRequest $request, string $id)
    {
        $this->productService->update($id, $request->validated());

        return success(true);
    }

    public function destroy(string $id)
    {
        $this->productService->delete($id);

        return success(true);
    }

    public function bulkStatusUpdate(BulkStatusUpdateRequest $request)
    {
        $this->productService->bulkStatusUpdate($request->validated());

        return success(true);
    }

    public function importTemplate()
    {
        return Excel::download(new ProductsTemplateExport, 'products_import_template.xlsx');
    }

    public function import(ImportProductsRequest $request)
    {
        DB::beginTransaction();

        try {
            $import = new ProductsImport();
            $import->import($request->file('file'));

            $errors = [];

            foreach ($import->failures() as $failure) {
                $rowName = __('row') . ' ' . $failure->row();
                $errors[$rowName][$failure->attribute()] = $failure->errors()[0];
            }

            if (count($errors)) {
                DB::rollBack();
                throw ValidationException::withMessages($errors);
            }

            DB::commit();

            return success(true);
        } catch (\Exception $e) {
            DB::rollBack();

            throw $e;
        }
    }
}
