<?php

use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class SnykSecurityRemediationTest extends CIUnitTestCase
{
    public function testQueueStatusUsesSha256ForQueueHash(): void
    {
        $controllerFile = APPPATH . 'Controllers/Api/QueueStatus.php';
        $this->assertFileExists($controllerFile);
        $content = file_get_contents($controllerFile);

        $this->assertStringContainsString("hash('sha256', json_encode(\$items))", $content);
        $this->assertStringNotContainsString("md5(json_encode(\$items))", $content);
    }

    public function testQueueSyncHasNoInnerHtmlSinkAssignments(): void
    {
        $jsFile = FCPATH . 'js/queue-sync.js';
        $this->assertFileExists($jsFile);
        $content = file_get_contents($jsFile);

        // Split after DOMPurify header/bundle (line 35+)
        $lines = explode("\n", $content);
        $customCodeLines = array_slice($lines, 35);
        $customCode = implode("\n", $customCodeLines);

        // Ensure no element.innerHTML = ... assignments exist in custom sync code
        $this->assertDoesNotMatchRegularExpression('/\binnerHTML\s*=/i', $customCode);

        // Ensure safe DOM methods and DOMPurify RETURN_DOM_FRAGMENT are utilized
        $this->assertStringContainsString('replaceElementChildrenSafely', $customCode);
        $this->assertStringContainsString('RETURN_DOM_FRAGMENT: true', $customCode);
        $this->assertStringContainsString('replaceChildren', $customCode);
    }
}
