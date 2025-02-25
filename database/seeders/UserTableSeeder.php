<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Aqui, o seeder utiliza o factory para criar um registro de usuário
     * com dados que obedecem as regras de negócio.
     *
     * @return void
     */
    public function run()
    {
        User::factory(3)->create();
    }
}
