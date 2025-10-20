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
        Schema::create('admin_reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->string('reminder_type', 100);
            $table->string('frequency', 32);
            $table->text('task_details');

            $table->boolean('is_active')->default(true);
            $table->string('timezone', 64)->default('America/Costa_Rica');

            $table->dateTimeTz('starts_at')->nullable();
            $table->time('send_at')->nullable();

            $table->dateTimeTz('next_due_at')->nullable();
            $table->dateTimeTz('last_sent_at')->nullable();

            $table->string('status', 32)->nullable();
            $table->json('meta')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'frequency']);
            $table->index(['is_active', 'next_due_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_reminders');
    }
};
