<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Config\Database;

final class AppManifestThemeTest extends CIUnitTestCase
{
    use FeatureTestTrait;
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
        $template = json_decode(file_get_contents(APPPATH.'Config/app.webmanifest'), true);
        foreach (['guest'=>'#2563eb','staff'=>'#0d9488','admin'=>'#7040b0','super_admin'=>'#7040b0'] as $role=>$expected) {
            $response = $this->withSession(['role'=>$role, 'isLoggedIn'=>$role!=='guest'])->get('/manifest.webmanifest')->response();
            $manifest = json_decode($response->getBody(), true);
            $this->assertSame(200, $response->getStatusCode());
            $this->assertStringContainsString('application/manifest+json', $response->getHeaderLine('Content-Type'));
            $this->assertStringContainsString('no-store', $response->getHeaderLine('Cache-Control'));
            $this->assertSame($expected, $manifest['theme_color']);
            foreach (['id','start_url','scope','icons','display'] as $key) $this->assertSame($template[$key], $manifest[$key]);
        }
    }

    public function testOriginalInstallAddressReflectsThemeChangesWithoutChangingItsUrl(): void
    {
        $this->withSession(['role'=>'guest']);
        $first = json_decode($this->get('/manifest.webmanifest')->response()->getBody(), true);
        $this->themeDb->table('system_settings')->where('setting_key', 'theme_guest_primary')->update(['setting_value'=>'#7040b0']);
        get_all_system_settings(true);
        $updated = json_decode($this->get('/manifest.webmanifest')->response()->getBody(), true);
        $this->assertSame('#2563eb', $first['theme_color']);
        $this->assertSame('#7040b0', $updated['theme_color']);
        $this->assertSame($first['id'], $updated['id']);
    }

    public function testPreviousManifestAddressStillWorksForExistingInstallations(): void
    {
        $response = $this->withSession(['role'=>'staff', 'isLoggedIn'=>true])
            ->get('/app-manifest.webmanifest?role=guest&theme=2563eb')->response();
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('#2563eb', json_decode($response->getBody(), true)['theme_color']);
    }
}
