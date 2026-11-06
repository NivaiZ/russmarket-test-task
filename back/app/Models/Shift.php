<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['schedule_id', 'employee_id', 'template_id', 'on_date', 'starts_at', 'ends_at'])]
class Shift extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'on_date' => 'date',
        ];
    }

    /**
     * @return BelongsTo<Schedule, $this>
     */
    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class);
    }

    /**
     * @return BelongsTo<ShiftTemplate, $this>
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(ShiftTemplate::class, 'template_id');
    }
}
