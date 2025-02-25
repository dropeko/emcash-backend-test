<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user', function (Blueprint $table) {
            // Utilizando UUID como chave primária
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('cpf')->unique();
            $table->string('email')->unique();
            $table->date('admission_date');
            // Campo para identificar a empresa parceira (única por funcionário)
            $table->string('company');
            // Status do funcionário: ativo ou inativo
            $table->boolean('active')->default(true);
            // Timestamps e soft delete
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user');
    }
};
