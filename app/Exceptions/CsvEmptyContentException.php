<?php

declare(strict_types=1);

namespace App\Exceptions;

class CsvEmptyContentException extends \Exception
{
    public function __construct(
        string $message = "O CSV está vazio.",
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
