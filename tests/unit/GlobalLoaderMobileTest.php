<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;

final class GlobalLoaderMobileTest extends CIUnitTestCase
{
    public function testMobileHidesOnlyTheCustomTopProgressBar(): void
    {
        $script = file_get_contents(FCPATH . 'assets/js/global-loader.js');

        $this->assertNotFalse($script);
        $this->assertStringContainsString('@media (max-width: 768px), (hover: none) and (pointer: coarse)', $script);
        $this->assertStringContainsString('.global-progress-bar { display: none !important; }', $script);
        $this->assertStringContainsString('.table-loader-overlay {', $script);
        $this->assertStringContainsString('.gl-btn-spinner {', $script);

        foreach ([
            'templates/footer.php',
            'templates/guestfooter.php',
            'auth/login.php',
            'auth/forgot_password.php',
            'auth/reset_password.php',
            'auth/verify_code.php',
        ] as $viewPath) {
            $view = file_get_contents(APPPATH . 'Views/' . $viewPath);
            $this->assertNotFalse($view);
            $this->assertMatchesRegularExpression('/global-loader\.js\?v=[0-9A-Za-z_-]+/', $view);
        }
    }
}
