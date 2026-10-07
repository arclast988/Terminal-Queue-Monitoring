<div class="col-12 col-md-6 vehicle-dispatch-order-field">
    <label for="dispatch_order" class="form-label-modern">Daily starting order</label>
    <input type="number" class="form-control-modern" id="dispatch_order" name="dispatch_order"
        value="<?= esc((string) (old('dispatch_order') ?? $dispatchOrder ?? '')) ?>"
        min="1" max="9999" step="1" inputmode="numeric" placeholder="Last in route"
        aria-describedby="dispatch-order-help">
    <div id="dispatch-order-help" class="form-text-modern">
        Saved starting position for this destination at 12:00 AM Philippine time.
        Active vehicles use consecutive positions. Leave blank to place last; other vehicles shift automatically.
        Today's order changes after departures, so it can differ from this starting position.
    </div>
</div>
