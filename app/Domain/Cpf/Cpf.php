<?php

namespace App\Domain\Cpf;

class Cpf
{
    private const MAX_LENGTH = 11;

    private string $cpf;

    public function __construct(string $cpf)
    {
        $this->cpf = $cpf;
    }

    public function isValid(): bool
    {
        $isValidLength = $this->isValidLength();
        $isValidNumber = $this->isValidNumber();

        return $isValidLength && $isValidNumber;
    }

    private function isValidLength(): bool
    {
        return false;
    }

    private function isValidNumber(): bool
    {
        return false;
    }
}
