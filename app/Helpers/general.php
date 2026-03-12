<?php

use Carbon\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;

function success($data)
{
    return response()->json([
        'data' => $data,
    ]);
}

function error(string $message, int $code)
{
    return response()->json([
        'message' => $message,
    ], $code);
}

function formatMoney(float $amount): ?string
{
    return number_format($amount, 2);
}

function formatRating(float $rating)
{
    return (string) round($rating, 1);
}

function formatDateTime(string $timestamp): ?string
{
    return Carbon::parse($timestamp)->format('Y-m-d H:i');
}

function formatDate(string $timestamp): ?string
{
    return Carbon::parse($timestamp)->format('Y-m-d');
}

function normalizePhoneNumber($number)
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

function setting($key)
{
    // return app(SettingRepository::class)->get($key);
}
