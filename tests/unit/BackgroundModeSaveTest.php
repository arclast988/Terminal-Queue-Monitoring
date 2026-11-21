<?php

namespace Tests\Unit;

use App\Controllers\Admin\Settings;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\ControllerTestTrait;
use Config\Database;

class BackgroundModeSaveHarness extends Settings
{
    public static array $activity = [];
    public static array $broadcasts = [];
    protected function logActivity(string $action, string $details): void { self::$activity[] = [$action,$details]; }
    protected function broadcastBrandingChange(?string $category = null): void { self::$broadcasts[] = $category; }
}

final class BackgroundModeSaveTest extends CIUnitTestCase
{
    use ControllerTestTrait;

    private $backgroundDb;
    private array $connections;

    protected function setUp(): void
    {
        parent::setUp();
        $this->backgroundDb = Database::connect(['DBDriver'=>'SQLite3','database'=>':memory:','DBPrefix'=>'','DBDebug'=>true], false);
        $instances = new \ReflectionProperty(\CodeIgniter\Database\Config::class,'instances');
        $this->connections = $instances->getValue();
        $instances->setValue(null,array_replace($this->connections,['default'=>$this->backgroundDb,'tests'=>$this->backgroundDb]));
        $this->backgroundDb->query('CREATE TABLE system_settings (id INTEGER PRIMARY KEY AUTOINCREMENT, setting_key TEXT UNIQUE, setting_value TEXT, created_at TEXT, updated_at TEXT)');
        foreach (['app_background_image'=>'uploads/custom-background.png','app_bg_slideshow_1'=>'uploads/slide.png','app_bg_slideshow_slots'=>'1'] as $key=>$value) {
            $this->backgroundDb->table('system_settings')->insert(['setting_key'=>$key,'setting_value'=>$value]);
        }
        BackgroundModeSaveHarness::$activity = BackgroundModeSaveHarness::$broadcasts = [];
        session()->set(['isLoggedIn'=>true,'id'=>1,'role'=>'super_admin']);
        get_all_system_settings(true);
    }

    protected function tearDown(): void
    {
        (new \ReflectionProperty(\CodeIgniter\Database\Config::class,'instances'))->setValue(null,$this->connections);
        get_all_system_settings(true);
        $this->backgroundDb->close();
        parent::tearDown();
    }

    private function save(string $mode): array
    {
        $this->withUri('http://localhost/admin/settings/save-bg-mode');
        $this->request->setMethod('POST')->setGlobal('post',['mode'=>$mode]);
        $response = $this->controller(BackgroundModeSaveHarness::class)->execute('saveBgMode')->response();
        $this->assertSame(200,$response->getStatusCode());
        return json_decode($response->getBody(),true,512,JSON_THROW_ON_ERROR);
    }

    private function rows(): array
    {
        return $this->backgroundDb->table('system_settings')->orderBy('id')->get()->getResultArray();
    }

    public function testEverySavedModeReturnsNoChangesWithoutWritingAuditingOrBroadcasting(): void
    {
        foreach (['none','single','slideshow'] as $mode) {
            $this->backgroundDb->table('system_settings')->where('setting_key','app_bg_mode')->delete();
            $this->backgroundDb->table('system_settings')->insert(['setting_key'=>'app_bg_mode','setting_value'=>$mode,'updated_at'=>'2026-11-19 12:00:00']);
            $before = $this->rows();
            $data = $this->save($mode);
            $this->assertTrue($data['success']);
            $this->assertTrue($data['no_change']);
            $this->assertSame($mode,$data['app_bg_mode']);
            $this->assertStringContainsString('No changes detected',$data['message']);
            $this->assertArrayHasKey('csrf_token',$data);
            $this->assertArrayHasKey('csrf_hash',$data);
            $this->assertArrayNotHasKey('data',$data);
            $this->assertSame($before,$this->rows());
        }
        $this->assertSame([],BackgroundModeSaveHarness::$activity);
        $this->assertSame([],BackgroundModeSaveHarness::$broadcasts);
    }

    public function testMissingEmptyAndInvalidStoredModesUseTheSlideshowDefaultWithoutCreatingOrChangingRows(): void
    {
        foreach ([null,'','invalid'] as $saved) {
            $this->backgroundDb->table('system_settings')->where('setting_key','app_bg_mode')->delete();
            if ($saved !== null) $this->backgroundDb->table('system_settings')->insert(['setting_key'=>'app_bg_mode','setting_value'=>$saved]);
            $before = $this->rows();
            $this->assertTrue($this->save('slideshow')['no_change']);
            $this->assertSame($before,$this->rows());
        }
        $this->assertSame([],BackgroundModeSaveHarness::$activity);
        $this->assertSame([],BackgroundModeSaveHarness::$broadcasts);
    }

    public function testRealModeChangesStillSaveOnceAndKeepEveryImageSetting(): void
    {
        $images = $this->rows();
        foreach (['single','none','slideshow'] as $index=>$mode) {
            $data = $this->save($mode);
            $this->assertTrue($data['success']);
            $this->assertFalse($data['no_change'] ?? false);
            $this->assertSame($mode,$data['data']['app_bg_mode']);
            $this->assertSame($mode,$this->backgroundDb->table('system_settings')->where('setting_key','app_bg_mode')->get()->getRowArray()['setting_value']);
            $this->assertCount($index+1,BackgroundModeSaveHarness::$activity);
            $this->assertCount($index+1,BackgroundModeSaveHarness::$broadcasts);
        }
        $this->assertSame($images,$this->backgroundDb->table('system_settings')->where('setting_key !=','app_bg_mode')->orderBy('id')->get()->getResultArray());
    }
}
