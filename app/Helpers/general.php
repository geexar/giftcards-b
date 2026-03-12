<?php

use App\Repositories\SettingRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;

function success($data)
{
    return response()->json([
        'data' => $data,
    ]);
}

function successMessage(string $message, int $code = 200)
{
    return response()->json([
        'message' => $message,
    ], $code);
}

function error(string $message, int $code)
{
    return response()->json([
        'message' => $message,
    ], $code);
}

function formatMoney(?float $amount): ?string
{
    if (is_null($amount)) {
        return null;
    }

    return number_format($amount, 2, '.', '');
}

function formatPercentage(float $value, float $totalValue): string
{
    if ($totalValue == 0) {
        return '0%';
    }

    return round(($value / $totalValue) * 100, 2) . '%';
}

function formatRating(?float $rating): ?string
{
    if (!$rating) {
        return null;
    }

    return (string) round($rating, 1);
}

function formatDateTime(?string $timestamp): ?string
{
    if (is_null($timestamp)) {
        return null;
    }

    return Carbon::parse($timestamp)->format('Y-m-d H:i');
}

function formatDate(?string $timestamp): ?string
{
    if (is_null($timestamp)) {
        return null;
    }

    return Carbon::parse($timestamp)->format('Y-m-d');
}

function normalizePhoneNumber($number): string
{
    return ltrim($number, '0');
}

function getInvalidatedValue(?string $value): ?string
{
    if (is_null($value)) {
        return null;
    }

    return $value . '_del_' . Str::random();
}

function restoreInvalidatedValue($value)
{
    $delimiter = '_del_';
    $pos = strrpos((string) $value, $delimiter);

    return $pos !== false ? substr((string) $value, 0, $pos) : $value;
}

function formatSecondsToMinutesTime($seconds): string
{
    $minutes = floor($seconds / 60);
    $remainingSeconds = $seconds % 60;

    return sprintf('%02d:%02d', $minutes, $remainingSeconds);
}

function formatSecondsToHoursTime($seconds): string
{
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    $remainingSeconds = $seconds % 60;

    return sprintf('%02d:%02d:%02d', $hours, $minutes, $remainingSeconds);
}

function generateOtp()
{
    return App::environment('production') ? fake()->randomNumber(6, true) : '123456';
}

function getSetting(string $group, string $key)
{
    return app(SettingRepository::class)->getSetting($group, $key)->value;
}
