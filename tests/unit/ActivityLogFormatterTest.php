<?php

namespace Tests\Unit;

use App\Libraries\ActivityLogFormatter as LogText;
use CodeIgniter\Test\CIUnitTestCase;

final class ActivityLogFormatterTest extends CIUnitTestCase
{
    public function testOldRecordsUseReadableLabelsAndRemainSearchable(): void
    {
        $this->assertSame('Archived account', LogText::action('Deactivate user'));
        $this->assertSame('Signed in', LogText::action('login'));
        $this->assertContains('Deactivate user', LogText::matchingActions('Archived account'));
        $this->assertSame('Custom action', LogText::action('Custom action'));
    }

    public function testTechnicalSettingsBecomeCompleteSentences(): void
    {
        $text = LogText::details('System Settings', 'Updated operational rules: Queue Cooldown=30m, Log Retention=60d, Departure Retention=90d');
        $this->assertStringContainsString('wait 30 minutes', $text);
        $this->assertStringContainsString('activity logs are kept for 60 days', $text);
        $this->assertStringContainsString('departure history is kept for 90 days', $text);
        $this->assertSame('Dispatcher signed in (alex)', LogText::details('Login', 'Staff logged in (alex)'));
    }

    public function testDetailsRetainNamesAndExplainTheActionWithoutASeparateColumn(): void
    {
        $this->assertSame('Added route: VILLABA → ORMOC (2 vehicle type(s)).', LogText::details('Create route', 'VILLABA → ORMOC (2 vehicle type(s)).'));
        $this->assertSame('Posted announcement: Keep the Evening route open.', LogText::details('Create announcement', 'Keep the Evening route open.'));
        $this->assertSame('Edited announcement #12: Morning service', LogText::details('Update announcement', 'ID 12: Morning service'));
        $this->assertStringContainsString('"Deactivate user"', LogText::details('Deactivate user', 'Deactivated user "Deactivate user" (alex) and moved to archive'));
        $this->assertStringContainsString('No additional details', LogText::details('Login', null));
        $this->assertSame('Restored 1 account.', LogText::details('Bulk activate users', 'Activated 1 user(s).'));
        $this->assertSame('Permanently deleted 2 archived vehicles.', LogText::details('Bulk delete vehicles', 'Permanently deleted 2 archived vehicle(s).'));
        $this->assertSame('Permanently deleted 2 routes (4 route entries).', LogText::details('Bulk delete routes', 'Permanently deleted 2 route group(s) (4 route entries).'));
    }
}
