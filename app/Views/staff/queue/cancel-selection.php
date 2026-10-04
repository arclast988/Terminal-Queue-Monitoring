<div class="modal fade" id="cancelSelectionModal" tabindex="-1" aria-labelledby="cancelSelectionTitle" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header queue-selection-header">
                <div><h5 class="modal-title fw-bold" id="cancelSelectionTitle"><i class="bi bi-check2-square me-2"></i>Select trips to cancel</h5><p class="small mb-0 mt-1">Review the vehicle details and choose the trips to remove from the queue.</p></div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="queue-selection-tools">
                    <div><label class="form-label-modern" for="cancelRouteFilter">Filter route</label><select id="cancelRouteFilter" class="form-select" data-no-autocomplete><option value="all">All routes</option>
                        <?php foreach (($queueRoutes ?? []) as $route): ?><option value="<?= esc($route['terminal_id'] . '|' . $route['destination'], 'attr') ?>"><?= esc($route['origin'] . ' → ' . $route['destination']) ?></option><?php endforeach; ?>
                    </select></div>
                    <label class="queue-select-visible"><input type="checkbox" id="cancelSelectVisible"> Select all shown</label>
                </div>
                <div id="cancelSelectionFeedback" class="queue-action-notice" role="alert" hidden></div>
                <div class="queue-cancel-list" id="cancelSelectionList">
                <?php foreach (($queue ?? []) as $trip): ?>
                    <label class="queue-cancel-item" data-cancel-route="<?= esc($trip['terminal_id'] . '|' . $trip['destination'], 'attr') ?>">
                        <input type="checkbox" name="cancel_queue_ids[]" value="<?= (int) $trip['id'] ?>" aria-label="Select <?= esc($trip['plate_number'], 'attr') ?> for cancellation">
                        <div class="queue-cancel-copy">
                            <div class="queue-cancel-heading"><strong><?= esc($trip['plate_number']) ?></strong><span class="badge-modern badge-modern-primary"><?= esc(vehicle_type_label($trip['vehicle_type'] ?? '')) ?></span><span class="small text-muted"><?= ($trip['status'] ?? '') === 'boarding' ? 'Boarding' : 'Waiting' ?></span></div>
                            <dl class="queue-cancel-details"><div><dt>Operator</dt><dd><?= esc($trip['operator_name'] ?: ($trip['owner_name'] ?? '—')) ?></dd></div><div><dt>Driver</dt><dd><?= esc($trip['driver_name'] ?: '—') ?></dd></div><div class="queue-cancel-destination"><dt>Destination</dt><dd><?= esc($trip['origin'] . ' → ' . $trip['destination']) ?></dd></div></dl>
                        </div>
                    </label>
                <?php endforeach; ?>
                </div>
                <p id="cancelSelectionEmpty" class="small text-muted text-center p-3" hidden>No active trips for this route.</p>
            </div>
            <div class="modal-footer queue-selection-footer">
                <span id="cancelSelectionCount" class="small text-muted">0 trips selected</span>
                <button type="button" class="btn-modern btn-modern-outline btn-modern-sm" id="refreshCancelSelection">Refresh selection</button>
                <button type="button" class="btn-modern btn-modern-outline btn-modern-sm" data-bs-dismiss="modal">Keep trips</button>
                <button type="button" class="btn-modern btn-modern-danger btn-modern-sm" id="cancelSelectedSubmit" disabled>Cancel selected trips</button>
            </div>
        </div>
    </div>
</div>
