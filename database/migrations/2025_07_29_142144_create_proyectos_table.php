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
        Schema::create('proyectos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('progreso');
            $table->string('estado')->default('pendiente');
            $table->string('prioridad');
            $table->text('beneficio_esperado')->nullable();
            $table->date('fecha_solicitud')->nullable();
            $table->date('ultima_actualizacion')->nullable();
            $table->text('observaciones')->nullable();
            $table->json('documentos')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proyectos');
    }
};
