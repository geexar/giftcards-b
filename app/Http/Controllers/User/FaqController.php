<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\User\FaqResource;
use App\Repositories\FaqRepository;

class FaqController extends Controller
{
    public function __construct(private FaqRepository $faqRepository) {}

    public function __invoke()
    {
        $faqs = $this->faqRepository->getActiveFaqs();

        return success(FaqResource::collection($faqs));
    }
}
