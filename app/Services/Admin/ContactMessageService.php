<?php

namespace App\Services\Admin;

use App\Repositories\ContactMessageRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ContactMessageService
{
    public function __construct(private ContactMessageRepository $contactMessageRepository) {}

    /**
     * Get a contact message by ID
     */
    public function getMessage(string $id)
    {
        $message = $this->contactMessageRepository->getById($id);

        if (!$message) {
            throw new NotFoundHttpException('Contact message not found');
        }

        return $message;
    }
}
