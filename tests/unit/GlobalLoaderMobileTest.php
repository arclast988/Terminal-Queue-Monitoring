<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;

final class GlobalLoaderMobileTest extends CIUnitTestCase
{
    public function testPageNavigationFeedbackIsAvailableInMobileBrowsersAndInstalledApps(): void
    {
        $script = file_get_contents(FCPATH . 'assets/js/global-loader.js');

        $this->assertNotFalse($script);
        $this->assertStringNotContainsString('.global-progress-bar { display: none !important; }', $script);
        $this->assertStringContainsString('gl-progress-sweep', $script);
        $this->assertStringContainsString("window.matchMedia('(display-mode: standalone)').matches", $script);
        $this->assertStringContainsString("sessionStorage.getItem('gl_standalone_opened')", $script);
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
