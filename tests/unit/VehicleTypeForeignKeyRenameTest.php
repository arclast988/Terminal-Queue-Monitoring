<?php

namespace Tests\Unit;

use App\Controllers\Admin\VehicleTypes;
use CodeIgniter\Cache\CacheInterface;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\ControllerTestTrait;
use Config\Database;
use Config\Services;
use ReflectionProperty;

class VehicleTypeForeignKeyHarness extends VehicleTypes
{
    protected function logActivity(string $action, string $details): void {}
    protected function broadcastUpdate(string $type, array $data = []): void {}
}

final class VehicleTypeForeignKeyRenameTest extends CIUnitTestCase
{
    use ControllerTestTrait;

    private BaseConnection $renameDb;
    private array $connections;

    protected function setUp(): void
    {
        parent::setUp();
        $this->renameDb = Database::connect([
            'DBDriver' => 'SQLite3', 'database' => ':memory:', 'DBPrefix' => '', 'DBDebug' => true,
        ], false);
        $this->renameDb->query('PRAGMA foreign_keys = ON');
        $this->renameDb->query('CREATE TABLE vehicle_types (
            id INTEGER PRIMARY KEY, name TEXT NOT NULL, slug TEXT NOT NULL UNIQUE,
            color TEXT, icon TEXT, photo TEXT, is_active INTEGER,
            created_at TEXT, updated_at TEXT
        )');
        $this->renameDb->table('vehicle_types')->insert([
            'id' => 1, 'name' => 'Van', 'slug' => 'van', 'color' => '#1565c0',
            'icon' => 'fa-van-shuttle', 'is_active' => 1,
        ]);
        $this->createReferences(true);

        $property = new ReflectionProperty(\CodeIgniter\Database\Config::class, 'instances');
        $this->connections = $property->getValue();
        $property->setValue(null, array_replace($this->connections, [
            'default' => $this->renameDb, 'tests' => $this->renameDb,
        ]));
        Services::injectMock('cache', $this->createMock(CacheInterface::class));
        session()->set(['role' => 'admin', 'isLoggedIn' => true, 'id' => 10]);
    }

    protected function tearDown(): void
    {
        (new ReflectionProperty(\CodeIgniter\Database\Config::class, 'instances'))->setValue(null, $this->connections);
        $this->renameDb->close();
        parent::tearDown();
    }

    private function createReferences(bool $foreignKeys): void
    {
        $routeConstraint = $foreignKeys ? ', FOREIGN KEY(vehicle_type) REFERENCES vehicle_types(slug) ON UPDATE CASCADE ON DELETE RESTRICT' : '';
        $vehicleConstraint = $foreignKeys ? ', FOREIGN KEY(type) REFERENCES vehicle_types(slug) ON UPDATE CASCADE ON DELETE RESTRICT' : '';
        $this->renameDb->query('CREATE TABLE routes (id INTEGER PRIMARY KEY, vehicle_type TEXT NOT NULL' . $routeConstraint . ')');
        $this->renameDb->query('CREATE TABLE vehicles (id INTEGER PRIMARY KEY, type TEXT NOT NULL' . $vehicleConstraint . ')');
        $this->renameDb->table('routes')->insert(['id' => 21, 'vehicle_type' => 'van']);
        $this->renameDb->table('vehicles')->insert(['id' => 31, 'type' => 'van']);
    }

    private function requestUpdate(string $name = 'Shuttle Van')
    {
        $fields = ['name' => $name, 'color' => '#1565c0', 'icon' => 'fa-van-shuttle'];
        $this->request->setMethod('POST')->setHeader('X-Requested-With', 'XMLHttpRequest')
            ->setHeader('Content-Type', 'application/x-www-form-urlencoded')->setGlobal('post', $fields);
        return $this->withBody(http_build_query($fields))->controller(VehicleTypeForeignKeyHarness::class)->execute('update', 1)->response();
    }

    private function assertReferences(string $name, string $slug): void
    {
        $type = $this->renameDb->table('vehicle_types')->where('id', 1)->get()->getRowArray();
        $this->assertSame($name, $type['name']);
        $this->assertSame($slug, $type['slug']);
        $this->assertSame($slug, $this->renameDb->table('routes')->where('id', 21)->get()->getRowArray()['vehicle_type']);
        $this->assertSame($slug, $this->renameDb->table('vehicles')->where('id', 31)->get()->getRowArray()['type']);
        $this->assertSame(0, (new ReflectionProperty(\CodeIgniter\Database\BaseConnection::class, 'transDepth'))->getValue($this->renameDb));
    }

    public function testRenameWorksWithEnforcedForeignKeysAndPreservesReferences(): void
    {
        $this->assertSame(200, $this->requestUpdate()->getStatusCode());
        $this->assertReferences('Shuttle Van', 'shuttle_van');
        $this->assertSame([], $this->renameDb->query('PRAGMA foreign_key_check')->getResultArray());
    }

    public function testLegacySchemaWithoutForeignKeysStillRenamesBothReferences(): void
    {
        $this->renameDb->query('DROP TABLE routes');
        $this->renameDb->query('DROP TABLE vehicles');
        $this->createReferences(false);

        $this->assertSame(200, $this->requestUpdate()->getStatusCode());
        $this->assertReferences('Shuttle Van', 'shuttle_van');
    }

    public function testFailedLegacyChildUpdateRollsBackParentAndPreviouslyUpdatedRoutes(): void
    {
        $this->renameDb->query('DROP TABLE routes');
        $this->renameDb->query('DROP TABLE vehicles');
        $this->createReferences(false);
        $this->renameDb->query("CREATE TRIGGER reject_vehicle_type_change BEFORE UPDATE OF type ON vehicles
            BEGIN SELECT RAISE(ABORT, 'Type change rejected'); END");

        $response = $this->requestUpdate();

        $this->assertSame(500, $response->getStatusCode());
        $this->assertFalse(json_decode($response->getBody(), true)['success']);
        $this->assertReferences('Van', 'van');
    }

    public function testFailedCascadeRollsBackTheWholeRename(): void
    {
        $this->renameDb->query("CREATE TRIGGER reject_vehicle_type_change BEFORE UPDATE OF type ON vehicles
            BEGIN SELECT RAISE(ABORT, 'Type change rejected'); END");

        $this->assertSame(500, $this->requestUpdate()->getStatusCode());
        $this->assertReferences('Van', 'van');
        $this->assertSame([], $this->renameDb->query('PRAGMA foreign_key_check')->getResultArray());
    }

    public function testUnchangedInputDoesNotLeaveATransactionOpen(): void
    {
        $response = $this->requestUpdate('Van');

        $this->assertSame(200, $response->getStatusCode());
        $this->assertTrue(json_decode($response->getBody(), true)['no_change']);
        $this->assertReferences('Van', 'van');
    }

    public function testEmptyAndOversizedDerivedSlugsAreRejectedByStoreAndUpdate(): void
    {
        foreach (['!!', str_repeat('a', 51)] as $name) {
            $this->assertSame(302, $this->requestUpdate($name)->getStatusCode());
            $this->assertReferences('Van', 'van');

            $fields = ['name' => $name];
            $this->request->setMethod('POST')->setGlobal('post', $fields);
            $response = $this->withBody(http_build_query($fields))->controller(VehicleTypeForeignKeyHarness::class)->execute('store')->response();
            $this->assertSame(302, $response->getStatusCode());
            $this->assertSame(1, $this->renameDb->table('vehicle_types')->countAllResults());
            $this->assertReferences('Van', 'van');
        }
    }
}
