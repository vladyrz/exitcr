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
        Schema::create('vacacions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('tipo_solicitud');
            $table->text('observaciones')->nullable();
            $table->string('estado_solicitud')->default('pendiente');
            $table->string('saldo_vacaciones')->nullable();

            $table->date('fecha_permiso')->nullable();
            $table->string('opciones_permiso')->nullable();
            $table->time('hora_inicio')->nullable();
            $table->time('hora_fin')->nullable();

            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_fin')->nullable();
            $table->integer('total_vacaciones')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vacacions');
    }
};
