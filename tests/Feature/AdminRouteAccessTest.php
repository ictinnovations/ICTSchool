<?php
/**
 * ICTSchool - administration route access tests.
 *
 * The user, permission and branch routes used to sit in a group with no auth
 * middleware, so an anonymous request could delete accounts, rewrite the role
 * grants or read the branch credentials. These tests hold every one of them shut
 * for anonymous visitors and for signed-in staff who are not Admin, and check
 * that an Admin can still use them. The impersonation route is covered as well,
 * since it shares the same history.
 */

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminRouteAccessTest extends TestCase
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

    private function addBranch()
    {
        DB::table('branches')->insert([
            'branch_name' => 'North', 'branch_url' => 'https://north.example.test',
            'username' => 'north-admin', 'password' => 'branch-secret',
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    private function addGrant()
    {
        DB::table('permission')->insert([
            'permission_name' => 'student_view', 'permission_group' => 'teacher',
            'permission_type' => 'yes', 'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    private function grants()
    {
        return DB::table('permission')->orderBy('id')->get()->toArray();
    }

    public function test_anonymous_visitor_cannot_log_in_as_another_user(): void
    {
        $admin = $this->user('Admin', 'admin');

        $this->get('/login/' . $admin->id)->assertRedirect('/');
        $this->get('/login/' . $admin->id . '/1')->assertNotFound();
        $this->assertGuest();
    }

    public function test_anonymous_visitor_cannot_delete_a_user(): void
    {
        $teacher = $this->user('Teacher', 'teacher');

        $this->get('/userdelete/' . $teacher->id)->assertRedirect('/');

        $this->assertNotNull(User::find($teacher->id));
    }

    public function test_anonymous_visitor_cannot_rewrite_the_role_grants(): void
    {
        $this->addGrant();
        $before = $this->grants();

        $this->get('/permission')->assertRedirect('/');
        $this->post('/permission/create', ['teacher' => ['student_add' => 'on']])->assertRedirect('/');

        $this->assertEquals($before, $this->grants());
    }

    public function test_anonymous_visitor_cannot_read_or_replace_branches(): void
    {
        $this->addBranch();

        $this->get('/branches')->assertRedirect('/')->assertDontSee('branch-secret');
        $this->post('/branch', [
            'branchname' => ['Evil'], 'url' => ['https://evil.example.test'],
            'username' => ['x'], 'password' => ['y'],
        ])->assertRedirect('/');

        $this->assertSame(['North'], DB::table('branches')->pluck('branch_name')->all());
    }

    public function test_staff_who_are_not_admin_are_kept_out(): void
    {
        $admin = $this->user('Admin', 'admin');
        $teacher = $this->user('Teacher', 'teacher');
        $other = $this->user('Teacher', 'other');
        $this->addBranch();
        $this->addGrant();
        $before = $this->grants();

        $this->actingAs($teacher);

        $this->get('/login/' . $admin->id);
        $this->assertAuthenticatedAs($teacher);

        $this->get('/userdelete/' . $other->id);
        $this->assertNotNull(User::find($other->id));

        $this->post('/permission/create', ['teacher' => ['student_add' => 'on']]);
        $this->assertEquals($before, $this->grants());

        $this->get('/branches')->assertDontSee('branch-secret');
        $this->get('/users')->assertDontSee('other@example.test');
    }

    public function test_admin_can_still_delete_a_user(): void
    {
        $admin = $this->user('Admin', 'admin');
        $teacher = $this->user('Teacher', 'teacher');

        $this->actingAs($admin)->get('/userdelete/' . $teacher->id)->assertRedirect('/users');

        $this->assertNull(User::find($teacher->id));
    }
}
