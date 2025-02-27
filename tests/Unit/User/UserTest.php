<?php

declare(strict_types=1);

namespace Tests\Unit\User;

use App\Domain\User\User;
use App\Domain\User\UserDataValidator;
use App\Exceptions\DataValidationException;
use App\Infra\Memory\UserMemory;
use Tests\TestCase;
use Faker\Factory as FakerFactory;

class UserTest extends TestCase
{
    private const VALID_CPF = '48472338088';
    private const INVALID_CPF_WITH_SPECIAL_CHARACTERS = '484.723.380-88';
    private const INVALID_CPF = '48992300088';

    private $faker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->faker = FakerFactory::create();
    }

    public function testShouldCorrectlyCreateUser(): void
    {
        $user = (new User(new UserMemory()))
            ->setDataValidator(new UserDataValidator())
            ->setId($this->faker->uuid())
            ->setName($this->faker->name())
            ->setEmail($this->faker->email())
            ->setCpf(self::VALID_CPF);

        $this->assertNotEmpty($user->getName());
    }

    public function testShouldThrowAnExceptionWhenTryToSetInvalidId(): void
    {
        $user = (new User(new UserMemory()))
            ->setDataValidator(new UserDataValidator());

        $this->expectException(DataValidationException::class);
        $this->expectExceptionMessage("ID must be exactly 36 characters long");

        $user->setId('invalid-id');
    }

    public function testShouldThrowAnExceptionWhenTryToSetTooLongId(): void
    {
        $user = (new User(new UserMemory()))
            ->setDataValidator(new UserDataValidator());

        $this->expectException(DataValidationException::class);
        $this->expectExceptionMessage("ID must be exactly 36 characters long");

        $user->setId('this-id-is-not-compatible-with-the-method-validation');
    }

    public function testShouldThrowAnExceptionWhenTryToSetEmptyId(): void
    {
        $user = (new User(new UserMemory()))
            ->setDataValidator(new UserDataValidator());

        $this->expectException(DataValidationException::class);
        $this->expectExceptionMessage("ID cannot be empty");

        $user->setId('');
    }

    public function testShouldThrowAnExceptionWhenTryToSetTooLongName(): void
    {
        $user = (new User(new UserMemory()))
            ->setDataValidator(new UserDataValidator());

        $this->expectException(DataValidationException::class);
        $this->expectExceptionMessage("Name must be at most 100 characters");

        $user->setName(str_repeat('a', 101));
    }

    public function testShouldThrowAnExceptionWhenTryToSetEmptyName(): void
    {
        $user = (new User(new UserMemory()))
            ->setDataValidator(new UserDataValidator());

        $this->expectException(DataValidationException::class);
        $this->expectExceptionMessage("Name cannot be empty");

        $user->setName('');
    }

    public function testShouldThrowAnExceptionWhenTryToSetInvalidEmail(): void
    {
        $user = (new User(new UserMemory()))
            ->setDataValidator(new UserDataValidator());

        $this->expectException(DataValidationException::class);
        $this->expectExceptionMessage("Email is not valid");

        $user->setEmail('invalid-email');
    }

    public function testShouldThrowAnExceptionWhenTryToSetTooLongEmail(): void
    {
        $user = (new User(new UserMemory()))
            ->setDataValidator(new UserDataValidator());

        $this->expectException(DataValidationException::class);
        $this->expectExceptionMessage("Email must be at most 100 characters");

        $user->setEmail(str_repeat('a', 101) . '@example.com');
    }

    public function testShouldThrowAnExceptionWhenTryToSetEmptyEmail(): void
    {
        $user = (new User(new UserMemory()))
            ->setDataValidator(new UserDataValidator());

        $this->expectException(DataValidationException::class);
        $this->expectExceptionMessage("Email cannot be empty");

        $user->setEmail('');
    }

    public function testShouldThrowAnExceptionWhenTryToSetCpfWithSpecialCharacters(): void
    {
        $user = (new User(new UserMemory()))
            ->setDataValidator(new UserDataValidator());

        $this->expectException(DataValidationException::class);
        $this->expectExceptionMessage("The user cpf is not valid");

        $user->setCpf(self::INVALID_CPF_WITH_SPECIAL_CHARACTERS);
    }

    public function testShouldThrowAnExceptionWhenTryToSetEmptyCpf(): void
    {
        $user = (new User(new UserMemory()))
            ->setDataValidator(new UserDataValidator());

        $this->expectException(DataValidationException::class);
        $this->expectExceptionMessage("The user cpf is not valid");

        $user->setCpf('');
    }

    public function testShouldThrowAnExceptionWhenTryToSetNonNumericCpf(): void
    {
        $user = (new User(new UserMemory()))
            ->setDataValidator(new UserDataValidator());

        $this->expectException(DataValidationException::class);
        $this->expectExceptionMessage("The user cpf is not valid");

        $user->setCpf('non-numeric-cpf');
    }

    public function testShouldThrowAnExceptionWhenTryToSetEmptyDateCreation(): void
    {
        $user = (new User(new UserMemory()))
            ->setDataValidator(new UserDataValidator());

        $this->expectException(DataValidationException::class);
        $this->expectExceptionMessage("Date creation cannot be empty");

        $user->setDateCreation('');
    }

    public function testShouldCorrectlySetDateCreation(): void
    {
        $user = (new User(new UserMemory()))
            ->setDataValidator(new UserDataValidator());

        $user->setDateCreation('2023-12-29 15:29:00');

        $this->assertNotEmpty($user->getDateCreation());
    }

    public function testShouldThrowAnExceptionWhenTryToSetDateCreationInInvalidFormat(): void
    {
        $user = (new User(new UserMemory()))
            ->setDataValidator(new UserDataValidator());

        $this->expectException(DataValidationException::class);
        $this->expectExceptionMessage("Date creation is not a valid datetime in format Y-m-d H:i:s");

        $user->setDateCreation('invalid-date-format');
    }

    public function testShouldCorrectlySetDateEdition(): void
    {
        $user = (new User(new UserMemory()))
            ->setDataValidator(new UserDataValidator());

        $user->setDateEdition('2023-12-30 16:30:00');

        $this->assertNotEmpty($user->getDateEdition());
    }

    public function testShouldThrowAnExceptionWhenTryToSetEmptyDateEdition(): void
    {
        $user = (new User(new UserMemory()))
            ->setDataValidator(new UserDataValidator());

        $this->expectException(DataValidationException::class);
        $this->expectExceptionMessage("Date edition cannot be empty");

        $user->setDateEdition('');
    }

    public function testShouldThrowAnExceptionWhenTryToSetDateEditionInInvalidFormat(): void
    {
        $user = (new User(new UserMemory()))
            ->setDataValidator(new UserDataValidator());

        $this->expectException(DataValidationException::class);
        $this->expectExceptionMessage("Date edition is not a valid datetime in format Y-m-d H:i:s");

        $user->setDateEdition('invalid-date-format');
    }
}
