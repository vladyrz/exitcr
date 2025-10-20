<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AdminReminder;
use App\Notifications\AdminReminderDue;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Notification;

class SendAdminReminders extends Command
{
    protected $signature = 'reminders:send';
    protected $description = 'Envía recordatorios admnistrativos vencidos según su frecuencia.';

    public function handle(): int
    {
        $nowUtc = now()->toImmutable();

        $candidates = AdminReminder::query()
            ->where('is_active', true)
            ->whereNotNull('next_due_at')
            ->where('next_due_at', '<=', $nowUtc)
            ->with('user')
            ->get();

        $sent = 0;

        foreach ($candidates as $reminder) {
            if (!$reminder->user) continue;

            if (!$reminder->isDue(CarbonImmutable::now($reminder->timezone))) {
                continue;
            }

            Notification::send($reminder->user, new AdminReminderDue($reminder));

            $reminder->forceFill([
                'last_sent_at' => now($reminder->timezone),
            ])->save();

            $reminder->advanceNextDue();

            $sent++;
        }

        $this->info("Reminders enviados: {$sent}");

        return Command::SUCCESS;
    }
}
