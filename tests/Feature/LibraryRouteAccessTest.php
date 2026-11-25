<?php
/**
 * Library route access and fine report query tests.
 *
 * The library routes sat in a group with only the auth middleware, so any
 * signed-in account, students included, could add, edit and delete books and
 * issue records. The fine report also pasted its month path segment into the
 * SQL string. These tests hold the routes to Admin and check that the month
 * reaches the database as a bound parameter.
 */

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class LibraryRouteAccessTest extends TestCase
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

    public function test_non_admin_is_turned_away_from_every_library_page(): void
    {
        foreach (['Student', 'Teacher'] as $group) {
            $user = $this->user($group, strtolower($group));
            foreach ([
                '/library/addbook', '/library/view', '/library/edit/1', '/library/delete/1',
                '/library/issuebook', '/library/issuebookview', '/library/issuebookdelete/1',
                '/library/search', '/library/reports', '/library/reports/fine',
                '/library/reports/fine/2026-01-01',
            ] as $url) {
                $this->actingAs($user)->get($url)->assertRedirect('page-not-found');
            }
        }
    }

    public function test_admin_can_still_open_the_library_pages(): void
    {
        $admin = $this->user('Admin', 'admin');

        $this->actingAs($admin)->get('/library/reports')->assertOk();
        $this->actingAs($admin)->get('/library/reports/fine')->assertOk();
    }

    public function test_fine_report_month_is_bound_not_concatenated(): void
    {
        $admin = $this->user('Admin', 'admin');

        $seen = [];
        DB::listen(function ($q) use (&$seen) {
            if (stripos($q->sql, 'issueBook') !== false) {
                $seen[] = $q;
            }
        });

        $month = "x') OR SLEEP(0)-- -";
        $this->actingAs($admin)->get('/library/reports/fine/' . rawurlencode($month));

        $this->assertNotEmpty($seen, 'the fine report query did not run');
        $this->assertStringNotContainsString('SLEEP', $seen[0]->sql);
        $this->assertContains($month, $seen[0]->bindings);
    }
}
