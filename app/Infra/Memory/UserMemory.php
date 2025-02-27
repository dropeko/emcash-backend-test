<?php

declare(strict_types=1);

namespace App\Infra\Memory;

use App\Domain\User\User;
use App\Domain\User\UserPersistenceInterface;

class UserMemory implements UserPersistenceInterface
{
    /**
     * @var User[]
     */
    private array $users = [];

    public function create(User $user): void
    {
        $this->users[$user->getId()] = $user;
    }

    public function isCpfAlreadyCreated(string $cpf): bool
    {
        foreach ($this->users as $user) {
            if ($user->getCpf() === $cpf) {
                return true;
            }
        }

        return false;
    }

    public function isEmailAlreadyCreated(User $user): bool
    {
        foreach ($this->users as $existingUser) {
            if ($existingUser->getEmail() === $user->getEmail()) {
                return true;
            }
        }

        return false;
    }

    public function findAll(): array
    {
        return array_values($this->users);
    }

    public function isExistentId(User $user): bool
    {
        return isset($this->users[$user->getId()]);
    }

    public function editName(User $user): void
    {
        $id = $user->getId();
        if (isset($this->users[$id])) {
            $this->users[$id]->setName($user->getName());
        }
    }

    public function findById(string $id): ?User
    {
        return $this->users[$id] ?? null;
    }

    public function softDelete(User $user): void
    {
        $id = $user->getId();
        if (isset($this->users[$id])) {
            $this->users[$id]->setActive(false);
        }
    }

    public function update(User $user): void
    {
        $id = $user->getId();
        if (isset($this->users[$id])) {
            $this->users[$id] = $user;
        }
    }
}
