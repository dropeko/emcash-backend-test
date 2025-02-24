<?php

namespace App\Infra\Db;

use App\Domain\User\User;
use App\Domain\User\UserDataValidator;
use App\Domain\User\UserPersistenceInterface;
use Illuminate\Support\Facades\DB;

class UserDb implements UserPersistenceInterface
{
    private const TABLE_NAME = '';

    private const COLUMN_UUID = '';
    private const COLUMN_NAME = '';
    private const COLUMN_EMAIL = '';
    private const COLUMN_CPF = '';
    private const COLUMN_CREATED_AT = '';
    private const COLUMN_DELETED_AT = '';
    private const COLUMN_UPDATED_AT = '';

    public function create(User $user): void
    {
        DB::table(self::TABLE_NAME)->insert([
            self::COLUMN_UUID => $user->getId(),
            self::COLUMN_NAME => $user->getName(),
            self::COLUMN_EMAIL => $user->getEmail(),
            self::COLUMN_CPF => $user->getCpf(),
            self::COLUMN_CREATED_AT => $user->getDateCreation(),
        ]);
    }

    public function findAll(User $user): array
    {
        $users = [];

        $records = DB::table(self::TABLE_NAME)
            ->select([
                self::COLUMN_UUID,
                self::COLUMN_NAME,
                self::COLUMN_EMAIL,
                self::COLUMN_CPF,
            ])
            ->where([
                self::COLUMN_DELETED_AT => null
            ])
            ->get()
        ;

        foreach ($records as $record) {
            $users[] = (new User(new UserDb()))
                ->setDataValidator(new UserDataValidator())
                ->setId($record->uuid)
                ->setName($record->name)
                ->setCpf($record->cpf)
                ->setEmail($record->email)
            ;
        }

        return $users;
    }
}
