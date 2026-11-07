<?php

use App\Models\Schedule;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schedule::query()->whereDoesntHave('shifts')->update(['progress' => 0]);
    }

    public function down(): void
    {
        // Проценты без смен были заглушкой. Восстановить прежние числа нельзя.
    }
};
