<?php

namespace Tests\Unit;

use App\Controllers\BaseController;
use CodeIgniter\Test\CIUnitTestCase;

final class EmailDeliveryConfigurationTest extends CIUnitTestCase
{
    public function testGmailAppPasswordWhitespaceIsRemovedFromFreshService(): void
    {
        $config = config('Email');
        $original = [
            'SMTPHost' => $config->SMTPHost,
            'SMTPUser' => $config->SMTPUser,
            'SMTPPass' => $config->SMTPPass,
            'fromEmail' => $config->fromEmail,
            'fromName' => $config->fromName,
        ];

        try {
            $config->SMTPHost = ' smtp.gmail.com ';
            $config->SMTPUser = ' terminal@example.com ';
            $config->SMTPPass = 'abcd efgh ijkl mnop';
            $config->fromEmail = 'terminal@example.com';
            $config->fromName = 'Terminal Queue';

            $controller = new class extends BaseController {
                public function configuredEmail(): \CodeIgniter\Email\Email
                {
                    return $this->getConfiguredEmailService();
                }
            };

            $email = $controller->configuredEmail();

            $this->assertSame('smtp.gmail.com', $email->SMTPHost);
            $this->assertSame('terminal@example.com', $email->SMTPUser);
            $this->assertSame('abcdefghijklmnop', $email->SMTPPass);
            $this->assertSame(16, strlen($email->SMTPPass));
            $this->assertSame(5, $email->SMTPTimeout);
        } finally {
            foreach ($original as $property => $value) {
                $config->{$property} = $value;
            }
        }
    }

    public function testFailedOtpDeliveryRemovesDeadTokensAndCooldowns(): void
    {
        $auth = file_get_contents(APPPATH . 'Controllers/Auth.php');

        $this->assertGreaterThanOrEqual(2, substr_count($auth, "where('token', \$token)->delete()"));
        $this->assertStringContainsString("\$cache->delete('otp_resend_' . \$token);", $auth);
        $this->assertStringContainsString('$cache->delete($cacheKey);', $auth);
        $this->assertStringContainsString("\$cache->delete('otp_resend_cp_' . \$user['id']);", $auth);
    }

    public function testBrevoProviderUsesHttpsDeliveryWithoutOpeningSmtp(): void
    {
        $config = config('Email');
        $original = [
            'deliveryProvider' => $config->deliveryProvider,
            'brevoApiKey' => $config->brevoApiKey,
            'fromEmail' => $config->fromEmail,
            'fromName' => $config->fromName,
        ];

        try {
            $config->deliveryProvider = 'brevo';
            $config->brevoApiKey = 'test-key-not-sent';
            $config->fromEmail = 'terminal@example.com';
            $config->fromName = 'Terminal Queue';

            $controller = new class extends BaseController {
                public array $delivery = [];

                public function sendTest(): bool
                {
                    return $this->sendConfiguredHtmlEmail(
                        'passenger@example.com',
                        'Verification code',
                        '<strong>123456</strong>'
                    );
                }

                protected function sendViaBrevo(
                    \Config\Email $config,
                    array $recipients,
                    string $subject,
                    string $html,
                    ?string $replyToEmail,
                    ?string $replyToName
                ): bool {
                    $this->delivery = compact('recipients', 'subject', 'html');
                    return true;
                }
            };

            $this->assertTrue($controller->sendTest());
            $this->assertSame(['passenger@example.com'], $controller->delivery['recipients']);
            $this->assertSame('Verification code', $controller->delivery['subject']);
        } finally {
            foreach ($original as $property => $value) {
                $config->{$property} = $value;
            }
        }
    }

    public function testEveryPublicEmailFlowUsesConfiguredTransport(): void
    {
        $auth = file_get_contents(APPPATH . 'Controllers/Auth.php');
        $contact = file_get_contents(APPPATH . 'Controllers/Contact.php');

        $this->assertSame(3, substr_count($auth, 'sendConfiguredHtmlEmail('));
        $this->assertStringContainsString('sendConfiguredHtmlEmail(', $contact);
        $this->assertStringNotContainsString('getConfiguredEmailService()', $contact);
    }

    public function testRailwayWritesCompleteSmtpConfigurationWithoutSecretFallbacks(): void
    {
        $start = file_get_contents(HOMEPATH . 'start.sh');

        foreach (['EMAIL_SMTP_HOST', 'EMAIL_SMTP_USER', 'EMAIL_SMTP_PASS', 'EMAIL_SMTP_PORT', 'EMAIL_SMTP_CRYPTO', 'EMAIL_SMTP_TIMEOUT'] as $variable) {
            $this->assertStringContainsString($variable, $start);
        }
        $this->assertStringContainsString('password-reset and contact email delivery will fail fast until configured', $start);
        $this->assertStringNotContainsString('EMAIL_SMTP_PASS:-password', $start);
    }
}
