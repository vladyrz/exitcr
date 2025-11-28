<?php

namespace App\Models;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;

class AdminReminder extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'reminder_type',
        'frequency',
        'task_details',
        'is_active',
        'timezone',
        'starts_at',
        'send_at',
        'next_due_at',
        'last_sent_at',
        'status',
        'meta',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'starts_at' => 'datetime',
        'next_due_at' => 'datetime',
        'last_sent_at' => 'datetime',
        'meta' => AsArrayObject::class,
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isDue(?CarbonImmutable $now = null): bool
    {
        if (!$this->is_active || !$this->next_due_at)
            return false;

        $tz = $this->timezone ?: 'UTC';
        $now = $now?->setTimezone($tz) ?? now($tz)->toImmutable();

        if ($this->last_sent_at) {
            $last = CarbonImmutable::parse($this->last_sent_at)->tz($tz);
            if ($last->equalTo($this->next_due_at)) {
                return false;
            }
        }

        return CarbonImmutable::parse($this->next_due_at)->tz($tz)->lessThanOrEqualTo($now);
    }

    public function computeNextDueAt(?CarbonImmutable $ref = null): ?CarbonImmutable
    {
        $tz = $this->timezone ?: 'UTC';
        $ref = $ref?->setTimezone($tz) ?? now($tz)->toImmutable();

        $base = $this->starts_at ? CarbonImmutable::parse($this->starts_at)->tz($tz) : $ref;

        if ($this->send_at) {
            [$h, $m, $s] = array_map('intval', explode(':', $this->send_at . ':00:00'));
            $base = $base->setTime($h, $m, 0);
        }

        $meta = (array) ($this->meta ?? []);

        $next = match ($this->frequency) {
            'daily' => $this->rollUntilFuture($base, $ref, 'day'),
            'weekly' => $this->computeWeekly($base, $ref, $meta),
            'monthly' => $this->computeMonthly($base, $ref, $meta),
            'quarterly' => $this->computeQuarterly($base, $ref, $meta),
            'yearly' => $this->computeYearly($base, $ref, $meta),
            default => null,
        };

        return $next?->tz($tz);
    }

    protected function rollUntilFuture(CarbonImmutable $base, CarbonImmutable $ref, string $unit): CarbonImmutable
    {
        $n = $base;
        while ($n->lessThanOrEqualTo($ref)) {
            $n = $n->add(1, $unit);
        }

        return $n;
    }

    protected function computeWeekly(CarbonImmutable $base, CarbonImmutable $ref, array $meta): CarbonImmutable
    {
        // meta['day_of_week'] 0=Dom,1=Lun,...6=Sáb. Default: día de base.
        $dow = (int) ($meta['day_of_week'] ?? $base->dayOfWeek);

        // Siguiente o el mismo día de la semana (0..6) de forma compatible
        $currentDow = (int) $base->dayOfWeek;            // 0..6
        $delta = ($dow - $currentDow + 7) % 7;      // 0..6
        $n = $base->addDays($delta);            // "nextOrSame"

        // Si ya pasó respecto a la referencia, salta semanas hasta que sea futuro
        while ($n->lessThanOrEqualTo($ref)) {
            $n = $n->addWeek();
        }

        return $n;
    }


    protected function computeMonthly(CarbonImmutable $base, CarbonImmutable $ref, array $meta): CarbonImmutable
    {
        $dom = $meta['day_of_month'] ?? $base->day;
        $n = $base->setDay(min($dom, $base->daysInMonth));
        while ($n->lessThanOrEqualTo($ref)) {
            $n = $base->addMonth()->setDay(min($dom, $base->addMonth()->daysInMonth));
            $base = $base->addMonth(); // Important: update base to keep advancing months correctly
        }

        return $n;
    }

    protected function computeQuarterly(CarbonImmutable $base, CarbonImmutable $ref, array $meta): CarbonImmutable
    {
        $dom = $meta['day_of_month'] ?? $base->day;
        $n = $base->setDay(min($dom, $base->daysInMonth));
        while ($n->lessThanOrEqualTo($ref)) {
            $base = $base->addMonths(3);
            $n = $base->setDay(min($dom, $base->daysInMonth));
        }

        return $n;
    }

    protected function computeYearly(CarbonImmutable $base, CarbonImmutable $ref, array $meta): CarbonImmutable
    {
        $month = $meta['month'] ?? $base->month;
        $dom = $meta['day_of_month'] ?? $base->day;
        $n = $base->setMonth($month)->setDay(min($dom, $base->setMonth($month)->daysInMonth));
        while ($n->lessThanOrEqualTo($ref)) {
            $b = $base->addYear()->setMonth($month);
            $n = $b->setDay(min($dom, $b->daysInMonth));
            $base = $base->addYear(); // Update base
        }

        return $n;
    }

    public function advanceNextDue(): void
    {
        $next = $this->computeNextDueAt();
        $this->forceFill(['next_due_at' => $next])->save();
    }
}
