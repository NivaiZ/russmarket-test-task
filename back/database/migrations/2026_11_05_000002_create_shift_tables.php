<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('role');
            $table->unsignedSmallInteger('weekly_hours');
            $table->string('avatar');
            $table->timestamps();
        });

        Schema::create('shift_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('starts_at', 5);
            $table->string('ends_at', 5);
            $table->string('border_color', 7);
            $table->string('background_color', 7);
            $table->unsignedTinyInteger('hotkey');
            $table->timestamps();
        });

        Schema::create('shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('template_id')->nullable()->constrained('shift_templates')->nullOnDelete();
            $table->date('on_date');
            $table->string('starts_at', 5);
            $table->string('ends_at', 5);
            $table->timestamps();

            $table->unique(['schedule_id', 'employee_id', 'on_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shifts');
        Schema::dropIfExists('shift_templates');
        Schema::dropIfExists('employees');
    }
};
