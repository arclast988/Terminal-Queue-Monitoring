<?php

namespace Tests\Unit;

use App\Controllers\AppManifest;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\ControllerTestTrait;
use Config\Database;

final class AppManifestThemeTest extends CIUnitTestCase
{
    use ControllerTestTrait;
    private $themeDb;
    private array $connections;

    protected function setUp(): void
    {
        parent::setUp();
        $this->themeDb = Database::connect(['DBDriver'=>'SQLite3','database'=>':memory:','DBPrefix'=>'','DBDebug'=>true], false);
        $property = new \ReflectionProperty(\CodeIgniter\Database\Config::class, 'instances');
        $this->connections = $property->getValue();
        $property->setValue(null, array_replace($this->connections, ['default'=>$this->themeDb,'tests'=>$this->themeDb]));
        $this->themeDb->query('CREATE TABLE system_settings (id INTEGER PRIMARY KEY, setting_key TEXT, setting_value TEXT)');
        foreach (['guest'=>'#2563eb','staff'=>'#0d9488','admin'=>'#7040b0'] as $role=>$color) {
            $this->themeDb->table('system_settings')->insert(['setting_key'=>'theme_'.$role.'_primary','setting_value'=>$color]);
        }
        get_all_system_settings(true);
        session()->set(['role'=>'staff','isLoggedIn'=>true]);
    }

    protected function tearDown(): void
    {
        (new \ReflectionProperty(\CodeIgniter\Database\Config::class, 'instances'))->setValue(null, $this->connections);
        get_all_system_settings(true);
        $this->themeDb->close();
        parent::tearDown();
    }

    public function testManifestUsesSavedRoleColorsAndPreservesTheInstalledAppIdentity(): void
    {
        $template = json_decode(file_get_contents(FCPATH.'manifest.webmanifest'), true);
        foreach (['guest'=>'#2563eb','staff'=>'#0d9488','admin'=>'#7040b0','super_admin'=>'#7040b0'] as $role=>$expected) {
            $this->response = service('response', null, false);
            $this->withUri('http://localhost/app-manifest.webmanifest?role='.$role);
            $this->request->setGlobal('get', ['role'=>$role]);
            $response = $this->controller(AppManifest::class)->execute('index')->response();
            $manifest = json_decode($response->getBody(), true);
            $this->assertSame(200, $response->getStatusCode());
            $this->assertStringContainsString('application/manifest+json', $response->getHeaderLine('Content-Type'));
            $this->assertStringContainsString('no-store', $response->getHeaderLine('Cache-Control'));
            $this->assertSame($expected, $manifest['theme_color']);
            foreach (['id','start_url','scope','icons','display'] as $key) $this->assertSame($template[$key], $manifest[$key]);
        }
    }
}
