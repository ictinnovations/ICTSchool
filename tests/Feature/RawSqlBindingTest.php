<?php
/**
 * Raw SQL binding tests.
 *
 * Several queries pasted request values into DB::raw() or into a raw SQL
 * string. The section list and marks ones still ran on Laravel 11 and were
 * open to any signed-in account. These tests check that each value reaches
 * the database as a bound parameter and never as SQL text.
 */

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RawSqlBindingTest extends TestCase
{
    use RefreshDatabase;

    private const PAYLOAD = "1 OR SLEEP(0)";

    private function admin()
    {
        return User::create([
            'firstname' => 'Test', 'lastname' => 'Admin', 'login' => 'admin',
            'email' => 'admin@example.test', 'desc' => '', 'group' => 'Admin',
            'password' => bcrypt('secret'),
        ]);
    }

    /** Collect the SQL of every query touching $table while $fn runs. */
    private function queriesOn($table, callable $fn)
    {
        $seen = [];
        DB::listen(function ($q) use (&$seen, $table) {
            if (stripos($q->sql, $table) !== false) {
                $seen[] = $q;
            }
        });
        $fn();
        return $seen;
    }

    private function assertBound(array $seen, $value)
    {
        $this->assertNotEmpty($seen, 'the query did not run');
        foreach ($seen as $q) {
            $this->assertStringNotContainsString('SLEEP', $q->sql);
        }
        $this->assertContains($value, array_merge(...array_map(fn ($q) => $q->bindings, $seen)));
    }

    public function test_section_list_binds_the_session(): void
    {
        $student = User::create([
            'firstname' => 'Test', 'lastname' => 'Student', 'login' => 'student',
            'email' => 'student@example.test', 'desc' => '', 'group' => 'Student',
            'password' => bcrypt('secret'),
        ]);

        $seen = $this->queriesOn('section', function () use ($student) {
            $this->actingAs($student)->get('/section/getList/cl1/' . rawurlencode(self::PAYLOAD))->assertOk();
        });
        $this->assertBound($seen, self::PAYLOAD);
    }

    public function test_create_marks_binds_exam_and_subject(): void
    {
        DB::table('Class')->insert(['code' => 'cl1', 'name' => 'One', 'description' => '', 'created_at' => now(), 'updated_at' => now()]);
        $admin = $this->admin();

        $seen = $this->queriesOn('Marks', function () use ($admin) {
            $this->actingAs($admin)->get('/create/marks?show=1&class=cl1&section=1&session=2026'
                . '&exam=' . rawurlencode(self::PAYLOAD) . '&sub_id=' . rawurlencode('2 OR SLEEP(0)'));
        });
        $this->assertBound($seen, self::PAYLOAD);
        $this->assertBound($seen, '2 OR SLEEP(0)');
    }

    public function test_accounting_income_list_binds_the_year(): void
    {
        $admin = $this->admin();
        DB::table('permission')->insert([
            'permission_name' => 'accounting', 'permission_group' => 'admin',
            'permission_type' => 'yes', 'created_at' => now(), 'updated_at' => now(),
        ]);

        $seen = $this->queriesOn('accounting', function () use ($admin) {
            $this->actingAs($admin)->post('/accounting/incomelist', ['year' => self::PAYLOAD, 'month' => 'January']);
        });
        $this->assertBound($seen, self::PAYLOAD);
    }
}
