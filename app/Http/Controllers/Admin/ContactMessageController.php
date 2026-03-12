<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\ContactMessageResource;
use App\Http\Resources\BaseCollection;
use App\Repositories\ContactMessageRepository;
use App\Services\Admin\ContactMessageService;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class ContactMessageController extends Controller implements HasMiddleware
{
    public function __construct(
        private ContactMessageService $contactMessageService,
        private ContactMessageRepository $contactMessageRepository
    ) {}

    public static function middleware(): array
    {
        return [
            new Middleware('can:show contact messages', only: ['index']),
            new Middleware('can:view contact message', only: ['show']),
        ];
    }

    /**
     * List all contact messages
     */
    public function index()
    {
        $messages = $this->contactMessageRepository->getPaginatedMessages();

        return success(new BaseCollection($messages, ContactMessageResource::class));
    }

    /**
     * Show a specific contact message
     */
    public function show(string $id)
    {
        $message = $this->contactMessageService->getMessage($id);

        return success(ContactMessageResource::make($message));
    }
}
