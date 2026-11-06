<?php

use App\Models\Schedule;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique()->after('name');
        });

        Schedule::query()->orderBy('id')->each(function (Schedule $schedule): void {
            $schedule->slug = Schedule::uniqueSlug(
                $schedule->name,
                $schedule->on_date->toDateString(),
                $schedule->id,
            );
            $schedule->save();
        });
    }

    public function down(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });
    }
};
