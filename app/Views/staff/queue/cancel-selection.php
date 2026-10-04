<div class="modal fade" id="cancelSelectionModal" tabindex="-1" aria-labelledby="cancelSelectionTitle" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header queue-selection-header">
                <div><h5 class="modal-title fw-bold" id="cancelSelectionTitle"><i class="bi bi-check2-square me-2"></i>Select trips to cancel</h5><p class="small mb-0 mt-1">Review the vehicle details and choose the trips to remove from the queue.</p></div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="queue-selection-tools">
                    <div><label class="form-label-modern" for="cancelRouteFilter">Filter route</label><select id="cancelRouteFilter" class="form-select" data-queue-route-search data-autocomplete-placeholder="Search route…" aria-label="Filter route"><option value="all">All routes</option>
                        <?php foreach (($queueRoutes ?? []) as $route): ?><option value="<?= esc($route['terminal_id'] . '|' . $route['destination'], 'attr') ?>"><?= esc($route['origin'] . ' → ' . $route['destination']) ?></option><?php endforeach; ?>
                    </select></div>
                    <div class="queue-cancel-search">
                        <label class="form-label-modern" for="cancelVehicleSearch">Search vehicles</label>
                        <div class="queue-cancel-search-field">
                            <i class="bi bi-search" aria-hidden="true"></i>
                            <input type="search" id="cancelVehicleSearch" class="form-control" placeholder="Plate, type, operator or driver…" autocomplete="off">
                            <button type="button" id="clearCancelVehicleSearch" class="queue-cancel-search-clear" aria-label="Clear vehicle search" hidden><i class="bi bi-x-lg" aria-hidden="true"></i></button>
                        </div>
                    </div>
                    <label class="queue-select-visible"><input type="checkbox" id="cancelSelectVisible"> Select all shown</label>
                </div>
                <div id="cancelSelectionFeedback" class="queue-action-notice" role="alert" hidden></div>
                <div class="queue-cancel-list" id="cancelSelectionList">
                <?= view('staff/queue/cancel-list', ['queue' => $queue ?? []]) ?>
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
