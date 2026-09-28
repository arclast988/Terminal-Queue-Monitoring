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

        // Auto-selection notifies validation and syncs the visible autocomplete field.
        $this->assertStringContainsString('destSelect.dispatchEvent(new Event(\'change\', { bubbles: true }));', $faresViewContent);
        $this->assertStringContainsString('destSelect.syncAutocompleteValue();', $faresViewContent);

        // Verify syncAddDestinationUI is called in auto-open and modal lifecycle
        $this->assertStringContainsString('syncAddDestinationUI();', $faresViewContent);

        // Verify syncAutocompleteValue is supported in autocomplete-search.js
        $autocompleteJs = file_get_contents(FCPATH . 'assets/js/autocomplete-search.js');
        $this->assertStringContainsString('selectEl.syncAutocompleteValue = updateInputValue;', $autocompleteJs);

        // Verify Fare Amount input in addFareModal has ID add_fare_amount
        $this->assertStringContainsString('id="add_fare_amount"', $faresViewContent);

        // Desktop focus waits for the modal to settle; touch devices avoid a keyboard jump.
        $this->assertStringContainsString('function focusAddFareInput(', $faresViewContent);
        $this->assertStringContainsString("window.matchMedia('(min-width: 769px) and (pointer: fine)').matches", $faresViewContent);
        $this->assertStringContainsString('focusAddFareInput(false);', $faresViewContent);
    }
}
