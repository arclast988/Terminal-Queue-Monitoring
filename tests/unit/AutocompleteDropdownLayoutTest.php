<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;

/**
 * Guards the bounded, correctly stacked autocomplete menu used by mobile filters.
 */
final class AutocompleteDropdownLayoutTest extends CIUnitTestCase
{
    public function testDropdownKeepsItsConfiguredHeightLimit(): void
    {
        $script = file_get_contents(FCPATH . 'assets/js/autocomplete-search.js');

        $this->assertStringContainsString('getDefaultDropdownMaxHeight', $script);
        $this->assertStringContainsString("dropdown.style.maxHeight = defaultMaxHeight + 'px';", $script);
        $this->assertStringNotContainsString("dropdown.style.maxHeight = '';", $script);
    }

    public function testOpenFieldStacksAboveSiblingFilters(): void
    {
        $script = file_get_contents(FCPATH . 'assets/js/autocomplete-search.js');

        $this->assertStringContainsString("style.setProperty('z-index', isInModal ? '1060' : '200', 'important')", $script);
        $this->assertStringContainsString('z-index: 200 !important;', $script);
    }

    public function testTemplatesUseUpdatedAutocompleteAssetVersion(): void
    {
        $guestFooter = file_get_contents(APPPATH . 'Views/templates/guestfooter.php');
        $appFooter = file_get_contents(APPPATH . 'Views/templates/footer.php');

        $this->assertStringContainsString('autocomplete-search.js?v=20260920_2', $guestFooter);
        $this->assertStringContainsString('autocomplete-search.js?v=20260920_2', $appFooter);
    }
}
