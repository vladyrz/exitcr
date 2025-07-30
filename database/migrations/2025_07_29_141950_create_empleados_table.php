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
        Schema::create('empleados', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('correo_empresarial')->nullable();
            $table->boolean('estado_contrato');
            $table->string('estado_progreso')->nullable();
            $table->string('puesto_de_trabajo')->nullable();
            $table->string('cedula', 20)->nullable();
            $table->string('telefono', 20)->nullable();
            $table->string('correo_personal')->nullable();
            $table->string('profesion')->nullable();
            $table->string('placa')->nullable();
            $table->string('direccion')->nullable();
            $table->date('fecha_nacimiento')->nullable();
            $table->string('estado_civil')->nullable();
            $table->text('contrato')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('empleados');
    }
};
