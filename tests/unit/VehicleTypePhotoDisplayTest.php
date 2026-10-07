<?php

namespace Tests\Unit;

use CodeIgniter\Cache\CacheInterface;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Database;
use Config\Services;
use ReflectionProperty;

final class VehicleTypePhotoDisplayTest extends CIUnitTestCase
{
    private BaseConnection $photoDb;
    private array $connections;
    private array $entries = [];
    private const TYPE_PHOTO = 'https://res.cloudinary.com/demo/image/upload/v123/vehicle_types/van.png';

    protected function setUp(): void
    {
        parent::setUp();
        $this->photoDb = Database::connect([
            'DBDriver' => 'SQLite3', 'database' => ':memory:', 'DBPrefix' => '', 'DBDebug' => true,
        ], false);
        $this->photoDb->query('CREATE TABLE vehicle_types (id INTEGER PRIMARY KEY, name TEXT, slug TEXT, color TEXT, icon TEXT, photo TEXT)');
        $this->photoDb->table('vehicle_types')->insert([
            'id' => 1, 'name' => 'Van', 'slug' => 'van', 'color' => '#1565c0',
            'icon' => 'fa-van-shuttle', 'photo' => self::TYPE_PHOTO,
        ]);
        $instances = new ReflectionProperty(\CodeIgniter\Database\Config::class, 'instances');
        $this->connections = $instances->getValue();
        $instances->setValue(null, ['tests' => $this->photoDb] + $this->connections);

        $cache = $this->createMock(CacheInterface::class);
        $cache->method('get')->willReturnCallback(fn ($key) => $this->entries[$key] ?? null);
        $cache->method('save')->willReturnCallback(function ($key, $value): bool {
            $this->entries[$key] = $value;
            return true;
        });
        $cache->method('delete')->willReturnCallback(function ($key): bool {
            unset($this->entries[$key]);
            return true;
        });
        Services::injectMock('cache', $cache);
    }

    protected function tearDown(): void
    {
        (new ReflectionProperty(\CodeIgniter\Database\Config::class, 'instances'))->setValue(null, $this->connections);
        $this->photoDb->close();
        parent::tearDown();
    }

    public function testSavedCloudinaryPhotoIsUsedByMetadataAndVehicleFallback(): void
    {
        $metadata = get_db_vehicle_types(true);
        $this->assertSame(self::TYPE_PHOTO, $metadata['van']['photo']);
        $this->assertSame(self::TYPE_PHOTO, get_db_vehicle_types()['van']['photo']);
        $this->assertSame(self::TYPE_PHOTO, vehicle_type_photo('van'));
        $this->assertSame(self::TYPE_PHOTO, vehicle_resolved_photo(['type' => 'van', 'photo' => null]));
        $this->assertSame(self::TYPE_PHOTO, vehicle_resolved_photo(['vehicle_type' => 'van', 'vehicle_photo' => null]));
    }

    public function testLocalTypePhotoRetainsItsFileVersion(): void
    {
        $this->photoDb->table('vehicle_types')->where('id', 1)->update(['photo' => 'images/logo.webp']);
        $this->assertSame(base_url('images/logo.webp') . '?v=' . filemtime(FCPATH . 'images/logo.webp'), get_db_vehicle_types(true)['van']['photo']);
    }

    public function testMalformedMetadataCachedByThePreviousReleaseIsNotReused(): void
    {
        $this->entries['db_vehicle_types'] = ['van' => ['photo' => base_url(self::TYPE_PHOTO)]];
        $this->assertSame(self::TYPE_PHOTO, vehicle_type_photo('van'));
    }

    public function testReplacingAndRemovingTheTypePhotoRefreshesTheFallback(): void
    {
        get_db_vehicle_types(true);
        $replacement = 'https://res.cloudinary.com/demo/image/upload/v124/vehicle_types/van.png';
        $this->photoDb->table('vehicle_types')->where('id', 1)->update(['photo' => $replacement]);
        get_db_vehicle_types(true);
        $this->assertSame($replacement, vehicle_resolved_photo(['type' => 'van', 'photo' => null]));
        $this->photoDb->table('vehicle_types')->where('id', 1)->update(['photo' => null]);
        get_db_vehicle_types(true);
        $this->assertNull(vehicle_type_photo('van'));
    }

    public function testIndividualVehiclePhotoTakesPriorityOverTypePhoto(): void
    {
        $custom = 'https://res.cloudinary.com/demo/image/upload/v126/vehicles/individual.png';
        $this->assertSame($custom, vehicle_resolved_photo(['type' => 'van', 'photo' => $custom]));
        $this->assertSame($custom, vehicle_resolved_photo(['vehicle_type' => 'van', 'vehicle_photo' => $custom]));
    }
}
