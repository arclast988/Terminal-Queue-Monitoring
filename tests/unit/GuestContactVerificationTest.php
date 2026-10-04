<?php

namespace Tests\Unit;

use App\Controllers\Contact;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\ControllerTestTrait;
use Config\Database;

class GuestContactHarness extends Contact
{
    public static int $clock = 1700000000;
    public static array $mail = [];
    public static array $outcomes = [];
    protected function now(): int { return self::$clock; }
    protected function supportRecipient(): ?string { return 'management@example.com'; }
    protected function sendConfiguredHtmlEmail(string|array $to, string $subject, string $html, ?string $replyToEmail = null, ?string $replyToName = null): bool
    {
        self::$mail[] = compact('to', 'subject', 'html', 'replyToEmail', 'replyToName');
        return array_shift(self::$outcomes) ?? true;
    }
}

final class GuestContactVerificationTest extends CIUnitTestCase
{
    use ControllerTestTrait;
    private array $connections;
    private $contactDb;

    protected function setUp(): void
    {
        parent::setUp();
        GuestContactHarness::$clock = 1700000000;
        GuestContactHarness::$mail = GuestContactHarness::$outcomes = [];
        session()->remove(['guest_contact_pending', 'isLoggedIn', 'role', '_ci_old_input', 'contact_success', 'contact_error', 'contact_verify_error', 'contact_verify_notice']);
        service('renderer')->resetData();
        $this->contactDb = Database::connect(['DBDriver'=>'SQLite3', 'database'=>':memory:', 'DBPrefix'=>'', 'DBDebug'=>true], false);
        $property = new \ReflectionProperty(\CodeIgniter\Database\Config::class, 'instances');
        $this->connections = $property->getValue();
        $property->setValue(null, array_replace($this->connections, ['default'=>$this->contactDb, 'tests'=>$this->contactDb]));
        $this->contactDb->query('CREATE TABLE system_settings (id INTEGER PRIMARY KEY, setting_key TEXT, setting_value TEXT)');
        get_all_system_settings(true);
    }

    protected function tearDown(): void
    {
        service('renderer')->resetData();
        session()->remove('guest_contact_pending');
        (new \ReflectionProperty(\CodeIgniter\Database\Config::class, 'instances'))->setValue(null, $this->connections);
        $this->contactDb->close();
        parent::tearDown();
    }

    private function draft(array $overrides=[]): array
    {
        return array_replace(['type'=>'contact', 'name'=>'Guest Passenger', 'email'=>'guest@example.com', 'subject'=>'Trip inquiry', 'message'=>'Please check my trip.'], $overrides);
    }

    private function action(string $method, array $post=[], bool $json=false)
    {
        service('renderer')->resetData();
        $this->response = service('response', null, false);
        $this->withUri('http://localhost/contact/'.($method === 'verification' ? 'verify' : $method));
        $this->request->setMethod($method === 'verification' ? 'GET' : 'POST');
        $this->request->setGlobal('post', $post);
        $this->request->setGlobal('server', ['REMOTE_ADDR'=>'192.0.2.25']);
        if ($json) $this->request->setHeader('Accept', 'application/json');
        return $this->controller(GuestContactHarness::class)->execute($method)->response();
    }

    private function start(array $overrides=[]): array
    {
        $response = $this->action('send', $this->draft($overrides));
        $this->assertStringEndsWith('/guest?contact_verify=1', $response->getHeaderLine('Location'));
        return session()->get('guest_contact_pending');
    }

    private function code(int $index=0): string
    {
        $this->assertSame(1, preg_match('/>([0-9]{6})<\/p>/', GuestContactHarness::$mail[$index]['html'], $match));
        return $match[1];
    }

    public function testBothFormsDeliverOnlyAfterTheSavedAddressIsVerifiedAndRejectReplay(): void
    {
        foreach (['contact', 'report'] as $type) {
            cache()->clean();
            GuestContactHarness::$mail = [];
            $pending = $this->start(['type'=>$type]);
            $this->assertCount(1, GuestContactHarness::$mail);
            $this->assertSame('guest@example.com', GuestContactHarness::$mail[0]['to']);
            $this->assertStringNotContainsString('Please check my trip.', GuestContactHarness::$mail[0]['html']);
            $code = $this->code();
            $this->assertNotSame($code, $pending['code_hash']);
            $this->assertTrue(password_verify($code, $pending['code_hash']));
            $this->assertSame(GuestContactHarness::$clock + 600, $pending['expires_at']);
            $response = $this->action('verify', ['draft_id'=>$pending['id'], 'code'=>$code, 'email'=>'replacement@example.com', 'message'=>'Tampered message', 'recipient'=>'attacker@example.com']);
            $this->assertStringContainsString('?'.$type.'=1', $response->getHeaderLine('Location'));
            $this->assertCount(2, GuestContactHarness::$mail);
            $mail = GuestContactHarness::$mail[1];
            $this->assertSame('management@example.com', $mail['to']);
            $this->assertSame('guest@example.com', $mail['replyToEmail']);
            $this->assertStringContainsString('Email verified: Yes', $mail['html']);
            $this->assertStringContainsString('Please check my trip.', $mail['html']);
            $this->assertStringNotContainsString('Tampered', $mail['html']);
            $this->assertNull(session()->get('guest_contact_pending'));
            $this->action('verify', ['draft_id'=>$pending['id'], 'code'=>$code]);
            $this->assertCount(2, GuestContactHarness::$mail);
        }
    }

    public function testWrongOrStaleDraftAndUnverifiedSubmissionCannotForwardMessages(): void
    {
        $this->action('verify', ['code'=>'123456', 'email'=>'guest@example.com']);
        $this->assertCount(0, GuestContactHarness::$mail);
        $pending = $this->start();
        foreach ([[], ['draft_id'=>'old-draft', 'code'=>$this->code()], ['draft_id'=>[$pending['id']], 'code'=>$this->code()], ['draft_id'=>$pending['id'], 'code'=>['123456']], ['draft_id'=>$pending['id'], 'code'=>'123']] as $post) {
            $this->action('verify', $post);
            $this->assertCount(1, GuestContactHarness::$mail);
            $this->assertFalse(session()->get('guest_contact_pending')['verified']);
        }
        session()->remove('guest_contact_pending');
        $this->action('verify', ['draft_id'=>$pending['id'], 'code'=>$this->code()]);
        $this->assertCount(1, GuestContactHarness::$mail);
    }

    public function testExpiryBlocksVerificationResendingAndRestoresTheReportDraft(): void
    {
        $pending = $this->start(['type'=>'report']);
        GuestContactHarness::$clock += 600;
        $response = $this->action('verify', ['draft_id'=>$pending['id'], 'code'=>$this->code()]);
        $this->assertStringContainsString('?report=1', $response->getHeaderLine('Location'));
        $this->assertSame($this->draft(['type'=>'report']), session()->getFlashdata('_ci_old_input')['post']);
        $this->action('resend', ['draft_id'=>$pending['id']]);
        $this->assertCount(1, GuestContactHarness::$mail);
        $this->assertNull(session()->get('guest_contact_pending'));
    }

    public function testFiveWrongGuessesInvalidateTheChallengeAndResendingDoesNotResetAttempts(): void
    {
        $pending = $this->start();
        $wrong = $this->code() === '000000' ? '000001' : '000000';
        $this->action('verify', ['draft_id'=>$pending['id'], 'code'=>$wrong]);
        GuestContactHarness::$clock += 60;
        $this->action('resend', ['draft_id'=>$pending['id']]);
        $pending = session()->get('guest_contact_pending');
        $this->assertSame(1, $pending['attempts']);
        $this->assertSame(GuestContactHarness::$clock + 600, $pending['expires_at']);
        $wrong = $this->code(1) === '000000' ? '000001' : '000000';
        for ($i=0; $i<4; $i++) $this->action('verify', ['draft_id'=>$pending['id'], 'code'=>$wrong]);
        $this->assertNull(session()->get('guest_contact_pending'));
        $this->assertStringContainsString('Too many incorrect', session()->getFlashdata('contact_error'));
        $this->action('verify', ['draft_id'=>$pending['id'], 'code'=>$this->code(1)]);
        $this->assertCount(2, GuestContactHarness::$mail);
    }

    public function testResendCooldownAndRequestLimitsApplyAcrossNewDraftsAndEmailCasing(): void
    {
        $pending = $this->start();
        $this->action('resend', ['draft_id'=>$pending['id']]);
        session()->remove('guest_contact_pending');
        $this->action('send', $this->draft(['email'=>'GUEST@example.com']));
        $this->assertCount(1, GuestContactHarness::$mail);
        for ($i=0; $i<2; $i++) {
            GuestContactHarness::$clock += 60;
            $this->start();
        }
        GuestContactHarness::$clock += 60;
        $this->action('send', $this->draft(['email'=>'different@example.com']));
        $this->assertCount(3, GuestContactHarness::$mail);
        $this->assertStringContainsString('Too many verification', session()->getFlashdata('contact_error'));
        GuestContactHarness::$clock += 121;
        $this->start();
        $this->assertCount(4, GuestContactHarness::$mail);
    }

    public function testOnlyTheNewestSuccessfullyDeliveredCodeIsUsable(): void
    {
        $pending = $this->start();
        $oldHash = $pending['code_hash'];
        GuestContactHarness::$clock += 60;
        $this->action('resend', ['draft_id'=>$pending['id']]);
        $updated = session()->get('guest_contact_pending');
        $this->assertNotSame($oldHash, $updated['code_hash']);
        $this->assertTrue(password_verify($this->code(1), $updated['code_hash']));
        // Avoid a probabilistic assertion if the random generator repeats a code.
        if ($this->code() !== $this->code(1)) {
            $this->action('verify', ['draft_id'=>$pending['id'], 'code'=>$this->code()]);
            $this->assertCount(2, GuestContactHarness::$mail);
        }
        $this->action('verify', ['draft_id'=>$pending['id'], 'code'=>$this->code(1)]);
        $this->assertCount(3, GuestContactHarness::$mail);
    }

    public function testFailedEmailDeliveryCanBeRetriedWithoutForwardingUnverifiedContent(): void
    {
        GuestContactHarness::$outcomes = [false];
        $this->action('send', $this->draft());
        $this->assertNull(session()->get('guest_contact_pending'));
        GuestContactHarness::$clock += 60;
        $pending = $this->start();
        GuestContactHarness::$clock += 60;
        GuestContactHarness::$outcomes = [false];
        $this->action('resend', ['draft_id'=>$pending['id']]);
        $this->assertSame($pending['code_hash'], session()->get('guest_contact_pending')['code_hash']);
        GuestContactHarness::$outcomes = [false];
        $this->action('verify', ['draft_id'=>$pending['id'], 'code'=>$this->code(1)]);
        $verified = session()->get('guest_contact_pending');
        $this->assertTrue($verified['verified']);
        $this->assertNull($verified['code_hash']);
        $this->action('resend', ['draft_id'=>$pending['id']]);
        $this->assertCount(4, GuestContactHarness::$mail);
        $this->action('verify', ['draft_id'=>$pending['id'], 'message'=>'Forged retry']);
        $this->assertCount(5, GuestContactHarness::$mail);
        $this->assertStringNotContainsString('Forged retry', GuestContactHarness::$mail[4]['html']);
        $this->assertNull(session()->get('guest_contact_pending'));
    }

    public function testEditingPreservesTheDraftButInvalidatesItsPreviousVerification(): void
    {
        $pending = $this->start(['message'=>'<script>alert("unsafe")</script>']);
        $response = $this->action('edit', ['draft_id'=>$pending['id']]);
        $this->assertStringContainsString('?contact=1', $response->getHeaderLine('Location'));
        $this->assertNull(session()->get('guest_contact_pending'));
        $form = view('partials/guest-contact-form', ['type'=>'contact']);
        $this->assertStringContainsString('&lt;script&gt;', $form);
        $this->assertStringNotContainsString('<script>alert', $form);
        $this->assertStringContainsString('guest@example.com', html_entity_decode($form, ENT_QUOTES));
        $this->assertStringContainsString('Continue to email verification', $form);
        $this->action('verify', ['draft_id'=>$pending['id'], 'code'=>$this->code()]);
        $this->assertCount(1, GuestContactHarness::$mail);
    }

    public function testInvalidInputNeverSendsEmail(): void
    {
        foreach ([['type'=>'admin'], ['email'=>'invalid'], ['email'=>['guest@example.com']], ['name'=>str_repeat('a',121)], ['subject'=>"Injected\nsubject"], ['message'=>str_repeat('a',5001)], ['message'=>'']] as $input) {
            $this->action('send', $this->draft($input));
            $this->assertNull(session()->get('guest_contact_pending'));
        }
        $this->assertCount(0, GuestContactHarness::$mail);
    }

    public function testVerificationModalEscapesTheSavedMessageAndDoesNotExposeTheCodeHash(): void
    {
        $pending = $this->start(['message'=>'<img src=x onerror=alert(1)>']);
        $response = $this->action('verification', [], true);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertStringContainsString('no-store', $response->getHeaderLine('Cache-Control'));
        $body = json_decode($response->getBody(), true)['html'];
        $this->assertStringContainsString('&lt;img src=x onerror=alert(1)&gt;', $body);
        $this->assertStringNotContainsString($pending['code_hash'], $body);
        $this->assertStringContainsString('guestContactCode', $body);
        GuestContactHarness::$outcomes = [false];
        $this->action('verify', ['draft_id'=>$pending['id'], 'code'=>$this->code()]);
        $body = json_decode($this->action('verification', [], true)->getBody(), true)['html'];
        $this->assertStringNotContainsString('id="guestContactCode"', $body);
        $this->assertStringContainsString('Send message', $body);
    }

    public function testJsonFlowReturnsModalErrorsSavedDraftAndFreshCsrfWithoutRedirects(): void
    {
        cache()->clean();
        $response = $this->action('send', $this->draft(['type'=>'report']), true);
        $data = json_decode($response->getBody(), true);
        $this->assertSame('verify', $data['stage']);
        $this->assertSame('report', $data['type']);
        $this->assertSame(csrf_token(), $data['csrf']['name']);
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $data['csrf']['hash']);
        $this->assertSame('', $response->getHeaderLine('Location'));
        $pending = session()->get('guest_contact_pending');
        $data = json_decode($this->action('verify', ['draft_id'=>$pending['id'], 'code'=>'123'], true)->getBody(), true);
        $this->assertFalse($data['success']);
        $this->assertStringContainsString('complete six-digit', $data['html']);
        $data = json_decode($this->action('edit', ['draft_id'=>$pending['id']], true)->getBody(), true);
        $this->assertSame('form', $data['stage']);
        $this->assertSame($this->draft(['type'=>'report']), $data['draft']);
        $this->assertArrayNotHasKey('code_hash', $data['draft']);
        $this->assertNull(session()->get('guest_contact_pending'));
    }

    public function testLegacyVerificationLinkReturnsToTheGuestModal(): void
    {
        cache()->clean();
        $this->start();
        $response = $this->action('verification');
        $this->assertStringEndsWith('/guest?contact_verify=1', $response->getHeaderLine('Location'));
    }

    public function testVerifiedDeliveryRetriesAreBoundedEvenWhenTransportFails(): void
    {
        cache()->clean();
        $pending = $this->start();
        GuestContactHarness::$outcomes = [false,false,false];
        for ($attempt=0; $attempt<4; $attempt++) {
            $this->action('verify', ['draft_id'=>$pending['id'], 'code'=>$this->code()], true);
        }
        $this->assertCount(4, GuestContactHarness::$mail);
        $this->assertTrue(session()->get('guest_contact_pending')['verified']);
        GuestContactHarness::$clock += 301;
        $data = json_decode($this->action('verify', ['draft_id'=>$pending['id']], true)->getBody(), true);
        $this->assertSame('complete', $data['stage']);
        $this->assertNull(session()->get('guest_contact_pending'));
    }
}
