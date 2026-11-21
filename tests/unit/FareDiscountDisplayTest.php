<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;
use Config\Database;

final class FareDiscountDisplayTest extends CIUnitTestCase
{
    private $fareDb;
    private array $originalConnections;
    private array $originalConfig;

    protected function setUp(): void
    {
        parent::setUp();
        helper('fare');
        $this->originalConfig = config(Database::class)->tests;
        $property = new \ReflectionProperty(\CodeIgniter\Database\Config::class, 'instances');
        $this->originalConnections = $property->getValue();
        $property->setValue(null, []);
        config(Database::class)->tests = ['DBDriver'=>'SQLite3', 'database'=>':memory:', 'DBPrefix'=>'', 'DBDebug'=>true];
        $this->fareDb = Database::connect();
        $this->fareDb->query('CREATE TABLE fare_discounts (id INTEGER PRIMARY KEY, terminal_id INTEGER, type TEXT, label TEXT, discount_percent REAL, is_active INTEGER)');
        $this->fareDb->query('CREATE TABLE fares (id INTEGER PRIMARY KEY AUTOINCREMENT, route_id INTEGER, fare_discount_id INTEGER, amount REAL)');
        $this->fareDb->table('fare_discounts')->insertBatch([
            ['id'=>1,'terminal_id'=>1,'type'=>'regular','label'=>'Regular Fare','discount_percent'=>0,'is_active'=>1],
            ['id'=>2,'terminal_id'=>1,'type'=>'pwd','label'=>'PWD Discount','discount_percent'=>20,'is_active'=>1],
            ['id'=>3,'terminal_id'=>1,'type'=>'student','label'=>'Student Discount','discount_percent'=>15,'is_active'=>1],
            ['id'=>4,'terminal_id'=>1,'type'=>'senior_citizen','label'=>'Senior Citizen Discount','discount_percent'=>20,'is_active'=>1],
            ['id'=>5,'terminal_id'=>1,'type'=>'inactive','label'=>'Inactive Discount','discount_percent'=>50,'is_active'=>0],
            ['id'=>6,'terminal_id'=>2,'type'=>'pwd','label'=>'Other Terminal Discount','discount_percent'=>10,'is_active'=>1],
            ['id'=>7,'terminal_id'=>2,'type'=>'regular','label'=>'Regular Fare','discount_percent'=>0,'is_active'=>1],
        ]);
        foreach ([[101,1,150],[102,1,80],[103,1,100],[103,2,77],[103,3,85],[201,7,120],[101,5,75]] as [$route,$discount,$amount]) {
            $this->fareDb->table('fares')->insert(['route_id'=>$route,'fare_discount_id'=>$discount,'amount'=>$amount]);
        }
    }

    protected function tearDown(): void
    {
        $this->fareDb->close();
        config(Database::class)->tests = $this->originalConfig;
        (new \ReflectionProperty(\CodeIgniter\Database\Config::class, 'instances'))->setValue(null, $this->originalConnections);
        parent::tearDown();
    }

    public function testEveryVehicleRouteShowsItsOwnConfiguredDiscountsWithoutDatabaseWrites(): void
    {
        $before = $this->fareDb->table('fares')->orderBy('id')->get()->getResultArray();
        $routes = enrich_routes_with_discounts([
            ['id'=>101,'terminal_id'=>1,'vehicle_type'=>'bus'],
            ['id'=>102,'terminal_id'=>1,'vehicle_type'=>'van'],
            ['id'=>103,'terminal_id'=>1,'vehicle_type'=>'van'],
        ], $this->fareDb->table('fare_discounts')->where('is_active',1)->get()->getResultArray());

        $this->assertSame(150.0, $routes[0]['fare']);
        $this->assertSame(120.0, $routes[0]['discounted_fares']['pwd']['amount']);
        $this->assertSame(127.5, $routes[0]['discounted_fares']['student']['amount']);
        $this->assertSame(120.0, $routes[0]['discounted_fares']['senior_citizen']['amount']);
        $this->assertSame(64.0, $routes[1]['discounted_fares']['pwd']['amount']);
        $this->assertSame(68.0, $routes[1]['discounted_fares']['student']['amount']);
        $this->assertSame(77.0, $routes[2]['discounted_fares']['pwd']['amount'], 'Preserve an explicitly stored fare.');
        $this->assertSame(85.0, $routes[2]['discounted_fares']['student']['amount']);
        $this->assertSame(80.0, $routes[2]['discounted_fares']['senior_citizen']['amount']);
        $this->assertArrayNotHasKey('inactive', $routes[0]['discounted_fares']);
        $this->assertSame($before, $this->fareDb->table('fares')->orderBy('id')->get()->getResultArray());
    }

    public function testOmittedPoliciesAreLoadedForTheRoutesTerminalOnly(): void
    {
        $routes = enrich_routes_with_discounts([
            ['id'=>101,'terminal_id'=>1], ['id'=>201,'terminal_id'=>2], ['id'=>301,'terminal_id'=>3,'fare'=>50],
        ]);
        $this->assertSame(120.0, $routes[0]['discounted_fares']['pwd']['amount']);
        $this->assertSame(['pwd'], array_keys($routes[1]['discounted_fares']));
        $this->assertSame('Other Terminal Discount', $routes[1]['discounted_fares']['pwd']['label']);
        $this->assertSame(108.0, $routes[1]['discounted_fares']['pwd']['amount']);
        $this->assertSame([], $routes[2]['discounted_fares']);
    }

    public function testMissingRegularFaresDoNotAcquireZeroPriceDiscounts(): void
    {
        $routes = enrich_routes_with_discounts([['id'=>999,'terminal_id'=>1]]);
        $this->assertSame(0.0, $routes[0]['fare']);
        $this->assertSame([], $routes[0]['discounted_fares']);
    }

    public function testConfiguredFractionalRatesRoundToCentsAndInactivePoliciesStayHidden(): void
    {
        $routes = enrich_routes_with_discounts([['id'=>301,'terminal_id'=>3,'fare'=>100.03]], [
            ['terminal_id'=>3,'type'=>'custom','label'=>'Custom Discount','discount_percent'=>12.5,'is_active'=>1],
            ['terminal_id'=>3,'type'=>'inactive','label'=>'Inactive','discount_percent'=>50,'is_active'=>0],
            ['terminal_id'=>3,'type'=>'invalid','label'=>'Invalid','discount_percent'=>120,'is_active'=>1],
        ]);
        $this->assertSame(['custom'], array_keys($routes[0]['discounted_fares']));
        $this->assertSame(87.53, $routes[0]['discounted_fares']['custom']['amount']);
        $this->assertSame(12.5, $routes[0]['discounted_fares']['custom']['discount_percent']);
    }

    public function testSharedFareCardsKeepDiscountsInsideEachVehicleRoutesEntry(): void
    {
        $rows = enrich_routes_with_discounts([
            ['id'=>101,'terminal_id'=>1,'vehicle_type'=>'bus','origin'=>'VILLABA','destination'=>'ISABEL'],
            ['id'=>102,'terminal_id'=>1,'vehicle_type'=>'van','origin'=>'VILLABA','destination'=>'ORMOC'],
        ]);
        $oldRole = session()->get('role');
        session()->set('role', 'staff');
        try {
            $html = view('shared/fares', [
                'title'=>'Route Fares', 'announcements'=>[], 'discounts'=>[], 'terminals'=>[],
                'vehicleTypes'=>[
                    ['id'=>1,'slug'=>'bus','name'=>'BUS','color'=>'#0891b2','icon'=>'fa-bus'],
                    ['id'=>2,'slug'=>'van','name'=>'VAN','color'=>'#dc2626','icon'=>'fa-van-shuttle'],
                ],
                'routesByType'=>['bus'=>[$rows[0]], 'van'=>[$rows[1]]],
            ], ['saveData'=>false]);
            $dom = new \DOMDocument();
            @$dom->loadHTML('<?xml encoding="utf-8" ?>' . $html);
            $entries = (new \DOMXPath($dom))->query('//div[contains(concat(" ", normalize-space(@class), " "), " fare-item ")]');
            $this->assertCount(2, $entries);
            $bus = $entries->item(0)->textContent;
            $van = $entries->item(1)->textContent;
            foreach ([$bus,$van] as $entry) {
                $this->assertStringContainsString('PWD Discount', $entry);
                $this->assertStringContainsString('Student Discount', $entry);
                $this->assertStringContainsString('Senior Citizen Discount', $entry);
            }
            $this->assertStringContainsString('₱120.00', $bus);
            $this->assertStringContainsString('₱127.50', $bus);
            $this->assertStringContainsString('₱64.00', $van);
            $this->assertStringContainsString('₱68.00', $van);
        } finally {
            $oldRole === null ? session()->remove('role') : session()->set('role', $oldRole);
            \Config\Services::resetSingle('renderer');
        }
    }

    public function testRepairRestoresPublicDiscountsForEveryVanAndMinibusRouteWithoutChangingStoredQuotes(): void
    {
        require_once APPPATH . 'Database/Migrations/2026-11-21-000000_RestoreSingleTerminalFareDiscounts.php';
        $this->fareDb->query('CREATE TABLE terminals (id INTEGER PRIMARY KEY, name TEXT)');
        $this->fareDb->table('terminals')->insert(['id'=>3,'name'=>'VILLABA']);
        $this->fareDb->table('fares')->emptyTable();
        $this->fareDb->table('fare_discounts')->emptyTable();
        $this->fareDb->table('fare_discounts')->insertBatch([
            ['id'=>1,'terminal_id'=>1,'type'=>'pwd','label'=>'PWD Discount','discount_percent'=>20,'is_active'=>1],
            ['id'=>2,'terminal_id'=>1,'type'=>'senior_citizen','label'=>'Senior Citizen Discount','discount_percent'=>20,'is_active'=>1],
            ['id'=>3,'terminal_id'=>1,'type'=>'student','label'=>'Student Discount','discount_percent'=>15,'is_active'=>1],
            ['id'=>4,'terminal_id'=>3,'type'=>'regular','label'=>'Regular Fare','discount_percent'=>0,'is_active'=>1],
            ['id'=>5,'terminal_id'=>1,'type'=>'afternoon','label'=>'Afternoon Discount','discount_percent'=>50,'is_active'=>1],
        ]);
        $routes = [];
        foreach (['van','minibus'] as $type) {
            foreach (['ISABEL'=>150,'ORMOC'=>80,'PALOMPON'=>100] as $destination=>$fare) {
                $routeId = count($routes)+101;
                $routes[] = ['id'=>$routeId,'terminal_id'=>3,'vehicle_type'=>$type,'origin'=>'VILLABA','destination'=>$destination];
                $this->fareDb->table('fares')->insert(['route_id'=>$routeId,'fare_discount_id'=>4,'amount'=>$fare]);
            }
        }
        foreach ([[103,1,80],[103,2,80],[103,3,85],[106,1,77]] as [$route,$discount,$amount]) {
            $this->fareDb->table('fares')->insert(['route_id'=>$route,'fare_discount_id'=>$discount,'amount'=>$amount]);
        }
        $beforeRepair = enrich_routes_with_discounts($routes);
        $this->assertSame([], $beforeRepair[0]['discounted_fares']);
        $this->assertSame([], $beforeRepair[1]['discounted_fares']);
        $this->assertCount(3, $beforeRepair[2]['discounted_fares'], 'Reproduce the screenshot: only the last route has stored discount quotes.');
        $stored = $this->fareDb->table('fares')->orderBy('id')->get()->getResultArray();
        (new \App\Database\Migrations\RestoreSingleTerminalFareDiscounts(Database::forge($this->fareDb)))->up();
        $repaired = enrich_routes_with_discounts($routes);
        foreach ($repaired as $index=>$route) {
            $regular = [150.0,80.0,100.0][$index%3];
            $this->assertSame($regular, $route['fare']);
            $this->assertCount(4, $route['discounted_fares']);
            $this->assertSame($index===5 ? 77.0 : $regular*.8, $route['discounted_fares']['pwd']['amount']);
            $this->assertSame(round($regular*.85,2), $route['discounted_fares']['student']['amount']);
            $this->assertSame($regular*.8, $route['discounted_fares']['senior_citizen']['amount']);
            $this->assertSame($regular*.5, $route['discounted_fares']['afternoon']['amount']);
        }
        $this->assertSame($stored, $this->fareDb->table('fares')->orderBy('id')->get()->getResultArray());

        $oldRole = session()->get('role');
        $oldLoggedIn = session()->get('isLoggedIn');
        session()->remove(['role','isLoggedIn']);
        try {
            $html = view('public/fares', [
                'title'=>'Route Fares','announcements'=>[],
                'vehicleTypes'=>[
                    ['id'=>1,'slug'=>'van','name'=>'VAN','color'=>'#c62828','icon'=>'fa-van-shuttle'],
                    ['id'=>2,'slug'=>'minibus','name'=>'MINIBUS','color'=>'#2e7d32','icon'=>'fa-bus'],
                ],
                'van_routes'=>array_slice($repaired,0,3),'minibus_routes'=>array_slice($repaired,3,3),'jeepney_routes'=>[],
                'routesByType'=>['van'=>array_slice($repaired,0,3),'minibus'=>array_slice($repaired,3,3)],
                'discounts'=>$this->fareDb->table('fare_discounts')->where('type !=','regular')->get()->getResultArray(),
            ], ['saveData'=>false]);
            $dom = new \DOMDocument();
            @$dom->loadHTML('<?xml encoding="utf-8" ?>' . $html);
            $xpath = new \DOMXPath($dom);
            foreach (['van','minibus'] as $type) {
                $entries = $xpath->query('//div[@id="fare-card-'.$type.'"]//div[contains(concat(" ",normalize-space(@class)," ")," fare-item ")]');
                $this->assertCount(3, $entries);
                foreach (['ISABEL','ORMOC','PALOMPON'] as $index=>$destination) {
                    $text = $entries->item($index)->textContent;
                    foreach ([$destination,'PWD Discount','Senior Citizen Discount','Student Discount','Afternoon Discount'] as $label) {
                        $this->assertStringContainsString($label, $text, $type.' '.$destination);
                    }
                    $studentAmount = ['₱127.50','₱68.00','₱85.00'][$index];
                    $this->assertStringContainsString($studentAmount, $text);
                    if ($type==='minibus' && $index===2) $this->assertStringContainsString('₱77.00', $text);
                }
            }
        } finally {
            $oldRole === null ? session()->remove('role') : session()->set('role', $oldRole);
            $oldLoggedIn === null ? session()->remove('isLoggedIn') : session()->set('isLoggedIn', $oldLoggedIn);
            \Config\Services::resetSingle('renderer');
        }
    }
}
