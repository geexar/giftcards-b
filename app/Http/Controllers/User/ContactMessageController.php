<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\ContactMessageRequest;
use App\Services\User\ContactMessageService;
use Illuminate\Http\Request;

class ContactMessageController extends Controller
{
    public function __construct(private ContactMessageService $feedbackService) {}

    public function __invoke(ContactMessageRequest $request)
    {
        $this->feedbackService->create($request->validated());

        return success(__("Your message has been sent successfully"));
    }
}
