<?php

namespace Tests\Unit;

use App\Controllers\Contact;
use App\Filters\SecurityHeaders;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\ControllerTestTrait;
use Config\Database;

class GuestContactHarness extends Contact
{
    public static ?string $recipient = 'management@example.com';
    public static array $mail = [];
    protected function supportRecipient(): ?string { return self::$recipient; }
    protected function sendConfiguredHtmlEmail(string|array $to, string $subject, string $html, ?string $replyToEmail = null, ?string $replyToName = null): bool
    {
        self::$mail[] = compact('to', 'subject', 'html', 'replyToEmail', 'replyToName');
        return true;
    }
}

final class GuestContactDraftTest extends CIUnitTestCase
{
    use ControllerTestTrait;
    private array $connections;
    private $contactDb;

    protected function setUp(): void
    {
        parent::setUp();
        GuestContactHarness::$recipient = 'management@example.com';
        GuestContactHarness::$mail = [];
        session()->remove(['guest_contact_pending', 'isLoggedIn', 'role', '_ci_old_input', 'contact_success', 'contact_error', 'contact_verify_error', 'contact_verify_notice', 'contact_gmail_url', 'contact_email_app_url']);
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
        $this->assertSame([], GuestContactHarness::$mail, 'Guest contact actions must never send system email.');
        service('renderer')->resetData();
        session()->remove(['guest_contact_pending', 'contact_gmail_url', 'contact_email_app_url', '_ci_old_input']);
        (new \ReflectionProperty(\CodeIgniter\Database\Config::class, 'instances'))->setValue(null, $this->connections);
        $this->contactDb->close();
        parent::tearDown();
    }

    private function draft(array $overrides=[]): array
    {
        return array_replace(['type'=>'contact', 'name'=>'Guest Passenger', 'subject'=>'Trip inquiry', 'message'=>'Please check my trip.'], $overrides);
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

    public function testBothFormsPrepareEncodedDraftsWithoutSelectingASenderOrDeliveringEmail(): void
    {
        foreach (['contact'=>'Contact Us', 'report'=>'Report Issue'] as $type=>$label) {
            $draft = $this->draft(['type'=>$type, 'name'=>'José & Guest', 'subject'=>'Fare & +=?#',
                'message'=>"<script>Stay as text</script>\nCafé 🚐 & +=?#"]);
            $response = $this->action('send', $draft + ['recipient'=>'attacker@example.com', 'email'=>'system@example.com', 'from'=>'system@example.com'], true);
            $data = json_decode($response->getBody(), true);
            $this->assertTrue($data['success']);
            $this->assertSame('draft', $data['stage']);
            $this->assertSame($draft, $data['draft']);
            $url = parse_url($data['gmailUrl']);
            $this->assertSame('https', $url['scheme']);
            $this->assertSame('mail.google.com', $url['host']);
            $this->assertSame('/mail/', $url['path']);
            parse_str($url['query'], $params);
            $this->assertSame('management@example.com', $params['to']);
            $this->assertSame('cm', $params['view']);
            $this->assertSame('[' . app_acronym() . '] ' . $label . ': Fare & +=?#', $params['su']);
            $this->assertSame('Name: José & Guest' . "\n\n" . $draft['message'], $params['body']);
            $appUrl = parse_url($data['emailAppUrl']);
            $this->assertSame('mailto', $appUrl['scheme']);
            $this->assertSame('management@example.com', rawurldecode($appUrl['path']));
            parse_str($appUrl['query'], $appParams);
            $this->assertSame($params['su'], $appParams['subject']);
            $this->assertSame(str_replace("\n", "\r\n", $params['body']), $appParams['body']);
            $this->assertArrayNotHasKey('authuser', $params);
            $this->assertArrayNotHasKey('from', $params);
            $this->assertArrayNotHasKey('email', $params);
            $this->assertNull(session()->get('guest_contact_pending'));
            $this->assertStringContainsString('no-store', $response->getHeaderLine('Cache-Control'));
            $this->assertSame(csrf_token(), $data['csrf']['name']);
        }
    }

    public function testInvalidInputCannotPrepareADraft(): void
    {
        foreach ([['type'=>'admin'], ['type'=>[]], ['name'=>[]], ['subject'=>[]], ['name'=>str_repeat('é',121)],
            ['subject'=>str_repeat('x',181)], ['subject'=>"Injected\nsubject"], ['name'=>"Guest\rInjected"],
            ['message'=>str_repeat('x',5001)], ['message'=>[]]] as $input) {
            $data = json_decode($this->action('send', $this->draft($input), true)->getBody(), true);
            $this->assertFalse($data['success']);
            $this->assertNull($data['gmailUrl']);
            $this->assertNull($data['emailAppUrl']);
            $this->assertSame('form', $data['stage']);
        }
    }

    public function testMissingRecipientRetainsTheDraftWithAnError(): void
    {
        GuestContactHarness::$recipient = null;
        $draft = $this->draft();
        $data = json_decode($this->action('send', $draft, true)->getBody(), true);
        $this->assertFalse($data['success']);
        $this->assertSame($draft, $data['draft']);
        $this->assertStringContainsString('currently unavailable', $data['message']);
        $this->assertNull($data['gmailUrl']);
        $this->assertNull($data['emailAppUrl']);
    }

    public function testNoJavascriptSubmissionShowsAnEscapedDraftLinkAndKeepsTheForm(): void
    {
        $response = $this->action('send', $this->draft(['message'=>'<script>alert("unsafe")</script>']));
        $this->assertStringEndsWith('/guest?contact=1#support-section', $response->getHeaderLine('Location'));
        $this->assertStringContainsString('no-store', $response->getHeaderLine('Cache-Control'));
        $form = view('partials/guest-contact-form', ['type'=>'contact']);
        $this->assertStringContainsString('https://mail.google.com/mail/?', html_entity_decode($form, ENT_QUOTES));
        $this->assertStringContainsString('&lt;script&gt;', $form);
        $this->assertStringNotContainsString('<script>alert', $form);
        $this->assertStringContainsString('Open email app', $form);
        $this->assertStringContainsString('Use Gmail in browser', $form);
        $this->assertStringContainsString('ready to review', $form);
        $this->assertStringContainsString('mailto:management@example.com?', html_entity_decode($form, ENT_QUOTES));
        $this->assertStringNotContainsString('name="email"', $form);
        $this->assertStringNotContainsString('data-contact-resume', $form);
        $this->assertStringContainsString('data-permanent="true"', $form);
        $document = new \DOMDocument();
        $document->loadHTML($form, LIBXML_NOERROR | LIBXML_NOWARNING);
        $xpath = new \DOMXPath($document);
        $this->assertSame(1, $xpath->query('//*[@data-contact-draft-notice and not(@hidden)]')->length);
        $request = service('request', config('App'), false);
        $request->getUri()->setPath(parse_url(base_url('guest'), PHP_URL_PATH));
        $guestResponse = service('response', null, false);
        (new SecurityHeaders())->after($request, $guestResponse);
        $this->assertStringContainsString('no-store', $guestResponse->getHeaderLine('Cache-Control'));
    }

    public function testOldVerificationRoutesPreserveTheMessageWithoutDeliveringIt(): void
    {
        foreach (['verification', 'verify', 'resend', 'edit'] as $method) {
            $draft = $this->draft(['type'=>'report']);
            session()->set('guest_contact_pending', $draft + ['email'=>'old@example.com', 'id'=>'old', 'verified'=>true, 'code_hash'=>'private']);
            $data = json_decode($this->action($method, ['draft_id'=>'old', 'code'=>'123456'], true)->getBody(), true);
            $this->assertSame('form', $data['stage']);
            $this->assertSame($draft, $data['draft']);
            $this->assertArrayNotHasKey('code_hash', $data['draft']);
            $this->assertArrayNotHasKey('email', $data['draft']);
            $this->assertStringContainsString('own account', $data['message']);
            $this->assertNull(session()->get('guest_contact_pending'));
        }
    }

    public function testOptionalSubjectAndMaximumUnicodeMessageArePreserved(): void
    {
        $message = str_repeat('é', 5000);
        $data = json_decode($this->action('send', $this->draft(['subject'=>'', 'message'=>$message]), true)->getBody(), true);
        $this->assertTrue($data['success']);
        parse_str(parse_url($data['gmailUrl'], PHP_URL_QUERY), $params);
        $this->assertSame('[' . app_acronym() . '] Contact Us', $params['su']);
        $this->assertStringEndsWith($message, $params['body']);
        $this->assertStringNotContainsString('Subject:', $params['body']);
    }

    public function testBothFormsAcceptOptionalNameAndMessageWithoutEmptyBodyLabels(): void
    {
        foreach (['report'=>'Report Issue', 'contact'=>'Contact Us'] as $type=>$label) {
            foreach ([[], ['name'=>'', 'message'=>''], ['name'=>'   ', 'message'=>" \n "]] as $optional) {
                $post = ['type'=>$type, 'subject'=>'Topic only'] + $optional;
                $data = json_decode($this->action('send', $post, true)->getBody(), true);
                $this->assertTrue($data['success']);
                $this->assertSame('', $data['draft']['name']);
                $this->assertSame('', $data['draft']['message']);
                parse_str(parse_url($data['gmailUrl'], PHP_URL_QUERY), $params);
                $this->assertSame('', $params['body']);
            }
            $response = $this->action('send', ['type'=>$type, 'subject'=>'Topic only']);
            $this->assertStringEndsWith('/guest?' . $type . '=1#support-section', $response->getHeaderLine('Location'));
            $form = view('partials/guest-contact-form', ['type'=>$type]);
            $this->assertStringContainsString('Your Name (optional)', $form);
            $this->assertStringContainsString('<option selected>Topic only</option>', $form);
            $this->assertStringContainsString('https://mail.google.com/mail/?', html_entity_decode($form, ENT_QUOTES));
        }
    }

    public function testDraftBodyContainsOnlyTheOptionalNameAndMessage(): void
    {
        foreach (['report', 'contact'] as $type) {
            foreach ([
                ['name'=>'Guest', 'message'=>'', 'body'=>'Name: Guest'],
                ['name'=>'', 'message'=>'Please check the trip.', 'body'=>'Please check the trip.'],
                ['name'=>' Guest ', 'message'=>' Please check the trip. ', 'body'=>"Name: Guest\n\nPlease check the trip."],
            ] as $case) {
                $data = json_decode($this->action('send', [
                    'type'=>$type, 'subject'=>'Trip inquiry', 'name'=>$case['name'], 'message'=>$case['message'],
                ], true)->getBody(), true);
                $this->assertTrue($data['success']);
                parse_str(parse_url($data['gmailUrl'], PHP_URL_QUERY), $params);
                $this->assertSame($case['body'], $params['body']);
            }
        }
    }

    public function testRecipientHelperUsesConfiguredManagementEmailBeforeSenderFallback(): void
    {
        $config = config('Email');
        $saved = [$config->recipients, $config->fromEmail, $config->SMTPUser];
        try {
            $config->recipients = 'management@example.com';
            $config->fromEmail = 'system@example.com';
            $config->SMTPUser = 'smtp@example.com';
            $this->assertSame('management@example.com', app_contact_recipient());
            $config->recipients = 'invalid';
            $this->assertSame('system@example.com', app_contact_recipient());
            $config->fromEmail = '';
            $this->assertSame('smtp@example.com', app_contact_recipient());
            $config->SMTPUser = '';
            $this->assertNull(app_contact_recipient());
        } finally {
            [$config->recipients, $config->fromEmail, $config->SMTPUser] = $saved;
        }
    }
}
