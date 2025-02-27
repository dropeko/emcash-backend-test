<?php

declare(strict_types=1);

namespace Tests\Unit\File;

use App\Domain\File\UserSpreadsheet\UserSpreadsheet;
use App\Infra\Memory\UserMemory;
use App\Infra\Uuid\UuidGenerator;
use App\Exceptions\CsvEmptyContentException;
use App\Exceptions\CsvHeadersValidation;
use App\Exceptions\UserSpreadsheetException;
use App\Exceptions\DataValidationException;
use App\Exceptions\DuplicatedDataException;
use Tests\TestCase;

class UserSpreadsheetTest extends TestCase
{
    public function testShouldThrowExceptionWhenCsvHeadersAreDifferent(): void
    {
        $userSpreadsheet = new UserSpreadsheet();
        
        $csvContent = "name2,cpf,email,data_admissao\n" .
            "Paolo Maldini,29983872099,some@email.com,2020-01-01\n" .
            "Andrea Pirlo,56663819038,some2@email.com,2020-01-01";

        $userDb = new UserMemory();

        $this->expectException(CsvHeadersValidation::class);
        $this->expectExceptionMessage("Cabeçalhos do CSV inválidos");

        $userSpreadsheet->import($csvContent, $userDb);
    }

    public function testShouldThrowExceptionWhenCsvHeadersAmountIsDifferent(): void
    {
        $userSpreadsheet = new UserSpreadsheet();

        $csvContent = "name,cpf,email\n" .
            "Paolo Maldini,29983872099,2020-01-01\n" .
            "Andrea Pirlo,56663819038,2020-01-01";

        $userDb = new UserMemory();

        $this->expectException(CsvHeadersValidation::class);
        $this->expectExceptionMessage("Cabeçalhos do CSV inválidos");

        $userSpreadsheet->import($csvContent, $userDb);
    }

    public function testShouldCorrectlyBuildUsersFromContent(): void
    {
        $userSpreadsheet = new UserSpreadsheet();

        $csvContent = "name,cpf,email,data_admissao\n" .
            "Paolo Maldini,29983872099,some@email.com,2020-01-01\n" .
            "Andrea Pirlo,56663819038,some2@email.com,2020-01-01";

        $userDb = new UserMemory();

        $createdCount = $userSpreadsheet->import($csvContent, $userDb);

        $this->assertSame(2, $createdCount);
    }
}
