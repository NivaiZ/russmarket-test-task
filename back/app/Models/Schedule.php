<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

#[Fillable(['name', 'address', 'slug', 'status', 'progress', 'employees_count', 'on_date'])]
class Schedule extends Model
{
    protected static function booted(): void
    {
        static::creating(function (Schedule $schedule): void {
            if (! is_string($schedule->slug) || $schedule->slug === '') {
                $schedule->slug = static::uniqueSlug($schedule->name, static::dateString($schedule->on_date));
            }
        });
    }

    public static function uniqueSlug(string $name, string $date, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);
        if ($base === '') {
            $base = 'grafik';
        }

        $candidates = [$base];
        $stamp = '';
        if (preg_match('/^\d{4}-(\d{2})-(\d{2})$/', $date, $matches) === 1) {
            $stamp = $matches[2].'-'.$matches[1];
            $candidates[] = $base.'-'.$stamp;
        }

        foreach ($candidates as $candidate) {
            if (! static::slugTaken($candidate, $ignoreId)) {
                return $candidate;
            }
        }

        $root = $stamp !== '' ? $base.'-'.$stamp : $base;
        $index = 2;
        while (static::slugTaken($root.'-'.$index, $ignoreId)) {
            $index++;
        }

        return $root.'-'.$index;
    }

    private static function slugTaken(string $slug, ?int $ignoreId): bool
    {
        return static::query()
            ->where('slug', $slug)
            ->when($ignoreId !== null, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->exists();
    }

    private static function dateString(mixed $value): string
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        return substr((string) $value, 0, 10);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'progress' => 'integer',
            'employees_count' => 'integer',
            'on_date' => 'date',
        ];
    }

    /**
     * @return HasMany<Shift, $this>
     */
    public function shifts(): HasMany
    {
        return $this->hasMany(Shift::class);
    }

    public function refreshProgress(): void
    {
        $employees = Employee::query()->count();
        $months = [
            $this->on_date->format('Y-m') => $this->on_date->daysInMonth,
        ];
        $filled = 0;

        foreach ($this->shifts()->pluck('on_date') as $onDate) {
            $filled++;
            $month = Carbon::parse($onDate)->startOfMonth();
            $months[$month->format('Y-m')] = $month->daysInMonth;
        }

        $total = max($employees * array_sum($months), 1);
        $this->employees_count = $employees;
        $this->progress = (int) round($filled / $total * 100);
        $this->save();
    }
}
