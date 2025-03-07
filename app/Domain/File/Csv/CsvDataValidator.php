<?php

namespace App\Domain\File\Csv;

use App\Domain\File\FileDataValidatorInterface;
use App\Exceptions\DataValidationException;

class CsvDataValidator implements FileDataValidatorInterface
{
    private const VALID_MIME_TYPE = 'text/csv';
    private const MAX_SIZE_IN_BYTES = 1000000;
    private const MIN_SIZE_IN_BYTES = 1;

    public function validateMimeType(string $mimeType): void
    {
        if (empty($mimeType)) {
            throw new DataValidationException('The file mimeType cannot be empty');
        }
        if ($mimeType !== self::VALID_MIME_TYPE) {
            throw new DataValidationException('The file type is not valid');
        }
    }

    public function validateContent(string $content): void
    {
        if (empty(trim($content))) {
            throw new DataValidationException('The file content cannot be empty');
        }
    }

    public function validateSizeInBytes(int $sizeInBytes): void
    {
        if ($sizeInBytes < self::MIN_SIZE_IN_BYTES || $sizeInBytes > self::MAX_SIZE_IN_BYTES) {
            throw new DataValidationException('The file size is not valid');
        }
    }
}
