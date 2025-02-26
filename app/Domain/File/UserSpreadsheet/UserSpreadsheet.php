<?php

namespace App\Domain\File\UserSpreadsheet;

use App\Domain\User\User;
use App\Infra\Db\UserDb;
use App\Infra\Uuid\UuidGenerator;
use App\Exceptions\UserSpreadsheetException;
use App\Exceptions\DuplicatedDataException;
use App\Exceptions\DataValidationException;
use App\Exceptions\InvalidUserObjectException;
use DateTime as DT;

class UserSpreadsheet
{
    protected $expectedHeaders = ['name', 'cpf', 'email', 'data_admissao', 'company'];
    protected $minAdmissionMonths = 6;

    public function import(string $csvContent, UserDb $userDb): int
    {
        if (empty(trim($csvContent))) {
            throw new UserSpreadsheetException("O CSV está vazio.");
        }
    
        $lines = array_filter(explode("\n", $csvContent), function ($line) {
            return trim($line) !== '';
        });
    
        if (count($lines) < 2) {
            throw new UserSpreadsheetException("O CSV não possui dados suficientes.");
        }
    
        $headerLine = array_shift($lines);
        $headers = str_getcsv($headerLine);
        if ($headers !== $this->expectedHeaders) {
            throw new UserSpreadsheetException(
                "Cabeçalhos do CSV inválidos. Esperado: " . implode(',', $this->expectedHeaders)
            );
        }
    
        $createdCount = 0;
        $importedCpfs = [];
        $uuidGenerator = new \App\Infra\Uuid\UuidGenerator();
    
        foreach ($lines as $lineNumber => $line) {
            $data = str_getcsv($line);

            if (count($data) < 4) {
                throw new DataValidationException("Linha " . ($lineNumber + 2) . " incompleta.");
            }
            list($name, $cpf, $email, $dataAdmissao, $company) = $data;
    
            if (empty($name) || empty($cpf) || empty($email) || empty($dataAdmissao)) {
                throw new DataValidationException("Linha " . ($lineNumber + 2) . " possui campo(s) vazio(s).");
            }
    
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new DataValidationException("Linha " . ($lineNumber + 2) . ": e-mail inválido.");
            }
    
            $date = \DateTime::createFromFormat('Y-m-d', $dataAdmissao);
            if (!$date || $date->format('Y-m-d') !== $dataAdmissao) {
                throw new DataValidationException("Linha " . ($lineNumber + 2) . ": data_admissao inválida.");
            }
    
            $now = new \DateTime();
            $interval = $date->diff($now);
            $months = ($interval->y * 12) + $interval->m;
            if ($months < $this->minAdmissionMonths) {
                throw new DataValidationException("Linha " . ($lineNumber + 2) . ": funcionário não possui os 6 meses de admissão.");
            }
    
            if (in_array($cpf, $importedCpfs)) {
                throw new DuplicatedDataException("Linha " . ($lineNumber + 2) . ": CPF duplicado no arquivo.");
            }

            if ($userDb->isCpfAlreadyCreated($cpf)) {
                throw new DuplicatedDataException("Linha " . ($lineNumber + 2) . ": CPF já existe no sistema.");
            }
    
            $user = new \App\Domain\User\User($userDb);
            $user->setId($uuidGenerator->generate());
            $user->setName($name);
            $user->setCpf($cpf);
            $user->setEmail($email);
            $user->setDataAdmissao($dataAdmissao);
            $user->setCompany(null);
            $user->setActive(true);
            $user->setDateCreation((new \DateTime())->format('Y-m-d H:i:s'));
    
            $userDb->create($user);
    
            $importedCpfs[] = $cpf;
            $createdCount++;
        }
    
        return $createdCount;
    }

    public function export(UserDb $userDb): string
    {
        $users = $userDb->findAll();

        if (empty($users)) {
            throw new UserSpreadsheetException("Nenhum usuário encontrado para exportação.");
        }

        $csvLines = [];
        $csvLines[] = implode(',', $this->expectedHeaders);

        foreach ($users as $user) {
            $line = [
                $user->getName(),
                $user->getCpf(),
                $user->getEmail(),
                $user->getDataAdmissao()
            ];
            $csvLines[] = implode(',', $line);
        }

        return implode("\n", $csvLines);
    }
}
