<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Equivale a la tabla `alumnos` de la versión PHP nativa
 * (id, nombre, apellido, identidad, correo) más dos datos nuevos:
 * edad y programa. El correo ahora es UNIQUE también en la base.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('estudiantes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->string('apellido', 100);
            $table->string('identidad', 20);
            $table->string('correo', 100)->unique();
            $table->unsignedTinyInteger('edad');
            $table->string('programa', 100);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('estudiantes');
    }
};
