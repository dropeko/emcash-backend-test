<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * Regras de negócio implementadas:
     * - O CPF e e-mail são únicos.
     * - A data de admissão deve ser pelo menos 6 meses atrás e não superior a X anos (aqui usamos 40 anos como exemplo).
     * - O funcionário deve estar ativo e vinculado a uma única empresa parceira.
     *
     * @return array
     */
    public function definition()
    {
        $admissionDate = $this->faker->dateTimeBetween('-40 years', '-6 months');
        
        return [
            'id'             => $this->faker->uuid,
            'name'           => $this->faker->name,
            'cpf'            => $this->faker->unique()->numerify('###########'),
            'email'          => $this->faker->unique()->safeEmail,
            'data_admissao' => $admissionDate->format('Y-m-d'),
            'company'        => $this->faker->company,
            'active'         => true,
        ];
    }
}
