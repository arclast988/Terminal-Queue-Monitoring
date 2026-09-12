<?php

use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class AddFareAutoSelectDestinationTest extends CIUnitTestCase
{
    public function testFaresViewContainsDestinationSyncAndAutoSelectLogic(): void
    {
        $faresViewContent = file_get_contents(APPPATH . 'Views/shared/fares.php');

        // Verify syncAddDestinationUI is defined
        $this->assertStringContainsString('function syncAddDestinationUI()', $faresViewContent);

        // Verify destination select dispatches change and input events on auto-open
        $this->assertStringContainsString('destSelect.dispatchEvent(new Event(\'change\', { bubbles: true }));', $faresViewContent);
        $this->assertStringContainsString('destSelect.dispatchEvent(new Event(\'input\', { bubbles: true }));', $faresViewContent);

        // Verify syncAddDestinationUI is called in auto-open and modal lifecycle
        $this->assertStringContainsString('syncAddDestinationUI();', $faresViewContent);

        // Verify syncAutocompleteValue is supported in autocomplete-search.js
        $autocompleteJs = file_get_contents(FCPATH . 'assets/js/autocomplete-search.js');
        $this->assertStringContainsString('selectEl.syncAutocompleteValue = updateInputValue;', $autocompleteJs);

        // Verify Fare Amount input in addFareModal has ID add_fare_amount
        $this->assertStringContainsString('id="add_fare_amount"', $faresViewContent);

        // Verify focusAddFareInput helper and shown.bs.modal focus triggers exist
        $this->assertStringContainsString('function focusAddFareInput(', $faresViewContent);
        $this->assertStringContainsString('focusAddFareInput(true);', $faresViewContent);
    }
}
