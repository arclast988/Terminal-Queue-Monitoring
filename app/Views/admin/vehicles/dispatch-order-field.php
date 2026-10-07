<div class="col-12 col-md-6 vehicle-dispatch-order-field">
    <label for="dispatch_order" class="form-label-modern">Daily starting order</label>
    <input type="number" class="form-control-modern" id="dispatch_order" name="dispatch_order"
        value="<?= esc((string) (old('dispatch_order') ?? $dispatchOrder ?? '')) ?>"
        min="1" max="9999" step="1" inputmode="numeric" placeholder="Last in route"
        aria-describedby="dispatch-order-help">
    <div id="dispatch-order-help" class="form-text-modern">
        Starting position for this destination. Leave blank to place last; other vehicles shift automatically.
        Departed vehicles move to the end. Resets at 12:00 AM Philippine time.
    </div>
</div>
