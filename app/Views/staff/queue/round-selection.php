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
                <select id="queueRoundRouteFilter" class="form-select mb-3" data-queue-route-search data-autocomplete-placeholder="Search route…" aria-label="Filter route">
                    <option value="all">All routes</option>
                    <?php foreach ($queueRoutes as $queueRoute): ?>
                    <option value="<?= esc($queueRoute['terminal_id'] . '|' . $queueRoute['destination'], 'attr') ?>"><?= esc($queueRoute['origin'] . ' → ' . $queueRoute['destination']) ?></option>
                    <?php endforeach; ?>
                </select>
                <div id="queueRoundFeedback" class="queue-action-notice" role="status" hidden></div>
                <div id="queueRoundControls" class="queue-round-grid">
                <?php foreach ($queueRoutes as $queueRoute): ?>
                    <?php $currentRoundActive = isset($queueRoute['round_intervals'][$queueRoute['round_number']]); ?>
                    <div class="queue-round-card" data-round-route="<?= esc($queueRoute['terminal_id'] . '|' . $queueRoute['destination'], 'attr') ?>">
                        <div>
                            <div class="queue-round-destination"><i class="bi bi-geo-alt"></i><?= esc(strtoupper($queueRoute['destination'])) ?></div>
                            <span class="queue-round-origin">From <?= esc($queueRoute['origin']) ?></span>
                        </div>
                        <div class="queue-round-choice">
                            <label for="dispatch-round-<?= (int) $queueRoute['id'] ?>">Active departure rule</label>
                            <select id="dispatch-round-<?= (int) $queueRoute['id'] ?>" class="form-select" data-no-autocomplete data-dispatch-round data-route-id="<?= (int) $queueRoute['id'] ?>" data-current-round="<?= $currentRoundActive ? (int) $queueRoute['round_number'] : '' ?>" data-round-unavailable="<?= empty($queueRoute['round_intervals']) ? 'true' : 'false' ?>" <?= empty($queueRoute['round_intervals']) ? 'disabled' : '' ?> aria-label="Active round for <?= esc($queueRoute['destination'], 'attr') ?>">
                            <?php if (!$currentRoundActive): ?>
                                <option value="" selected disabled><?= empty($queueRoute['round_intervals']) ? 'No rounds available now' : 'Choose an available round' ?></option>
                            <?php endif; ?>
                            <?php foreach ($queueRoute['round_choices'] as $roundChoice): ?>
                                <?php if (!isset($queueRoute['round_intervals'][$roundChoice])) continue; ?>
                                <option value="<?= (int) $roundChoice ?>" <?= $roundChoice === $queueRoute['round_number'] ? 'selected' : '' ?>>Round <?= (int) $roundChoice ?> · <?= (int) $queueRoute['round_intervals'][$roundChoice] ?> min</option>
                            <?php endforeach; ?>
                            </select>
                            <?php if (!$currentRoundActive): ?>
                            <p class="small text-warning mb-0 mt-2"><?= empty($queueRoute['round_intervals']) ? 'No departure rule is active for this route right now.' : 'Choose a round that is active now.' ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
                </div>
            </div>
            <div class="modal-footer">
                <a class="btn-modern btn-modern-outline btn-modern-sm" href="<?= base_url('staff/departure-rules') ?>"><i class="bi bi-sliders"></i> Manage rules</a>
                <button type="button" class="btn-modern btn-modern-primary btn-modern-sm" data-bs-dismiss="modal">Done</button>
            </div>
        </div>
    </div>
</div>
