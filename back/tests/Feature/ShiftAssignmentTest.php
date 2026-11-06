<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\Schedule;
use App\Models\ShiftTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShiftAssignmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_shift_requires_a_real_employee_and_a_different_end_time(): void
    {
        $schedule = Schedule::query()->create([
            'name' => 'ТЦ Метрополис',
            'address' => 'Ходынский б-р, 4',
            'status' => 'draft',
            'progress' => 0,
            'employees_count' => 0,
            'on_date' => '2026-11-02',
        ]);
        $employee = Employee::query()->create([
            'name' => 'Кузнецова Алина',
            'role' => 'агент',
            'weekly_hours' => 24,
            'avatar' => '/avatars/alina.png',
        ]);
        $template = ShiftTemplate::query()->create([
            'name' => 'Утро',
            'starts_at' => '07:00',
            'ends_at' => '15:00',
            'border_color' => '#bbc9f7',
            'background_color' => '#eef1fd',
            'hotkey' => 1,
        ]);

        $this->postJson("/api/schedules/{$schedule->id}/shifts", [
            'employee_id' => 999,
            'on_date' => '2026-11-02',
            'starts_at' => '07:00',
            'ends_at' => '15:00',
        ])->assertUnprocessable();

        $this->postJson("/api/schedules/{$schedule->id}/shifts", [
            'employee_id' => $employee->id,
            'on_date' => '2026-11-02',
            'starts_at' => '09:00',
            'ends_at' => '09:00',
        ])->assertUnprocessable();

        $this->postJson("/api/schedules/{$schedule->id}/shifts", [
            'employee_id' => $employee->id,
            'on_date' => '2026-11-02',
            'starts_at' => '07:00',
            'ends_at' => '15:00',
            'template_id' => $template->id,
        ])->assertCreated();

        $this->patchJson("/api/schedules/{$schedule->id}", [
            'status' => 'published',
        ])->assertOk()->assertJsonPath('data.status', 'published');
    }

    public function test_bulk_shift_assignment_rejects_oversized_cell_lists(): void
    {
        $schedule = Schedule::query()->create([
            'name' => 'ТЦ Метрополис',
            'address' => 'Ходынский б-р, 4',
            'status' => 'draft',
            'progress' => 0,
            'employees_count' => 0,
            'on_date' => '2026-11-02',
        ]);
        $employee = Employee::query()->create([
            'name' => 'Кузнецова Алина',
            'role' => 'агент',
            'weekly_hours' => 24,
            'avatar' => '/avatars/alina.png',
        ]);

        $cells = [];
        for ($day = 1; $day <= 249; $day++) {
            $cells[] = [
                'employee_id' => $employee->id,
                'on_date' => sprintf('2026-01-%02d', (($day - 1) % 28) + 1),
            ];
        }

        $this->postJson("/api/schedules/{$schedule->id}/shifts", [
            'cells' => $cells,
            'starts_at' => '07:00',
            'ends_at' => '15:00',
        ])->assertUnprocessable();
    }
}
