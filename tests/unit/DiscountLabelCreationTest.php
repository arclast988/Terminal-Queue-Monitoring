<?php

namespace Tests\Unit;

use App\Controllers\Admin\Routes;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\ControllerTestTrait;
use Config\Database;

class DiscountLabelCreationHarness extends Routes
{
    protected function logActivity(string $action, string $details): void {}
    protected function broadcastUpdate(string $type, array $data = []): void {}
}

final class DiscountLabelCreationTest extends CIUnitTestCase
{
    use ControllerTestTrait;

    private $discountDb;
    private array $connections;

    protected function setUp(): void
    {
        parent::setUp();
        $this->discountDb = Database::connect(['DBDriver'=>'SQLite3','database'=>':memory:','DBPrefix'=>'','DBDebug'=>true],false);
        $instances = new \ReflectionProperty(\CodeIgniter\Database\Config::class,'instances');
        $this->connections = $instances->getValue();
        $instances->setValue(null,array_replace($this->connections,['default'=>$this->discountDb,'tests'=>$this->discountDb]));
        foreach ([
            'terminals (id INTEGER PRIMARY KEY, name TEXT)',
            'routes (id INTEGER PRIMARY KEY, terminal_id INTEGER)',
            'fare_discounts (id INTEGER PRIMARY KEY AUTOINCREMENT, terminal_id INTEGER, type VARCHAR(50), label TEXT, discount_percent REAL, is_active INTEGER, created_at TEXT, updated_at TEXT, UNIQUE(terminal_id,type))',
            'fares (id INTEGER PRIMARY KEY AUTOINCREMENT, route_id INTEGER, fare_discount_id INTEGER, amount REAL, created_at TEXT, updated_at TEXT)',
        ] as $schema) $this->discountDb->query('CREATE TABLE '.$schema);
        $this->discountDb->table('terminals')->insertBatch([['id'=>1,'name'=>'VILLABA'],['id'=>2,'name'=>'CEBU']]);
        $this->discountDb->table('routes')->insertBatch([['id'=>10,'terminal_id'=>1],['id'=>20,'terminal_id'=>2]]);
        $this->discountDb->table('fare_discounts')->insertBatch([
            ['id'=>1,'terminal_id'=>1,'type'=>'regular','label'=>'Regular Fare','discount_percent'=>0,'is_active'=>1],
            ['id'=>2,'terminal_id'=>2,'type'=>'regular','label'=>'Regular Fare','discount_percent'=>0,'is_active'=>1],
            ['id'=>3,'terminal_id'=>1,'type'=>'pwd','label'=>'PWD Discount','discount_percent'=>20,'is_active'=>0],
        ]);
        $this->discountDb->table('fares')->insertBatch([
            ['route_id'=>10,'fare_discount_id'=>1,'amount'=>150],
            ['route_id'=>20,'fare_discount_id'=>2,'amount'=>50],
        ]);
    }

    protected function tearDown(): void
    {
        session()->remove(['errors','error','success','_ci_old_input']);
        (new \ReflectionProperty(\CodeIgniter\Database\Config::class,'instances'))->setValue(null,$this->connections);
        $this->discountDb->close();
        parent::tearDown();
    }

    private function create(string $label, array $extra = []): void
    {
        session()->remove(['errors','error','success','_ci_old_input']);
        service('validation')->reset();
        $post = $extra + ['form_action'=>'create_discount','terminal_id'=>'2','label'=>$label,'discount_percent'=>'15.5'];
        $this->withUri('http://localhost/admin/routes/discounts/store');
        $this->request->setMethod('POST')->setGlobal('post',$post)->setGlobal('request',$post);
        $response = $this->controller(DiscountLabelCreationHarness::class)->execute('storeDiscount')->response();
        $this->assertSame(302,$response->getStatusCode());
        $this->assertStringEndsWith('/fares',$response->getHeaderLine('Location'));
    }

    private function policies(): array
    {
        return $this->discountDb->table('fare_discounts')->orderBy('id')->get()->getResultArray();
    }

    public function testTheVisibleFieldsCreateAPolicyAndFareWithoutSubmittingAnInternalKey(): void
    {
        $before = $this->policies();
        $this->create('Military Discount');
        $this->assertNotNull(session()->getFlashdata('success'));
        $policies = $this->policies();
        $this->assertSame($before,array_slice($policies,0,3));
        $created = $policies[3];
        $this->assertSame('military_discount',$created['type']);
        $this->assertSame('Military Discount',$created['label']);
        $this->assertSame(2,(int)$created['terminal_id']);
        $this->assertSame(15.5,(float)$created['discount_percent']);
        $this->assertSame(42.25,(float)$this->discountDb->table('fares')->where('fare_discount_id',$created['id'])->get()->getRowArray()['amount']);
        $this->assertSame(1,$this->discountDb->table('fares')->where('route_id',10)->countAllResults());
    }

    public function testGeneratedKeysHandlePunctuationLongLabelsAndNonLatinLabels(): void
    {
        $labels = ['Military Discount','Military-Discount',str_repeat('Long Label ',8).'One',str_repeat('Long Label ',8).'Two','学生割引','高齢者割引','Regular'];
        foreach ($labels as $label) {
            $this->create($label,['type'=>'regular']);
            $this->assertNotNull(session()->getFlashdata('success'),$label);
        }
        $created = array_slice($this->policies(),3);
        $this->assertSame('military_discount',$created[0]['type']);
        $this->assertSame('military_discount_2',$created[1]['type']);
        $this->assertSame('discount',$created[4]['type']);
        $this->assertSame('discount_2',$created[5]['type']);
        $this->assertSame('regular_discount',$created[6]['type']);
        $keys = array_column($created,'type');
        $this->assertSame($keys,array_values(array_unique($keys)));
        foreach ($keys as $key) {
            $this->assertLessThanOrEqual(50,strlen($key));
            $this->assertMatchesRegularExpression('/^[a-z0-9_]+$/',$key);
        }
    }

    public function testDuplicateNamesReferToTheVisibleLabelAndPreserveExistingInactivePolicies(): void
    {
        $before = $this->policies();
        $this->create('  pwd   discount  ',['terminal_id'=>'1']);
        $error = session()->getFlashdata('error');
        $this->assertStringContainsString('already exists for this terminal',$error);
        $this->assertStringNotContainsString('type',$error);
        $this->assertSame($before,$this->policies());
        $old = session()->get('_ci_old_input')['post'];
        $this->assertSame('  pwd   discount  ',$old['label']);
        $this->assertSame('15.5',$old['discount_percent']);
        $this->create('PWD Discount');
        $this->assertNotNull(session()->getFlashdata('success'),'Other terminals can use the same discount name.');
    }

    public function testInvalidVisibleFieldsHaveActionableMessagesAndDoNotWritePolicies(): void
    {
        $before = $this->policies();
        foreach ([['label'=>'  '],['terminal_id'=>''],['discount_percent'=>'101'],['discount_percent'=>'-1']] as $invalid) {
            $this->create('Valid Discount',$invalid);
            $errors = session()->getFlashdata('errors');
            $this->assertNotEmpty($errors);
            $this->assertArrayNotHasKey('type',$errors);
            $this->assertSame($before,$this->policies());
            $this->assertSame('create_discount',session()->get('_ci_old_input')['post']['form_action']);
        }
    }
}
