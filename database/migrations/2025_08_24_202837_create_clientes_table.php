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
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('cedula')->nullable();
            $table->string('email')->nullable();
            $table->string('telefono', 20);
            $table->string('direccion')->nullable();
            $table->string('contacto_preferido')->nullable();
            $table->string('tipo_cliente');
            $table->string('otro_tipo')->nullable();
            $table->text('observaciones')->nullable();
            $table->date('fecha_ingreso');
            $table->json('documentos')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};
