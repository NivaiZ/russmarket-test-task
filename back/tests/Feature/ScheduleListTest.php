<?php

namespace Tests\Feature;

use App\Models\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScheduleListTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_matches_point_name_regardless_of_cyrillic_case(): void
    {
        Schedule::query()->create([
            'name' => 'ТЦ Метрополис',
            'address' => 'Ходынский б-р, 4',
            'status' => 'published',
            'progress' => 91,
            'employees_count' => 24,
            'on_date' => '2026-08-30',
        ]);
        Schedule::query()->create([
            'name' => 'ТЦ Авиапарк',
            'address' => 'Ходынский б-р, 4',
            'status' => 'draft',
            'progress' => 10,
            'employees_count' => 4,
            'on_date' => '2026-08-31',
        ]);

        $response = $this->getJson('/api/schedules?search=метрополис');

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.name', 'ТЦ Метрополис');
    }

    public function test_new_schedule_is_a_draft_so_the_list_can_place_it_in_that_group(): void
    {
        $response = $this->postJson('/api/schedules', [
            'name' => 'Новый график',
            'address' => '',
        ]);

        $response->assertCreated();
        $response->assertJsonPath('data.status', 'draft');
        $response->assertJsonPath('data.slug', 'novyi-grafik');
        $this->assertDatabaseHas('schedules', [
            'name' => 'Новый график',
            'status' => 'draft',
            'slug' => 'novyi-grafik',
        ]);
    }

    public function test_schedule_opens_by_slug_and_keeps_the_numeric_address_working(): void
    {
        $schedule = Schedule::query()->create([
            'name' => 'ТЦ Метрополис',
            'address' => 'Ходынский б-р, 4',
            'status' => 'published',
            'progress' => 1,
            'employees_count' => 8,
            'on_date' => '2026-08-30',
        ]);

        $this->getJson('/api/schedules/'.$schedule->slug)
            ->assertOk()
            ->assertJsonPath('data.name', 'ТЦ Метрополис');

        $this->getJson('/api/schedules/'.$schedule->id)
            ->assertOk()
            ->assertJsonPath('data.slug', $schedule->slug);
    }

    public function test_api_requests_are_rate_limited(): void
    {
        for ($index = 0; $index < 120; $index++) {
            $this->getJson('/api/schedules')->assertOk();
        }

        $this->getJson('/api/schedules')->assertStatus(429);
    }
}
