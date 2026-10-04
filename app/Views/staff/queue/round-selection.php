<div class="modal fade" id="queueRoundModal" tabindex="-1" aria-labelledby="queueRoundTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header queue-round-header">
                <div>
                    <h5 class="modal-title fw-bold" id="queueRoundTitle"><i class="bi bi-arrow-repeat me-2"></i>Departure rounds</h5>
                    <p class="small mb-0 mt-1">Choose the active departure rule for each route.</p>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <label class="form-label-modern" for="queueRoundRouteFilter">Filter route</label>
                <select id="queueRoundRouteFilter" class="form-select mb-3" data-no-autocomplete>
                    <option value="all">All routes</option>
                    <?php foreach ($queueRoutes as $queueRoute): ?>
                    <option value="<?= esc($queueRoute['terminal_id'] . '|' . $queueRoute['destination'], 'attr') ?>"><?= esc($queueRoute['origin'] . ' → ' . $queueRoute['destination']) ?></option>
                    <?php endforeach; ?>
                </select>
                <div id="queueRoundFeedback" class="queue-action-notice" role="status" hidden></div>
                <div id="queueRoundControls" class="queue-round-grid">
                <?php foreach ($queueRoutes as $queueRoute): ?>
                    <div class="queue-round-card" data-round-route="<?= esc($queueRoute['terminal_id'] . '|' . $queueRoute['destination'], 'attr') ?>">
                        <div>
                            <div class="queue-round-destination"><i class="bi bi-geo-alt"></i><?= esc(strtoupper($queueRoute['destination'])) ?></div>
                            <span class="queue-round-origin">From <?= esc($queueRoute['origin']) ?></span>
                        </div>
                        <div class="queue-round-choice">
                            <label for="dispatch-round-<?= (int) $queueRoute['id'] ?>">Active departure rule</label>
                            <select id="dispatch-round-<?= (int) $queueRoute['id'] ?>" class="form-select" data-no-autocomplete data-dispatch-round data-route-id="<?= (int) $queueRoute['id'] ?>" data-current-round="<?= (int) $queueRoute['round_number'] ?>" aria-label="Active round for <?= esc($queueRoute['destination'], 'attr') ?>">
                            <?php foreach ($queueRoute['round_choices'] as $roundChoice): ?>
                                <option value="<?= (int) $roundChoice ?>" <?= $roundChoice === $queueRoute['round_number'] ? 'selected' : '' ?> <?= !isset($queueRoute['round_intervals'][$roundChoice]) ? 'disabled' : '' ?>>Round <?= (int) $roundChoice ?><?= isset($queueRoute['round_intervals'][$roundChoice]) ? ' · ' . (int) $queueRoute['round_intervals'][$roundChoice] . ' min' : ' · No active rule' ?></option>
                            <?php endforeach; ?>
                            </select>
                            <?php if (!isset($queueRoute['round_intervals'][$queueRoute['round_number']])): ?>
                            <p class="small text-warning mb-0 mt-2">No rule is configured for this round today. Check its selected days and route in Departure Rules.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
                </div>
                <p class="queue-round-help mb-0 mt-3">Changes apply immediately to waiting vehicles. Vehicles already boarding keep their current timer. Automatic boarding continues every five minutes; use “Start boarding now” when you need an earlier start.</p>
            </div>
            <div class="modal-footer">
                <a class="btn-modern btn-modern-outline btn-modern-sm" href="<?= base_url('staff/departure-rules') ?>"><i class="bi bi-sliders"></i> Manage rules</a>
                <button type="button" class="btn-modern btn-modern-primary btn-modern-sm" data-bs-dismiss="modal">Done</button>
            </div>
        </div>
    </div>
</div>
