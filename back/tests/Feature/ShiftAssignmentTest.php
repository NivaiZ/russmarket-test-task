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

    public function test_progress_counts_a_shift_outside_the_anchor_month(): void
    {
        $schedule = Schedule::query()->create([
            'name' => 'ТЦ Метрополис',
            'address' => 'Ходынский б-р, 4',
            'status' => 'draft',
            'progress' => 91,
            'employees_count' => 1,
            'on_date' => '2026-11-02',
        ]);
        $employee = Employee::query()->create([
            'name' => 'Кузнецова Алина',
            'role' => 'агент',
            'weekly_hours' => 24,
            'avatar' => '/avatars/alina.png',
        ]);

        $this->postJson("/api/schedules/{$schedule->id}/shifts", [
            'employee_id' => $employee->id,
            'on_date' => '2026-12-01',
            'starts_at' => '07:00',
            'ends_at' => '15:00',
        ])->assertCreated();

        $schedule->refresh();
        $this->assertSame(2, $schedule->progress);
    }

    public function test_selected_shifts_are_cleared_in_one_request(): void
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

        $created = $this->postJson("/api/schedules/{$schedule->id}/shifts", [
            'cells' => [
                ['employee_id' => $employee->id, 'on_date' => '2026-11-02'],
                ['employee_id' => $employee->id, 'on_date' => '2026-11-03'],
            ],
            'starts_at' => '07:00',
            'ends_at' => '15:00',
        ])->assertCreated()->json('data');

        $this->deleteJson("/api/schedules/{$schedule->id}/shifts", [
            'ids' => [$created[0]['id'], $created[1]['id']],
        ])->assertOk();

        $this->assertDatabaseCount('shifts', 0);
        $schedule->refresh();
        $this->assertSame(0, $schedule->progress);
    }
}
