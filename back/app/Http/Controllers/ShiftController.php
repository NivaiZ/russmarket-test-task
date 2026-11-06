<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use App\Models\Shift;
use App\Models\ShiftTemplate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ShiftController extends Controller
{
    public function index(Request $request, Schedule $schedule): JsonResponse
    {
        $filters = $request->validate([
            'from' => ['required', 'date'],
            'to' => ['required', 'date'],
        ]);

        $shifts = $schedule->shifts()
            ->with('template')
            ->whereBetween('on_date', [$filters['from'], $filters['to']])
            ->orderBy('on_date')
            ->get();

        return response()->json([
            'data' => $shifts->map(fn (Shift $shift) => $this->payload($shift))->all(),
        ]);
    }

    public function store(Request $request, Schedule $schedule): JsonResponse
    {
        $data = $request->validate([
            'employee_id' => ['required_without:cells', 'integer', 'exists:employees,id'],
            'on_date' => ['required_without:cells', 'date'],
            'cells' => ['required_without:employee_id', 'array', 'min:1', 'max:248'],
            'cells.*.employee_id' => ['required', 'integer', 'exists:employees,id'],
            'cells.*.on_date' => ['required', 'date'],
            'starts_at' => ['required', 'date_format:H:i'],
            'ends_at' => ['required', 'date_format:H:i', 'different:starts_at'],
            'template_id' => ['nullable', 'integer', 'exists:shift_templates,id'],
        ]);

        $cells = $data['cells'] ?? [[
            'employee_id' => $data['employee_id'],
            'on_date' => $data['on_date'],
        ]];

        $created = [];
        foreach ($cells as $cell) {
            $shift = Shift::query()->updateOrCreate(
                [
                    'schedule_id' => $schedule->id,
                    'employee_id' => $cell['employee_id'],
                    'on_date' => $cell['on_date'],
                ],
                [
                    'starts_at' => $data['starts_at'],
                    'ends_at' => $data['ends_at'],
                    'template_id' => $data['template_id'] ?? null,
                ],
            );
            $created[] = $this->payload($shift->load('template'));
        }

        $schedule->refreshProgress();

        return response()->json(['data' => $created], 201);
    }

    public function update(Request $request, Shift $shift): JsonResponse
    {
        $data = $request->validate([
            'employee_id' => ['sometimes', 'integer', 'exists:employees,id'],
            'on_date' => ['sometimes', 'date'],
            'starts_at' => ['sometimes', 'date_format:H:i'],
            'ends_at' => ['sometimes', 'date_format:H:i'],
            'template_id' => ['nullable', 'integer', 'exists:shift_templates,id'],
        ]);

        $starts = $data['starts_at'] ?? $shift->starts_at;
        $ends = $data['ends_at'] ?? $shift->ends_at;
        if ($starts === $ends) {
            throw ValidationException::withMessages([
                'ends_at' => 'Время окончания должно отличаться от времени начала.',
            ]);
        }

        $employeeId = $data['employee_id'] ?? $shift->employee_id;
        $onDate = $data['on_date'] ?? $shift->on_date->toDateString();
        $occupied = Shift::query()
            ->where('schedule_id', $shift->schedule_id)
            ->where('employee_id', $employeeId)
            ->whereDate('on_date', $onDate)
            ->whereKeyNot($shift->id)
            ->exists();
        if ($occupied) {
            throw ValidationException::withMessages([
                'on_date' => 'В этой ячейке уже есть смена.',
            ]);
        }

        $shift->fill([
            'employee_id' => $employeeId,
            'on_date' => $onDate,
            'starts_at' => $starts,
            'ends_at' => $ends,
        ]);
        if (array_key_exists('template_id', $data)) {
            $shift->template_id = $data['template_id'];
        }
        $shift->save();
        $shift->schedule->refreshProgress();

        return response()->json(['data' => $this->payload($shift->load('template'))]);
    }

    public function destroy(Shift $shift): JsonResponse
    {
        $schedule = $shift->schedule;
        $shift->delete();
        $schedule->refreshProgress();

        return response()->json(status: 204);
    }

    /**
     * @return array{id: int, schedule_id: int, employee_id: int, template_id: int|null, on_date: string, starts_at: string, ends_at: string, border_color: string, background_color: string}
     */
    private function payload(Shift $shift): array
    {
        $template = $shift->relationLoaded('template') ? $shift->template : null;
        if ($template === null && $shift->template_id !== null) {
            $template = ShiftTemplate::query()->find($shift->template_id);
        }

        return [
            'id' => $shift->id,
            'schedule_id' => $shift->schedule_id,
            'employee_id' => $shift->employee_id,
            'template_id' => $shift->template_id,
            'on_date' => $shift->on_date->toDateString(),
            'starts_at' => $shift->starts_at,
            'ends_at' => $shift->ends_at,
            'border_color' => $template->border_color ?? '#5b7dec',
            'background_color' => $template->background_color ?? '#e8edfc',
        ];
    }
}
