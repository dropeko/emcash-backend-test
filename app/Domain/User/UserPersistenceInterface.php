<?php

namespace App\Domain\User;

interface UserPersistenceInterface
{
    public function create(User $user): void;
    public function isCpfAlreadyCreated(string $cpf): bool;
    public function isEmailAlreadyCreated(User $user): bool;
    public function findAll(): array;
    public function isExistentId(User $user): bool;
    public function editName(User $user): void;
    public function findById(string $id): ?User;
    public function softDelete(User $user): void;
    public function update(User $user): void;
}
