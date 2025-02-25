<?php

namespace App\Domain\User;

use App\Domain\Cpf\Cpf;
use App\Exceptions\DataValidationException;

class UserDataValidator implements UserDataValidatorInterface
{
    private const ID_MAX_LEGTH = 36;
    private const NAME_MAX_LEGTH = 100;
    private const EMAIL_MAX_LEGTH = 100;
    private const UUID_REGEX = '/^[0-9a-fA-F]{8}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{12}$/';

    public function validateId(string $id): void
    {
        if (empty(trim($id))) {
            throw new DataValidationException("ID cannot be empty");
        }
        if (strlen($id) !== self::ID_MAX_LEGTH) {
            throw new DataValidationException("ID must be exactly " . self::ID_MAX_LEGTH . " characters long");
        }
        if (!preg_match(self::UUID_REGEX, $id)) {
            throw new DataValidationException("ID is not a valid UUID");
        }
    }

    public function validateName(string $name): void
    {
        if (empty(trim($name))) {
            throw new DataValidationException("Name cannot be empty");
        }
        if (strlen($name) > self::NAME_MAX_LEGTH) {
            throw new DataValidationException("Name must be at most " . self::NAME_MAX_LEGTH . " characters");
        }
    }

    public function validateEmail(string $email): void
    {
        if (empty(trim($email))) {
            throw new DataValidationException("Email cannot be empty");
        }
        if (strlen($email) > self::EMAIL_MAX_LEGTH) {
            throw new DataValidationException("Email must be at most " . self::EMAIL_MAX_LEGTH . " characters");
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new DataValidationException("Email is not valid");
        }
    }

    public function validateCpf(string $cpf): void
    {
        if (strlen($cpf) !== 11) {
            throw new DataValidationException('The user cpf is not valid');
        }
    }

    public function validateDateCreation(string $dateCreation): void
    {
        if (empty(trim($dateCreation))) {
            throw new DataValidationException("Date creation cannot be empty");
        }
        $dt = \DateTime::createFromFormat('Y-m-d H:i:s', $dateCreation);
        if (!$dt || $dt->format('Y-m-d H:i:s') !== $dateCreation) {
            throw new DataValidationException("Date creation is not a valid datetime in format Y-m-d H:i:s");
        }
    }

    public function validateDateEdition(string $dateEdition): void
    {
        if (empty(trim($dateEdition))) {
            throw new DataValidationException("Date edition cannot be empty");
        }
        $dt = \DateTime::createFromFormat('Y-m-d H:i:s', $dateEdition);
        if (!$dt || $dt->format('Y-m-d H:i:s') !== $dateEdition) {
            throw new DataValidationException("Date edition is not a valid datetime in format Y-m-d H:i:s");
        }
    }
}
