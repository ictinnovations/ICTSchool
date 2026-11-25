<?php
/**
 * Dormitory fee report SQL injection test (GHSA-5ffp-cm9v-j9c9).
 *
 * The report built its query by pasting the dormId and month path segments into
 * the SQL string, so any logged-in user could inject. These tests check that both
 * values reach the database as bound parameters and never as SQL text.
 */

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DormitoryReportSqlTest extends TestCase
{
    use RefreshDatabase;

    private function reportQuery($dormId, $month)
    {
        $user = User::create([
            'firstname' => 'Test', 'lastname' => 'Admin', 'login' => 'admin',
            'email' => 'admin@example.test', 'desc' => '', 'group' => 'Admin',
            'password' => bcrypt('secret'),
        ]);

        $seen = [];
        DB::listen(function ($q) use (&$seen) {
            if (stripos($q->sql, 'dormitory_student') !== false) {
                $seen[] = $q;
            }
        });

        $this->actingAs($user)->get('/dormitory/report/fee/' . rawurlencode($dormId) . '/' . rawurlencode($month));

        $this->assertNotEmpty($seen, 'the report query did not run');
        return $seen[0];
    }

    public function test_dorm_id_is_bound_not_concatenated(): void
    {
        $q = $this->reportQuery('0 OR 1=1', '2026-01-01');
        $this->assertStringNotContainsString('1=1', $q->sql);
        $this->assertContains('0 OR 1=1', $q->bindings);
    }

    public function test_month_is_bound_not_concatenated(): void
    {
        $q = $this->reportQuery('1', "x') OR SLEEP(0)-- -");
        $this->assertStringNotContainsString('SLEEP', $q->sql);
        $this->assertContains("x') OR SLEEP(0)-- -", $q->bindings);
    }
}
