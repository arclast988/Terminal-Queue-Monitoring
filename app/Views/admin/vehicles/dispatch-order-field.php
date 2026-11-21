<?php
    $dispatchOrderStatus = $dispatchOrderStatus ?? 'active';
    $dispatchRouteId = (string) (old('route_id') ?? $dispatchRouteId ?? '');
    $routeDispatchOrders = $routeDispatchOrders ?? [];
    $dispatchCounts = $routeDispatchOrders[$dispatchRouteId] ?? ['active_count' => 0, 'total_count' => 0];
    $dispatchMinimum = $dispatchOrderStatus === 'active' ? 1 : (int) $dispatchCounts['active_count'] + 1;
    $dispatchMaximum = (int) $dispatchCounts[$dispatchOrderStatus === 'active' ? 'active_count' : 'total_count'] + 1;
?>
<div class="col-12 col-md-6 vehicle-dispatch-order-field">
    <label for="dispatch_order" class="form-label-modern">Route order (#)</label>
    <input type="number" class="form-control-modern" id="dispatch_order" name="dispatch_order"
        value="<?= esc((string) (old('dispatch_order') ?? $dispatchOrder ?? '')) ?>"
        min="<?= $dispatchMinimum ?>" max="<?= $dispatchMaximum ?>" step="1" inputmode="numeric" placeholder="Last in route"
        aria-describedby="dispatch-order-help dispatch-order-range">
    <div id="dispatch-order-help" class="form-text-modern">
        Changing this number sets today's lineup and the daily starting position.
        Leave blank to put this vehicle last. Departures then advance today's lineup.
    </div>
    <div id="dispatch-order-range" class="form-text-modern" aria-live="polite"></div>
    <?php if (isset($currentDispatchPosition)): ?>
        <div id="vehicle-current-order-note" class="form-text-modern mt-2"
            <?= (string) $currentDispatchPosition !== (string) ($dispatchOrder ?? '') && (string) old('sync_current_order') !== '1' ? '' : 'hidden' ?>>
            Today's current position: <?= esc((string) $currentDispatchPosition) ?>.
        </div>
    <?php endif; ?>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var input = document.getElementById('dispatch_order');
    var route = document.getElementById('route_id');
    var range = document.getElementById('dispatch-order-range');
    var counts = <?= json_encode($routeDispatchOrders, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    var active = <?= json_encode($dispatchOrderStatus === 'active') ?>;
    if (!input || !route) return;

    function updateBounds() {
        var line = counts[route.value] || {active_count: 0, total_count: 0};
        var minimum = active ? 1 : Number(line.active_count) + 1;
        var maximum = Number(active ? line.active_count : line.total_count) + 1;
        input.min = String(minimum);
        input.max = String(maximum);
        if (range) range.textContent = route.value
            ? (minimum === maximum ? 'Available position: ' + maximum + '.' : 'Available positions: ' + minimum + ' to ' + maximum + '.')
            : 'Select a destination to choose its order.';
        if (input.value !== '' && Number.isFinite(input.valueAsNumber)) {
            var bounded = Math.min(input.valueAsNumber, maximum);
            if (bounded !== input.valueAsNumber) {
                input.value = String(bounded);
                input.dispatchEvent(new Event('input', {bubbles: true}));
            }
        }
    }
    input.addEventListener('input', updateBounds);
    input.addEventListener('change', updateBounds);
    // Type changes select a sibling route first; apply its range after those handlers.
    route.addEventListener('change', function () { Promise.resolve().then(updateBounds); });
    Promise.resolve().then(updateBounds);
});
</script>
