<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;
use Config\ContentManagement;

final class ContentManagementTest extends CIUnitTestCase
{
    public function testCatalogSeparatesEveryAudienceAndUsesPlainTextFields(): void
    {
        $groups = ContentManagement::groups();

        foreach (['contact', 'faq', 'terms', 'commuter', 'admin', 'superadmin', 'dispatcher'] as $group) {
            $this->assertArrayHasKey($group, $groups);
            $this->assertNotEmpty($groups[$group]['fields']);
        }

        foreach (ContentManagement::fields() as $key => $field) {
            $this->assertStringStartsWith('content_', $key);
            $this->assertContains($field['type'], ['text', 'textarea']);
        }
    }

    public function testContentManagerIsSuperadminOnlyAndAudited(): void
    {
        $routes = file_get_contents(APPPATH . 'Config/Routes.php');
        $controller = file_get_contents(APPPATH . 'Controllers/Admin/Settings.php');
        $drawer = file_get_contents(APPPATH . 'Views/partials/header.php');
        $profile = file_get_contents(APPPATH . 'Views/partials/nav-profile.php');

        $this->assertStringContainsString("group('admin/settings', ['filter' => 'auth:super_admin']", $routes);
        $this->assertStringContainsString("post('content', 'Admin\\Settings::updateContent')", $routes);
        $this->assertStringContainsString("session()->get('role') !== 'super_admin'", $controller);
        $this->assertStringContainsString("logActivity('Content Manager'", $controller);
        $this->assertStringContainsString("'content_terms_updated_at'", $controller);
        $this->assertStringContainsString('Content Manager', $drawer);
        $this->assertStringContainsString('Content Manager', $profile);
    }

    public function testManagedCopyKeepsBuiltInFallbacksAndUsesTextContent(): void
    {
        $guest = file_get_contents(APPPATH . 'Views/templates/guestfooter.php');
        $admin = file_get_contents(APPPATH . 'Views/help/admin.php');
        $manual = file_get_contents(APPPATH . 'Controllers/Manual.php');
        $dispatcher = file_get_contents(APPPATH . 'Views/help/dispatcher.php');
        $script = file_get_contents(FCPATH . 'assets/js/managed-content.js');

        $this->assertStringContainsString('managed_content_overrides', $guest);
        $this->assertStringContainsString('content_terms_updated_at', $guest);
        $this->assertStringContainsString('ManagedContent.applyGuest', $guest);
        $this->assertStringContainsString("'content_superadmin'", $admin);
        $this->assertStringContainsString("'Super Administrator Help Guide'", $manual);
        $this->assertStringContainsString('ManagedContent.applyGuide', $admin);
        $this->assertStringContainsString('ManagedContent.applyGuide', $dispatcher);
        $this->assertStringContainsString('element.textContent = value;', $script);
        $this->assertStringNotContainsString('innerHTML = value', $script);
    }

    public function testContentManagerExplainsBlankFieldFallbackAndMobileLayout(): void
    {
        $view = file_get_contents(APPPATH . 'Views/admin/settings/content.php');

        $this->assertStringContainsString('Super Admin Only', $view);
        $this->assertStringContainsString('Leave any field blank to keep the built-in wording', $view);
        $this->assertStringContainsString('Clear this field to restore the built-in version.', $view);
        $this->assertStringContainsString('@media(max-width:720px)', $view);
        $this->assertStringContainsString('other.open = false;', $view);
    }
}
