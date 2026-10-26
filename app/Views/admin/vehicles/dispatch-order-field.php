<div class="col-12 col-md-6 vehicle-dispatch-order-field">
    <label for="dispatch_order" class="form-label-modern">Daily starting position</label>
    <input type="number" class="form-control-modern" id="dispatch_order" name="dispatch_order"
        value="<?= esc((string) (old('dispatch_order') ?? $dispatchOrder ?? '')) ?>"
        min="1" max="9999" step="1" inputmode="numeric" placeholder="Last in route"
        aria-describedby="dispatch-order-help">
    <div id="dispatch-order-help" class="form-text-modern">
        Starting place in line each day at 12:00 AM (Philippine time).
        Leave blank to put this vehicle last. The order changes as vehicles leave.
    </div>
</div>
