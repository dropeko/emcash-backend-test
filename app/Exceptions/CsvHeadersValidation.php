<?php

declare(strict_types=1);

namespace App\Exceptions;

class CsvHeadersValidation extends \Exception
{
    public function __construct(
        string $message = "Cabeçalhos do CSV inválidos.",
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
