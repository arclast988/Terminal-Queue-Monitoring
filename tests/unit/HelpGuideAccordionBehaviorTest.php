<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;

final class HelpGuideAccordionBehaviorTest extends CIUnitTestCase
{
    public function testEveryHelpGuideUsesSingleOpenAccordionBehavior(): void
    {
        $guideFiles = [
            APPPATH . 'Views/templates/guestfooter.php',
            APPPATH . 'Views/help/admin.php',
            APPPATH . 'Views/help/dispatcher.php',
        ];

        foreach ($guideFiles as $guideFile) {
            $guide = file_get_contents($guideFile);

            $this->assertStringNotContainsString('class="accordion-item active"', $guide, $guideFile);
            $this->assertStringContainsString("closest('.accordion-list')", $guide, $guideFile);
            $this->assertStringContainsString("querySelectorAll('.accordion-item.active')", $guide, $guideFile);
            $this->assertStringContainsString("openItem !== item", $guide, $guideFile);
            $this->assertStringContainsString("classList.toggle('active', !isActive)", $guide, $guideFile);
        }
    }
}
