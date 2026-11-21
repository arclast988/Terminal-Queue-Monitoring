<?php

namespace Tests\Unit;

use App\Controllers\Admin\Settings;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\ControllerTestTrait;
use Config\Database;

class ContentManagerNoChangeHarness extends Settings
{
    public static array $activity = [];
    protected function logActivity(string $action, string $details): void { self::$activity[] = [$action, $details]; }
}

final class ContentManagerNoChangeTest extends CIUnitTestCase
{
    use ControllerTestTrait;
    private $contentDb;
    private array $connections;

    protected function setUp(): void
    {
        parent::setUp();
        $this->contentDb = Database::connect(['DBDriver'=>'SQLite3', 'database'=>':memory:', 'DBPrefix'=>'', 'DBDebug'=>true], false);
        $instances = new \ReflectionProperty(\CodeIgniter\Database\Config::class, 'instances');
        $this->connections = $instances->getValue();
        $instances->setValue(null, array_replace($this->connections, ['default'=>$this->contentDb, 'tests'=>$this->contentDb]));
        $this->contentDb->query('CREATE TABLE system_settings (id INTEGER PRIMARY KEY AUTOINCREMENT, setting_key TEXT UNIQUE, setting_value TEXT, created_at TEXT, updated_at TEXT)');
        $this->contentDb->table('system_settings')->insert(['setting_key'=>'content_contact_intro', 'setting_value'=>'Saved introduction.']);
        get_all_system_settings(true);
        ContentManagerNoChangeHarness::$activity = [];
        session()->set(['isLoggedIn'=>true, 'id'=>1, 'role'=>'super_admin']);
        session()->remove(['content_no_changes', 'info']);
    }

    protected function tearDown(): void
    {
        session()->remove(['content_no_changes', 'info', '_ci_old_input']);
        (new \ReflectionProperty(\CodeIgniter\Database\Config::class, 'instances'))->setValue(null, $this->connections);
        get_all_system_settings(true);
        $this->contentDb->close();
        parent::tearDown();
    }

    private function assertUnchangedNotice(string $value): void
    {
        $before = $this->contentDb->table('system_settings')->get()->getResultArray();
        $this->withUri('http://localhost/admin/settings/content');
        $this->request->setMethod('POST')->setGlobal('post', ['content_contact_intro'=>$value]);
        $response = $this->controller(ContentManagerNoChangeHarness::class)->execute('updateContent')->response();
        $this->assertSame(302, $response->getStatusCode());
        $this->assertStringEndsWith('/admin/settings/content', $response->getHeaderLine('Location'));
        $this->assertTrue(session()->getFlashdata('content_no_changes'));
        $this->assertSame('No changes detected — nothing was updated.', session()->getFlashdata('info'));
        $this->assertSame($before, $this->contentDb->table('system_settings')->get()->getResultArray());
        $this->assertSame([], ContentManagerNoChangeHarness::$activity);
    }

    public function testIdenticalContentUsesThePopupNoticeWithoutWritingOrAuditing(): void
    {
        $this->assertUnchangedNotice('Saved introduction.');
    }

    public function testNormalizedWhitespaceAlsoUsesThePopupNoticeWithoutWriting(): void
    {
        $this->assertUnchangedNotice("  Saved introduction.\r\n ");
    }
}
