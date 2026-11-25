<?php
/**
 * Dormitory route access tests.
 *
 * The dormitory routes sat in a group with only the auth middleware, so any
 * signed-in account, students included, could add, edit and delete dormitories
 * and room assignments, take fees and print the reports. These tests hold them
 * to Admin and check that an Admin can still use them.
 */

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DormitoryRouteAccessTest extends TestCase
{
    use RefreshDatabase;

    private function user($group, $login)
    {
        return User::create([
            'firstname' => 'Test', 'lastname' => $group, 'login' => $login,
            'email' => $login . '@example.test', 'desc' => '', 'group' => $group,
            'password' => bcrypt('secret'),
        ]);
    }

    private function addDormitory()
    {
        return DB::table('dormitory')->insertGetId([
            'name' => 'North Hall', 'numOfRoom' => 10, 'address' => 'Campus',
            'description' => '', 'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    public function test_non_admin_is_turned_away_from_every_dormitory_page(): void
    {
        $dorm = $this->addDormitory();

        foreach (['Student', 'Teacher'] as $group) {
            $user = $this->user($group, strtolower($group));
            foreach ([
                '/dormitory', '/dormitory/assignstd', '/dormitory/assignstd/list',
                '/dormitory/fee', '/dormitory/report/std', '/dormitory/report/fee',
                '/dormitory/report/fee/' . $dorm . '/2026-01-01',
            ] as $url) {
                $this->actingAs($user)->get($url)->assertRedirect('page-not-found');
            }
        }
    }

    public function test_non_admin_cannot_delete_a_dormitory(): void
    {
        $dorm = $this->addDormitory();
        $student = $this->user('Student', 'student');

        $this->actingAs($student)->get('/dormitory/delete/' . $dorm)->assertRedirect('page-not-found');

        $this->assertNotNull(DB::table('dormitory')->find($dorm));
    }

    public function test_admin_can_still_open_the_dormitory_pages(): void
    {
        $admin = $this->user('Admin', 'admin');

        $this->actingAs($admin)->get('/dormitory')->assertOk();
        $this->actingAs($admin)->get('/dormitory/report/fee')->assertOk();
    }
}
