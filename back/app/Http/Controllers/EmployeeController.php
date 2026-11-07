<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    private const LIST_LIMIT = 100;

    public function index(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
        ]);

        $employees = Employee::query()->orderBy('id')->get();
        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '') {
            // SQLite lower()/LIKE не учитывают кириллический регистр — оставляем mb_strtolower.
            $needle = mb_strtolower($search);
            $employees = $employees->filter(
                fn (Employee $employee) => str_contains(mb_strtolower($employee->name), $needle),
            )->values();
        }

        $employees = $employees->take(self::LIST_LIMIT)->values();

        return response()->json([
            'data' => $employees->map(fn (Employee $employee) => [
                'id' => $employee->id,
                'name' => $employee->name,
                'role' => $employee->role,
                'weekly_hours' => $employee->weekly_hours,
                'avatar' => $this->safeAvatar($employee->avatar),
            ])->all(),
        ]);
    }

    private function safeAvatar(?string $avatar): string
    {
        if (! is_string($avatar) || $avatar === '') {
            return '';
        }

        if (preg_match('#^/avatars/[A-Za-z0-9._-]+$#', $avatar) === 1) {
            return $avatar;
        }

        return '';
    }
}
