<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Schedule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    private const LIST_LIMIT = 100;

    public function index(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'in:draft,published'],
        ]);

        $query = Schedule::query()->orderBy('on_date')->orderBy('id');

        $status = $filters['status'] ?? null;
        if (is_string($status) && $status !== '') {
            $query->where('status', $status);
        }

        $schedules = $query->limit(self::LIST_LIMIT)->get();

        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '') {
            // SQLite lower()/LIKE не учитывают кириллический регистр — оставляем mb_strtolower.
            $needle = mb_strtolower($search);
            $schedules = $schedules->filter(
                fn (Schedule $schedule) => str_contains(mb_strtolower($schedule->name), $needle),
            )->values();
        }

        return response()->json([
            'data' => $schedules->map(fn (Schedule $schedule) => $this->payload($schedule))->all(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
        ]);

        $schedule = Schedule::query()->create([
            'name' => $data['name'],
            'address' => $data['address'] ?? '',
            'status' => 'draft',
            'progress' => 0,
            'employees_count' => Employee::query()->count(),
            'on_date' => now()->toDateString(),
        ]);

        return response()->json(['data' => $this->payload($schedule)], 201);
    }

    public function show(string $schedule): JsonResponse
    {
        return response()->json(['data' => $this->payload($this->findSchedule($schedule))]);
    }

    public function update(Request $request, Schedule $schedule): JsonResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:draft,published'],
        ]);

        $schedule->status = $data['status'];
        $schedule->save();

        return response()->json(['data' => $this->payload($schedule)]);
    }

    private function findSchedule(string $key): Schedule
    {
        $query = Schedule::query()->where('slug', $key);
        if (ctype_digit($key)) {
            $query->orWhere('id', (int) $key);
        }

        return $query->firstOrFail();
    }

    /**
     * @return array{id: int, slug: string, name: string, address: string, status: string, progress: int, employees_count: int, on_date: string}
     */
    private function payload(Schedule $schedule): array
    {
        return [
            'id' => $schedule->id,
            'slug' => $schedule->slug,
            'name' => $schedule->name,
            'address' => $schedule->address,
            'status' => $schedule->status,
            'progress' => $schedule->progress,
            'employees_count' => $schedule->employees_count,
            'on_date' => $schedule->on_date->toDateString(),
        ];
    }
}
