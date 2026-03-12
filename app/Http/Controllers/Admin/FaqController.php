<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FaqRequest;
use App\Http\Resources\Admin\FaqResource;
use App\Http\Resources\BaseCollection;
use App\Repositories\FaqRepository;
use App\Services\Admin\FaqService;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class FaqController extends Controller implements HasMiddleware
{
    public function __construct(
        private FaqService $faqService,
        private FaqRepository $faqRepository
    ) {}

    public static function middleware(): array
    {
        return [
            new Middleware('can:show faqs', only: ['index']),
            new Middleware('can:create faq', only: ['store']),
            new Middleware('can:update faq', only: ['show', 'update', 'toggleStatus']),
            new Middleware('can:delete faq', only: ['destroy']),
        ];
    }

    /**
     * Display a paginated list of FAQs
     */
    public function index()
    {
        $faqs = $this->faqRepository->getPaginatedFaqs();

        return success(new BaseCollection($faqs, FaqResource::class));
    }

    /**
     * Store a newly created FAQ
     */
    public function store(FaqRequest $request)
    {
        $this->faqService->create($request->validated());

        return success(true);
    }

    /**
     * Display a specific FAQ
     */
    public function show(string $id)
    {
        $faq = $this->faqService->getFaq($id);

        return success(FaqResource::make($faq));
    }

    /**
     * Update the specified FAQ
     */
    public function update(FaqRequest $request, string $id)
    {
        $this->faqService->update($id, $request->validated());

        return success(true);
    }

    /**
     * Remove the specified FAQ
     */
    public function destroy(string $id)
    {
        $this->faqService->delete($id);

        return success(true);
    }

    public function toggleStatus(string $id)
    {
        $this->faqService->toggleStatus($id);

        return success(true);
    }
}
