<?php

use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class RealtimePassengerBadgeTest extends CIUnitTestCase
{
    public function testStaffQueueViewHasFullBadgeWrapAndDataCapacity(): void
    {
        $viewFile = APPPATH . 'Views/staff/queue/index.php';
        $this->assertFileExists($viewFile);
        $content = file_get_contents($viewFile);

        // Ensure data-capacity attribute exists on passenger count span
        $this->assertStringContainsString('data-capacity=', $content);

        // Ensure full-badge- wrapper with dynamic ID exists
        $this->assertStringContainsString('id="full-badge-<?= $item[\'id\'] ?>"', $content);
        $this->assertStringContainsString('class="full-badge-wrap"', $content);
    }

    public function testDebouncePassengersModuleHandlesFullBadgeToggling(): void
    {
        $jsFile = FCPATH . 'js/debounce-passengers.js';
        $this->assertFileExists($jsFile);
        $content = file_get_contents($jsFile);

        // Ensure getBadge searches for full-badge- container
        $this->assertStringContainsString("'full-badge-' + id", $content);
        $this->assertStringContainsString(".full-badge-wrap", $content);

        // Ensure updateUI toggles display based on capacity
        $this->assertStringContainsString("badge.style.display = isFull ? 'block' : 'none'", $content);
    }

    public function testQueueSyncModuleHandlesFullBadgeToggling(): void
    {
        $jsFile = FCPATH . 'js/queue-sync.js';
        $this->assertFileExists($jsFile);
        $content = file_get_contents($jsFile);

        // Ensure getBadge searches for full-badge- container in queue-sync
        $this->assertStringContainsString("'full-badge-' + id", $content);
        $this->assertStringContainsString(".full-badge-wrap", $content);

        // Ensure updatePassengerUI toggles display based on capacity
        $this->assertStringContainsString("badge.style.display = isFull ? 'block' : 'none'", $content);
    }

    public function testGuestPassengerMessageUsesPassengerOnlyUpdater(): void
    {
        $viewFile = APPPATH . 'Views/public/enhanced_dashboard.php';
        $content = file_get_contents($viewFile);

        $this->assertStringContainsString('function updateQueueCardPassengerOnly(card, count, capacity)', $content);

        $handlerStart = strpos($content, 'function handleRealtimePassengerChange(data)');
        $handlerEnd = strpos($content, '// Initialize real-time sync.', $handlerStart);
        $this->assertNotFalse($handlerStart);
        $this->assertNotFalse($handlerEnd);

        $handler = substr($content, $handlerStart, $handlerEnd - $handlerStart);
        $this->assertStringContainsString('updateQueueCardPassengerOnly(card, itemStub.current_passengers, itemStub.capacity)', $handler);
        $this->assertStringNotContainsString('updateQueueCardInPlace(card, itemStub', $handler);
    }

    public function testGuestPassengerPopupDeduplicatesRepeatedRealtimeDelivery(): void
    {
        $viewFile = APPPATH . 'Views/public/enhanced_dashboard.php';
        $content = file_get_contents($viewFile);

        $this->assertStringContainsString('window.__pttmPassengerPopByKey || {}', $content);
        $this->assertStringContainsString('window.__pttmPassengerPopByKey = _lastPassengerPopByKey;', $content);
        $this->assertStringContainsString('lastPop.count === normalizedTarget', $content);
        $this->assertStringContainsString("anchorEl.querySelectorAll('.passenger-delta-badge')", $content);
        $this->assertStringContainsString('triggerPassengerPop(anchor, pCount - previousCount, passengerKey, pCount)', $content);
        $this->assertSame(1, substr_count($content, 'triggerPassengerPop(anchor,'), 'Only the real-time handler should display the passenger delta popup');
        $this->assertStringNotContainsString('var passengerDeltas = {};', $content, 'Polling should reconcile counts without replaying the popup');
        $this->assertStringNotContainsString('var _pttmQueueChannel = null;', $content, 'QueueSync should be the single owner of cross-tab queue delivery');
        $this->assertStringContainsString('var _lastRealtimePassengerAt = {};', $content);
        $this->assertStringContainsString('function placeQueueCardAt(card, desiredIndex)', $content);
        $this->assertStringNotContainsString('queueList.appendChild(existingCard);', $content, 'Stable cards must not be detached because that restarts the active popup animation');
    }

    public function testGuestPassengerPopupUsesCompositorFriendlyShortAnimation(): void
    {
        $content = file_get_contents(APPPATH . 'Views/public/enhanced_dashboard.php');

        $this->assertStringContainsString('animation: passengerDeltaFloat 0.68s', $content);
        $this->assertStringContainsString('will-change: transform, opacity;', $content);
        $this->assertStringContainsString("badge.addEventListener('animationend'", $content);
        $this->assertStringNotContainsString('@keyframes ghostFloatUp', $content);
        $this->assertStringContainsString('content-visibility: auto;', $content);

        $animationStart = strpos($content, '@keyframes passengerDeltaFloat');
        $animationEnd = strpos($content, '.passenger-count-row', $animationStart);
        $this->assertNotFalse($animationStart);
        $this->assertNotFalse($animationEnd);
        $animationCss = substr($content, $animationStart, $animationEnd - $animationStart);
        $this->assertStringNotContainsString('filter: blur(', $animationCss);
    }

    public function testSharedPassengerUpdaterDoesNotInjectFullBadgeIntoNumberSpan(): void
    {
        $queueSync = file_get_contents(FCPATH . 'js/queue-sync.js');
        $debounce = file_get_contents(FCPATH . 'js/debounce-passengers.js');

        $guard = "span && !span.classList.contains('passenger-count-num')";
        $this->assertStringContainsString($guard, $queueSync);
        $this->assertStringContainsString($guard, $debounce);
    }

    public function testDepartureRulesModalRestoresFocusBeforeBeingHidden(): void
    {
        $content = file_get_contents(APPPATH . 'Views/public/enhanced_dashboard.php');

        $this->assertStringContainsString('aria-hidden="true" inert', $content);
        $this->assertStringContainsString('var routeAverageReturnFocus = null;', $content);
        $this->assertStringContainsString("modal.removeAttribute('inert')", $content);
        $this->assertStringContainsString("modal.setAttribute('inert', '')", $content);

        $focusRestore = strpos($content, "focusTarget.focus({ preventScroll: true })");
        $hideModal = strpos($content, "modal.setAttribute('aria-hidden', 'true')");
        $this->assertNotFalse($focusRestore);
        $this->assertNotFalse($hideModal);
        $this->assertLessThan($hideModal, $focusRestore, 'Focus must be restored before the modal is hidden from assistive technology');
    }
}
