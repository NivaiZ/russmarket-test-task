<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'starts_at', 'ends_at', 'border_color', 'background_color', 'hotkey'])]
class ShiftTemplate extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'hotkey' => 'integer',
        ];
    }
}
