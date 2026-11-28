<?php
/**
 * Admin area access tests.
 *
 * School settings, academic years, levels, messaging setup, the SMS log,
 * promotion, applicants, holidays, the public website editor, the invoice
 * cron and the activity log all sat behind the auth middleware alone, so a
 * student account could change them or wipe the audit trail. These tests hold
 * them to Admin and check that an Admin can still open them.
 */

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAreaAccessTest extends TestCase
{
    use RefreshDatabase;

    private const ADMIN_PAGES = [
        '/settings', '/schedule', '/institute',
        '/academicYear', '/academicYear/list', '/academicYear/delete/1',
        '/level/list', '/level/delete/1',
        '/ictcore', '/notification_type', '/ictcore/attendance', '/ictcore/fees',
        '/smslog', '/smslog/delete/1',
        '/template/list', '/message/delete/1', '/message',
        '/promotion',
        '/applicants', '/applicants/delete/1',
        '/holidays', '/holidays/delete/1', '/class-off', '/class-off/delete/1',
        '/site/dashboard', '/site/about-content', '/site/testimonial', '/slider',
        '/cron/run', '/cron/invoices/months',
    ];

    private function user($group, $login)
    {
        return User::create([
            'firstname' => 'Test', 'lastname' => $group, 'login' => $login,
            'email' => $login . '@example.test', 'desc' => '', 'group' => $group,
            'password' => bcrypt('secret'),
        ]);
    }

    public function test_non_admin_is_turned_away_from_admin_pages(): void
    {
        foreach (['Student', 'Teacher'] as $group) {
            $user = $this->user($group, strtolower($group));
            foreach (self::ADMIN_PAGES as $url) {
                $this->actingAs($user)->get($url)->assertRedirect('page-not-found');
            }
        }
    }

    public function test_non_admin_cannot_read_or_clear_the_activity_log(): void
    {
        $student = $this->user('Student', 'student');

        $this->actingAs($student)->get('/activity')->assertRedirect('page-not-found');
        $this->actingAs($student)->delete('/activity/clear-activity')->assertRedirect('page-not-found');
        $this->actingAs($student)->delete('/activity/destroy-activity')->assertRedirect('page-not-found');
    }

    public function test_admin_can_still_open_admin_pages(): void
    {
        $admin = $this->user('Admin', 'admin');

        foreach (['/settings', '/institute', '/academicYear', '/level/list', '/smslog', '/holidays'] as $url) {
            $status = $this->actingAs($admin)->get($url)->getStatusCode();
            $this->assertNotEquals(302, $status, "$url redirected an Admin");
            $this->assertLessThan(500, $status, "$url errored for an Admin");
        }
    }
}
