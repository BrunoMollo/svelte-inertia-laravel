<?php

namespace App\Services\Ocr;

readonly class OcrResult
{
    public function __construct(
        public bool $success,
        public ?string $text,
        public ?string $error,
        public int $pages,
        public float $processingTime,
    ) {}

    public static function success(string $text, int $pages, float $processingTime): self
    {
        return new self(
            success: true,
            text: $text,
            error: null,
            pages: $pages,
            processingTime: $processingTime,
        );
    }

    public static function failure(string $error, float $processingTime = 0.0): self
    {
        return new self(
            success: false,
            text: null,
            error: $error,
            pages: 0,
            processingTime: $processingTime,
        );
    }
}
