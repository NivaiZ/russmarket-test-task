<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Schedule;
use App\Models\ShiftTemplate;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if (Schedule::query()->doesntExist()) {
            $points = [
                ['status' => 'published', 'progress' => 0, 'on_date' => '2026-08-30'],
                ['status' => 'draft', 'progress' => 0, 'on_date' => '2026-08-31'],
                ['status' => 'draft', 'progress' => 0, 'on_date' => '2026-08-31'],
            ];
            foreach ($points as $point) {
                Schedule::query()->create([
                    'name' => 'ТЦ Метрополис',
                    'slug' => Schedule::uniqueSlug('ТЦ Метрополис', $point['on_date']),
                    'address' => 'Ходынский б-р, 4',
                    'status' => $point['status'],
                    'progress' => $point['progress'],
                    'employees_count' => 8,
                    'on_date' => $point['on_date'],
                ]);
            }
        }

        $employees = [
            ['name' => 'Кузнецова Алина', 'avatar' => '/avatars/alina.png'],
            ['name' => 'Волков Тимур', 'avatar' => '/avatars/timur.png'],
            ['name' => 'Морозова Виктория', 'avatar' => '/avatars/viktoria.png'],
            ['name' => 'Соколов Артём', 'avatar' => '/avatars/artem.png'],
            ['name' => 'Лебедева Полина', 'avatar' => '/avatars/polina.png'],
            ['name' => 'Новиков Кирилл', 'avatar' => '/avatars/kirill.png'],
            ['name' => 'Ерохина Дарья', 'avatar' => '/avatars/daria.png'],
            ['name' => 'Гаврилов Егор', 'avatar' => '/avatars/egor.png'],
        ];
        foreach ($employees as $employee) {
            Employee::query()->updateOrCreate(
                ['name' => $employee['name']],
                ['role' => 'агент', 'weekly_hours' => 24, 'avatar' => $employee['avatar']],
            );
        }

        $templates = [
            ['name' => 'Утро', 'starts_at' => '07:00', 'ends_at' => '15:00', 'border_color' => '#bbc9f7', 'background_color' => '#eef1fd', 'hotkey' => 1],
            ['name' => 'День', 'starts_at' => '10:00', 'ends_at' => '18:00', 'border_color' => '#c8adff', 'background_color' => '#f4edff', 'hotkey' => 2],
            ['name' => 'Вечер', 'starts_at' => '15:00', 'ends_at' => '23:00', 'border_color' => '#b2eeeb', 'background_color' => '#e8f8f7', 'hotkey' => 3],
        ];
        foreach ($templates as $template) {
            ShiftTemplate::query()->updateOrCreate(
                ['name' => $template['name']],
                $template,
            );
        }

        Schedule::query()->update(['employees_count' => Employee::query()->count()]);
    }
}
