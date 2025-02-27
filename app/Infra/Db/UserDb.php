<?php

declare(strict_types=1);

namespace App\Infra\Db;

use App\Domain\User\User;
use App\Domain\User\UserDataValidator;
use App\Domain\User\UserPersistenceInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class UserDb implements UserPersistenceInterface
{
    private const TABLE_NAME = 'user';
    private const COLUMN_UUID = 'id';
    private const COLUMN_NAME = 'name';
    private const COLUMN_EMAIL = 'email';
    private const COLUMN_CPF = 'cpf';
    private const COLUMN_DATA_ADMISSAO = 'data_admissao';
    private const COLUMN_COMPANY = 'company';
    private const COLUMN_ACTIVE = 'active';
    private const COLUMN_CREATED_AT = 'created_at';
    private const COLUMN_DELETED_AT = 'deleted_at';
    private const COLUMN_UPDATED_AT = 'updated_at';

    public function create(User $user): void
    {
        try {
            DB::table(self::TABLE_NAME)->insert([
                self::COLUMN_UUID         => $user->getId(),
                self::COLUMN_NAME         => $user->getName(),
                self::COLUMN_EMAIL        => $user->getEmail(),
                self::COLUMN_CPF          => $user->getCpf(),
                self::COLUMN_DATA_ADMISSAO=> $user->getDataAdmissao(),
                self::COLUMN_COMPANY      => $user->getCompany() ?? '',
                self::COLUMN_ACTIVE       => $user->isActive(),
                self::COLUMN_CREATED_AT   => $user->getDateCreation(),
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('Erro ao criar usuário no banco de dados: ' . $e->getMessage());
            throw new \RuntimeException('Erro ao criar usuário: ' . $e->getMessage());
        } catch (\Exception $e) {
            Log::error('Erro ao criar usuário: ' . $e->getMessage());
            throw new \RuntimeException('Erro ao criar usuário no banco de dados.');
        }
    }

    /**
     * Retorna todos os usuários ativos (não deletados).
     *
     * @return User[]
     */
    public function findAll(): array
    {
        $users = [];
        $records = DB::table(self::TABLE_NAME)
            ->select([
                self::COLUMN_UUID,
                self::COLUMN_NAME,
                self::COLUMN_EMAIL,
                self::COLUMN_CPF,
            ])
            ->whereNull(self::COLUMN_DELETED_AT)
            ->get();

        foreach ($records as $record) {
            $users[] = (new User(new self()))
                ->setDataValidator(new UserDataValidator())
                ->setId($record->id)
                ->setName($record->name)
                ->setCpf($record->cpf)
                ->setEmail($record->email);
        }

        return $users;
    }

    /**
     * Busca um usuário pelo ID.
     *
     * @param string $id
     *
     * @return User|null
     */
    public function findById(string $id): ?User
    {
        $record = DB::table(self::TABLE_NAME)
            ->where(self::COLUMN_UUID, $id)
            ->whereNull(self::COLUMN_DELETED_AT)
            ->first();

        if (!$record) {
            return null;
        }

        return User::fromRecord($record, new self());
    }

    /**
     * Verifica se o CPF já foi criado.
     *
     * @param string $cpf
     *
     * @return bool
     */
    public function isCpfAlreadyCreated(string $cpf): bool
    {
        $record = DB::table(self::TABLE_NAME)
            ->where(self::COLUMN_CPF, $cpf)
            ->whereNull(self::COLUMN_DELETED_AT)
            ->first();

        return $record !== null;
    }

    /**
     * Verifica se o Email já foi criado.
     *
     * @param User $user
     *
     * @return bool
     */
    public function isEmailAlreadyCreated(User $user): bool
    {
        $email = $user->getEmail();
        $record = DB::table(self::TABLE_NAME)
            ->where(self::COLUMN_EMAIL, $email)
            ->whereNull(self::COLUMN_DELETED_AT)
            ->first();

        return $record !== null;
    }

    /**
     * Verifica se o ID do usuário existe.
     *
     * @param User $user
     *
     * @return bool
     */
    public function isExistentId(User $user): bool
    {
        $id = $user->getId();
        $record = DB::table(self::TABLE_NAME)
            ->where(self::COLUMN_UUID, $id)
            ->whereNull(self::COLUMN_DELETED_AT)
            ->first();

        return $record !== null;
    }

    /**
     * Edita o nome do usuário.
     *
     * @param User $user
     *
     * @return void
     */
    public function editName(User $user): void
    {
        DB::table(self::TABLE_NAME)
            ->where(self::COLUMN_UUID, $user->getId())
            ->update([
                self::COLUMN_NAME       => $user->getName(),
                self::COLUMN_UPDATED_AT => now(),
            ]);
    }

    /**
     * Realiza o soft delete de um usuário.
     *
     * @param User $user
     *
     * @return void
     */
    public function softDelete(User $user): void
    {
        DB::table(self::TABLE_NAME)
            ->where(self::COLUMN_UUID, $user->getId())
            ->update([
                self::COLUMN_DELETED_AT => Carbon::now(),
            ]);
    }

    /**
     * Atualiza as informações de um usuário.
     *
     * @param User $user
     *
     * @return void
     */
    public function update(User $user): void
    {
        DB::table(self::TABLE_NAME)
            ->where(self::COLUMN_UUID, $user->getId())
            ->update([
                self::COLUMN_NAME       => $user->getName(),
                self::COLUMN_CPF        => $user->getCpf(),
                self::COLUMN_EMAIL      => $user->getEmail(),
                self::COLUMN_UPDATED_AT => Carbon::now(),
            ]);
    }
}
