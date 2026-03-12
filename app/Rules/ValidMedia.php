<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;

class ValidMedia implements ValidationRule
{
    /**
     * @var array<string, string[]>  // key = type, value = list of MIME strings
     */
    protected array $allMimes = [
        'image' => ['image/jpeg', 'image/jpg', 'image/png'],
        'pdf' => ['application/pdf'],
        'video' => ['video/mp4', 'video/x-msvideo', 'video/x-ms-wmv', 'video/x-flv', 'video/x-matroska'],
    ];

    /** @var array<string> */
    protected array $allowedMimes;

    /** @var array<string> */
    protected array $allowedExtensions;

    /** @var int */
    protected int $maxSizeKB;

    public function __construct(?array $types = null, ?int $maxSizeKB = null)
    {
        $types ??= ['image', 'pdf', 'video'];

        $this->allowedMimes = collect($types)
            ->flatMap(fn($type) => $this->allMimes[$type] ?? [])
            ->unique()
            ->values()
            ->toArray();

        // Convert MIME types → extensions
        $this->allowedExtensions = collect($this->allowedMimes)
            ->map(fn($mime) => explode('/', $mime)[1] ?? $mime)
            ->unique()
            ->toArray();

        $this->maxSizeKB = $maxSizeKB ?? 5120;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! ($value instanceof UploadedFile)) {
            $fail(__('not a valid file'));
            return;
        }

        if (!in_array($value->getMimeType(), $this->allowedMimes) || !in_array($value->getClientOriginalExtension(), $this->allowedExtensions)) {
            $fail(__('File type not supported. Only: :types', [
                'types' => implode(', ', $this->allowedExtensions),
            ]));
            return;
        }

        if ($value->getSize() / 1024 > $this->maxSizeKB) {
            $size = $this->maxSizeKB < 1024
                ? $this->maxSizeKB . ' KB'
                : round($this->maxSizeKB / 1024, 2) . ' MB';

            $fail(__('validation.custom.media.max_size', ['attribute' => __("validation.attributes.{$attribute}"), 'size' => $size]));
        }
    }
}
