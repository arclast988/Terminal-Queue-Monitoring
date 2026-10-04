<?php

namespace Tests\Unit;

use App\Controllers\Auth;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\ControllerTestTrait;
use Config\Database;

class LoginProtectionHarness extends Auth
{
    protected function logActivity(string $action, string $details): void {}
}

final class LoginProtectionTest extends CIUnitTestCase
{
    use ControllerTestTrait;
    private array $connections;
    private $loginDb;

    protected function setUp(): void
    {
        parent::setUp();
        cache()->clean();
        session()->remove(['isLoggedIn','id','role','error']);
        $this->loginDb = Database::connect(['DBDriver'=>'SQLite3','database'=>':memory:','DBPrefix'=>'','DBDebug'=>true], false);
        $property = new \ReflectionProperty(\CodeIgniter\Database\Config::class, 'instances');
        $this->connections = $property->getValue();
        $property->setValue(null, array_replace($this->connections, ['default'=>$this->loginDb,'tests'=>$this->loginDb]));
        $this->loginDb->query('CREATE TABLE users (id INTEGER PRIMARY KEY, username TEXT, email TEXT, password_hash TEXT, status TEXT, role TEXT, full_name TEXT, login_attempts INTEGER DEFAULT 0, locked_until TEXT, created_at TEXT, updated_at TEXT)');
        for ($id=1; $id<=6; $id++) $this->loginDb->table('users')->insert(['id'=>$id,'username'=>'user'.$id,'email'=>'user'.$id.'@example.com','password_hash'=>password_hash('ValidPassword123!', PASSWORD_DEFAULT),'role'=>'staff','full_name'=>'Dispatcher','status'=>'active']);
    }

    protected function tearDown(): void
    {
        session()->remove(['isLoggedIn','id','role','error']);
        (new \ReflectionProperty(\CodeIgniter\Database\Config::class, 'instances'))->setValue(null, $this->connections);
        $this->loginDb->close();
        parent::tearDown();
    }

    private function login(mixed $username, mixed $password='wrong', string $ip='192.0.2.10')
    {
        $this->response = service('response', null, false);
        $this->withUri('http://localhost/login');
        $this->request->setMethod('POST');
        $this->request->setGlobal('post', compact('username','password'));
        $this->request->setGlobal('server', ['REMOTE_ADDR'=>$ip]);
        return $this->controller(LoginProtectionHarness::class)->execute('login')->response();
    }

    public function testKnownAndUnknownAccountsShareTheIpLimitBeforePasswordValidation(): void
    {
        foreach (['user1','missing','user2','user3','user4'] as $username) $this->login($username);
        $this->login('user5', 'ValidPassword123!');
        $this->assertStringContainsString('Too many failed attempts', session()->getFlashdata('error'));
        $this->assertFalse((bool) session()->get('isLoggedIn'));
        $this->assertSame(0, (int) $this->loginDb->table('users')->where('id',5)->get()->getRowArray()['login_attempts']);
        $response = $this->login('user5', 'ValidPassword123!', '192.0.2.11');
        $this->assertTrue((bool) session()->get('isLoggedIn'));
        $this->assertStringEndsWith('/staff/dashboard', $response->getHeaderLine('Location'));
    }

    public function testAccountLockAppliesAcrossDifferentIpsAndExpires(): void
    {
        for ($i=1; $i<=5; $i++) $this->login('user1', 'wrong', '192.0.2.'.$i);
        $user = $this->loginDb->table('users')->where('id',1)->get()->getRowArray();
        $this->assertSame(5, (int) $user['login_attempts']);
        $this->assertGreaterThan(time(), strtotime($user['locked_until']));
        $this->login('user1', 'ValidPassword123!', '192.0.2.20');
        $this->assertStringContainsString('Account locked', session()->getFlashdata('error'));
        $this->assertFalse((bool) session()->get('isLoggedIn'));
        $this->loginDb->table('users')->where('id',1)->update(['locked_until'=>date('Y-m-d H:i:s',time()-1)]);
        $this->login('user1', 'ValidPassword123!', '192.0.2.21');
        $this->assertTrue((bool) session()->get('isLoggedIn'));
        $this->assertSame(0, (int) $this->loginDb->table('users')->where('id',1)->get()->getRowArray()['login_attempts']);
    }

    public function testSuccessfulLoginDoesNotEraseOtherFailuresFromTheIpWindow(): void
    {
        $this->login('user1');
        $this->login('user2', 'ValidPassword123!');
        session()->remove('isLoggedIn');
        for ($i=0; $i<4; $i++) $this->login('missing');
        $this->login('user2', 'ValidPassword123!');
        $this->assertFalse((bool) session()->get('isLoggedIn'));
        $this->assertStringContainsString('Too many failed attempts', session()->getFlashdata('error'));
    }

    public function testExpiredIpLockAndMalformedCredentialsAreHandled(): void
    {
        $key = 'login_ip_' . hash('sha256','192.0.2.10');
        cache()->save($key, ['attempts'=>5,'locked_until'=>time()-1], 300);
        $this->login(['user1'], ['wrong']);
        $this->assertFalse((bool) session()->get('isLoggedIn'));
        $this->assertSame(1, cache()->get($key)['attempts']);
        $this->login('user1', 'ValidPassword123!');
        $this->assertTrue((bool) session()->get('isLoggedIn'));
    }
}
