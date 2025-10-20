<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\AdminReminder;

class AdminReminderSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first() ?? User::factory()->create();

        AdminReminder::create([
            'user_id'       => $user->id,
            'reminder_type' => 'Reporte semanal',
            'frequency'     => 'weekly',
            'task_details'  => 'Enviar reporte de avances al correo de jefatura',
            'is_active'     => true,
            'timezone'      => 'America/Costa_Rica',
            'starts_at'     => now('America/Costa_Rica'),
            'send_at'       => '09:00:00',
            'meta'          => ['day_of_week' => 2],
        ])->advanceNextDue();
    }
}
