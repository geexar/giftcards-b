<?php

namespace App\Services\User;

use App\Notifications\ContactMessageNotification;
use App\Repositories\AdminRepository;
use App\Repositories\ContactMessageRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ContactMessageService
{
    private const LIMIT = 5;
    private const WINDOW = 86400; // 24 hours

    public function __construct(
        private ContactMessageRepository $contactMessageRepository,
        private AdminRepository $adminRepository
    ) {}

    public function create(array $data): void
    {
        $user = auth('user')->user();
        $key = $this->rateLimitKey($user);

        // Check rate limit
        if (RateLimiter::tooManyAttempts($key, self::LIMIT)) {
            throw new BadRequestHttpException(__("you have reached the max limit to send contact messages"));
        }

        // Count this attempt
        RateLimiter::hit($key, self::WINDOW);

        // Attach user data automatically
        if ($user) {
            $data['user_id'] = $user->id;
            $data['name'] = $user->name;
            $data['email'] = $user->email;
            $data['country_code'] = $user->country_code ? $user->country_code : ($data['country_code'] ?? null);
            $data['phone'] = $user->phone ? $user->phone : ($data['phone'] ?? null);
        }

        DB::transaction(function () use ($data, $user) {
            $contactMessage = $this->contactMessageRepository->create($data);

            // Notify admins
            $notifiedAdmins = $this->adminRepository->getNotifiedAdmins('view contact message');

            $notification = new ContactMessageNotification($contactMessage);
            Notification::send($notifiedAdmins, $notification);
        });
    }

    private function rateLimitKey($user): string
    {
        return $user
            ? "contact_message:user:" . $user->id
            : "contact_message:guest:" . request()->ip();
    }
}
