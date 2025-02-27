<?php

declare(strict_types=1);

namespace App\Exceptions;

class DuplicatedDataException extends \Exception
{
    /**
     * @param string $field Nome do campo que está duplicado.
     * @param string $value Valor duplicado.
     * @param int $code
     * @param \Throwable|null $previous
     */
    public function __construct(
        string $field = '',
        string $value = '',
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        $message = $field !== '' && $value !== ''
            ? "Dados duplicados encontrados no campo '{$field}' com o valor '{$value}' já existe."
            : "Dados duplicados encontrados.";
            
        parent::__construct($message, $code, $previous);
    }
}
