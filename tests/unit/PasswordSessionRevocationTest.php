<?php

namespace Tests\Unit;

use App\Controllers\Auth;
use App\Filters\AuthFilter;
use App\Models\UserModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\ControllerTestTrait;
use Config\Database;

class PasswordSessionAuth extends Auth
{
    protected function logActivity(string $action, string $details): void {}
}

final class PasswordSessionRevocationTest extends CIUnitTestCase
{
    use ControllerTestTrait;

    private $sessionDb;
    private array $connections;
    private string $originalHash;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sessionDb = Database::connect(['DBDriver'=>'SQLite3', 'database'=>':memory:', 'DBPrefix'=>'', 'DBDebug'=>true], false);
        $property = new \ReflectionProperty(\CodeIgniter\Database\Config::class, 'instances');
        $this->connections = $property->getValue();
        $property->setValue(null, array_replace($this->connections, ['default'=>$this->sessionDb, 'tests'=>$this->sessionDb]));
        $this->sessionDb->query('CREATE TABLE users (id INTEGER PRIMARY KEY, username TEXT, email TEXT, full_name TEXT, role TEXT, status TEXT, password_hash TEXT, profile_image TEXT, login_attempts INTEGER, locked_until TEXT, created_at TEXT, updated_at TEXT)');
        $this->sessionDb->query('CREATE TABLE password_reset_tokens (id INTEGER PRIMARY KEY, username TEXT, token TEXT, reset_code TEXT, code_attempts INTEGER, verified INTEGER, used INTEGER, expires_at TEXT)');
        $this->originalHash = password_hash('OriginalPass123', PASSWORD_DEFAULT);
        foreach ([1, 2] as $id) {
            $this->sessionDb->table('users')->insert(['id'=>$id, 'username'=>'staff'.$id.'@example.com', 'email'=>'staff'.$id.'@example.com', 'full_name'=>'Test Dispatcher', 'role'=>'staff', 'status'=>'active', 'password_hash'=>$this->originalHash]);
        }
        $this->sessionDb->table('password_reset_tokens')->insert(['id'=>1, 'username'=>'staff1@example.com', 'token'=>'reset-token', 'reset_code'=>'123456', 'code_attempts'=>0, 'verified'=>1, 'used'=>0, 'expires_at'=>date('Y-m-d H:i:s', time()+600)]);
        $this->signIn(1, $this->originalHash);
    }

    protected function tearDown(): void
    {
        (new \ReflectionProperty(\CodeIgniter\Database\Config::class, 'instances'))->setValue(null, $this->connections);
        $this->sessionDb->close();
        parent::tearDown();
    }

    private function signIn(int $id, string $hash): void
    {
        session()->set(['isLoggedIn'=>true, 'id'=>$id, 'username'=>'staff'.$id.'@example.com', 'role'=>'staff', 'auth_password_fingerprint'=>hash('sha256', $hash), 'profile_image_synced_at'=>time()]);
    }

    private function changePassword(string $current = 'OriginalPass123')
    {
        $fields = ['current_password'=>$current, 'verification_code'=>'123456', 'new_password'=>'ChangedPass456', 'confirm_password'=>'ChangedPass456'];
        $this->request->setMethod('POST')->setHeader('Content-Type','application/x-www-form-urlencoded')->setGlobal('post', $fields);
        return $this->withBody(http_build_query($fields))->controller(PasswordSessionAuth::class)->execute('updateChangedPassword')->response();
    }

    public function testPasswordChangerStaysSignedInAndOtherSessionsOfTheAccountLoseAccessImmediately(): void
    {
        $this->assertSame(302, $this->changePassword()->getStatusCode());
        $hash = (new UserModel($this->sessionDb))->find(1)['password_hash'];
        $this->assertTrue(password_verify('ChangedPass456', $hash));
        $this->assertSame(hash('sha256', $hash), session()->get('auth_password_fingerprint'));
        $this->assertNull((new AuthFilter())->before(service('request'), ['staff']));

        // Another user/account is unaffected by the first account's password change.
        $this->signIn(2, $this->originalHash);
        $this->assertNull((new AuthFilter())->before(service('request'), ['staff']));

        // Simulate the second browser's unchanged session, even with a recent profile check.
        $this->signIn(1, $this->originalHash);
        $request = service('request')->setHeader('X-Requested-With', 'XMLHttpRequest');
        $response = (new AuthFilter())->before($request, ['staff']);
        $this->assertSame(401, $response->getStatusCode());
        $this->assertTrue(json_decode($response->getBody(), true)['session_expired']);
        $this->assertFalse((bool) session()->get('isLoggedIn'));
    }

    public function testRejectedPasswordChangeDoesNotInvalidateAnySession(): void
    {
        $this->changePassword('WrongPass123');
        $this->assertSame($this->originalHash, (new UserModel($this->sessionDb))->find(1)['password_hash']);
        $this->signIn(1, $this->originalHash);
        $this->assertNull((new AuthFilter())->before(service('request'), ['staff']));
    }

    public function testAdministratorPasswordUpdateAlsoInvalidatesExistingSessions(): void
    {
        (new UserModel($this->sessionDb))->update(1, ['password_hash'=>password_hash('AdminChanged789', PASSWORD_DEFAULT)]);
        $response = (new AuthFilter())->before(service('request'), ['staff']);
        $this->assertStringEndsWith('/login', $response->getHeaderLine('Location'));
        $this->assertFalse((bool) session()->get('isLoggedIn'));
    }

    public function testPasswordResetInvalidatesAllSessionsOfTheResetAccount(): void
    {
        $fields = ['password'=>'ResetPass456', 'confirm_password'=>'ResetPass456'];
        $this->request->setMethod('POST')->setHeader('Content-Type','application/x-www-form-urlencoded')->setGlobal('post', $fields)->setGlobal('request', $fields);
        $this->withBody(http_build_query($fields))->controller(PasswordSessionAuth::class)->execute('updatePassword', 'reset-token');
        $this->signIn(1, $this->originalHash);
        $response = (new AuthFilter())->before(service('request'), ['staff']);
        $this->assertStringEndsWith('/login', $response->getHeaderLine('Location'));
        $this->assertFalse((bool) session()->get('isLoggedIn'));
    }

    public function testLegacySessionMustSignInAgainInsteadOfAdoptingTheNewPassword(): void
    {
        session()->remove('auth_password_fingerprint');
        $response = (new AuthFilter())->before(service('request'), ['staff']);
        $this->assertStringEndsWith('/login', $response->getHeaderLine('Location'));
        $this->assertFalse((bool) session()->get('isLoggedIn'));
    }

    public function testAccountLookupFailureBlocksAccessWithoutDestroyingTheSession(): void
    {
        $filter = new class extends AuthFilter {
            protected function loadAccount(int $userId): ?array { throw new \RuntimeException('Temporary outage'); }
        };
        $this->assertSame(503, $filter->before(service('request'), ['staff'])->getStatusCode());
        $this->assertTrue((bool) session()->get('isLoggedIn'));
    }

    public function testLoginBindsTheSessionToTheCurrentPassword(): void
    {
        session()->remove('auth_password_fingerprint');
        $fields = ['username'=>'staff1@example.com', 'password'=>'OriginalPass123'];
        $this->request->setMethod('POST')->setHeader('Content-Type','application/x-www-form-urlencoded')->setGlobal('post', $fields)->setGlobal('request', $fields);
        $this->withBody(http_build_query($fields))->controller(PasswordSessionAuth::class)->execute('login');
        $this->assertSame(hash('sha256', $this->originalHash), session()->get('auth_password_fingerprint'));
        $this->assertNull((new AuthFilter())->before(service('request'), ['staff']));
    }
}
