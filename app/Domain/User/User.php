<?php

namespace App\Domain\User;

use App\Domain\Uuid\UuidGeneratorInterface;
use App\Exceptions\DuplicatedDataException;
use App\Exceptions\InvalidUserObjectException;
use App\Exceptions\UserNotFoundException;

class User
{
    private string $id;
    private string $name;
    private string $email;
    private string $cpf;
    private string $dateCreation;
    private string $dateEdition;
    private string $data_admissao;
    private ?string $company = null;
    private bool $active = true;

    private UserDataValidatorInterface $dataValidator;
    private UuidGeneratorInterface $uuidGenerator;
    private UserPersistenceInterface $persistence;

    public function __construct(UserPersistenceInterface $persistence)
    {
        $this->persistence = $persistence;
    }

    public function setDataValidator(UserDataValidatorInterface $dataValidator): User
    {
        $this->dataValidator = $dataValidator;
        return $this;
    }

    public function getDataValidator(): UserDataValidatorInterface
    {
        return $this->dataValidator;
    }

    public function setUuidGenerator(UuidGeneratorInterface $uuidGenerator): User
    {
        $this->uuidGenerator = $uuidGenerator;
        return $this;
    }

    public function setId(string $id): User
    {
        $this->getDataValidator()->validateId($id);
        $this->id = $id;
        return $this;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setName(string $name): User
    {
        $this->getDataValidator()->validateName($name);
        $this->name = $name;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setEmail(string $email): User
    {
        $this->getDataValidator()->validateEmail($email);
        $this->email = $email;
        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setCpf(string $cpf): User
    {
        $this->getDataValidator()->validateCpf($cpf);
        $this->cpf = $cpf;
        return $this;
    }

    public function getCpf(): string
    {
        return $this->cpf;
    }

    public function setDateCreation(string $dateCreation): User
    {
        $this->getDataValidator()->validateDateCreation($dateCreation);
        $this->dateCreation = $dateCreation;
        return $this;
    }

    public function getDateCreation(): string
    {
        return $this->dateCreation;
    }

    public function setDateEdition(string $dateEdition): User
    {
        $this->getDataValidator()->validateDateEdition($dateEdition);
        $this->dateEdition = $dateEdition;
        return $this;
    }

    public function getDateEdition(): string
    {
        return $this->dateEdition;
    }

    public function setDataAdmissao(string $dataAdmissao): User
    {
        $this->data_admissao = $dataAdmissao;
        return $this;
    }

    public function getDataAdmissao(): string
    {
        return $this->data_admissao;
    }

    public function setCompany(?string $company): User
    {
        $this->company = $company;
        return $this;
    }

    public function getCompany(): ?string
    {
        return $this->company;
    }

    public function setActive(bool $active): User
    {
        $this->active = $active;
        return $this;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function generateId(): User
    {
        $this->id = $this->uuidGenerator->generate();
        return $this;
    }

    public function checkAlreadyCreatedCpf(): void
    {
        // Implementar a lógica de verificação de CPF duplicado
    }

    public function checkAlreadyCreatedEmail(): void
    {
        // Implementar a lógica de verificação de Email duplicado
    }

    public function findAll(): array
    {
        return $this->persistence->findAll($this);
    }

    /**
     * Cria um objeto User a partir de um registro do banco, sem disparar validações.
     *
     * @param object $record
     * @param \App\Domain\User\UserPersistenceInterface $persistence
     * @return User
     */
    public static function fromRecord(object $record, $persistence): User
    {
        $user = new self($persistence);
        // Atribuição direta dos valores
        $user->id = $record->id;
        $user->name = $record->name;
        $user->cpf = $record->cpf;
        $user->email = $record->email;
        $user->data_admissao = $record->data_admissao ?? '';
        $user->company = $record->company ?? null;
        $user->active = isset($record->active) ? (bool)$record->active : true;
        // Se necessário, também podemos atribuir as datas de criação e edição
        $user->dateCreation = $record->created_at ?? '';
        $user->dateEdition = $record->updated_at ?? '';
        return $user;
    }
}
