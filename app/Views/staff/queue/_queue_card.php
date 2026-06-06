<?php
/**
 * Dispatcher "Active Queue Card" — presentational partial.
 * Preserves every realtime hook the existing JS relies on:
 *   id="card-{id}", id="passenger-count-{id}", id="full-badge-{id}",
 *   onclick updatePassengers()/updateStatus(), and the Bootstrap depart modal.
 * Expects: $item (one queue row).
 */
$isFull     = (int) $item['current_passengers'] >= (int) $item['capacity'];
$isBoarding = ($item['status'] ?? '') === 'boarding';
$vType      = strtolower($item['vehicle_type'] ?? '');
$pct        = min(100, ($item['current_passengers'] / max(1, $item['capacity'])) * 100);
$fill       = $pct >= 100 ? 'full' : ($pct >= 70 ? 'mid' : 'low');
?>
<article class="tq-card status-<?= $isBoarding ? 'boarding' : 'waiting' ?>" id="card-<?= $item['id'] ?>">
    <header class="tq-card__head">
        <span class="tq-pos <?= vehicle_type_class($vType) ?>">#<?= $item['position'] ?></span>
        <span class="tq-plate"><?= esc($item['plate_number']) ?></span>
        <?= vehicle_type_badge($vType) ?>
        <?php if ($isBoarding): ?>
            <span class="tq-pill tq-pill--go"><span class="tq-pill__dot"></span> Boarding</span>
        <?php else: ?>
            <span class="tq-pill tq-pill--wait"><span class="tq-pill__dot"></span> Waiting</span>
        <?php endif; ?>
    </header>

    <div class="tq-card__meta">
        <div><i class="fas fa-route"></i> <span><strong><?= esc($item['origin']) ?></strong> &rarr; <strong><?= esc($item['destination']) ?></strong></span></div>
        <div><i class="fas fa-clock"></i> Arrived <?= date('h:i A', strtotime($item['arrival_time'])) ?></div>
        <div class="tq-eta"><i class="fas fa-plane-departure"></i> Est. <strong><?= !empty($item['estimated_departure']) ? date('h:i A', strtotime($item['estimated_departure'])) : 'Waiting' ?></strong></div>
        <div><i class="fas fa-chair"></i> <?= (int) $item['capacity'] ?> seats</div>
    </div>

    <div class="tq-cap" data-fill="<?= $fill ?>" style="--pct:<?= $pct ?>%">
        <div class="tq-cap__head">
            <i class="fas fa-users"></i> Passengers
            <span id="passenger-count-<?= $item['id'] ?>" class="tq-cap__count ms-auto <?= $isFull ? 'is-full text-danger' : '' ?>">
                <?= $item['current_passengers'] ?> / <?= $item['capacity'] ?>
            </span>
            <span class="tq-pill tq-pill--full full-badge" id="full-badge-<?= $item['id'] ?>" style="<?= $isFull ? '' : 'display:none;' ?>">Full</span>
        </div>
        <div class="tq-cap__bar"><span></span></div>
    </div>

    <footer class="tq-card__actions">
        <button type="button" class="tq-step tq-step--minus" aria-label="Remove passenger"
            onclick="updatePassengers(<?= $item['id'] ?>, 'decrement')">&minus;</button>
        <button type="button" class="tq-step tq-step--plus" aria-label="Add passenger"
            onclick="updatePassengers(<?= $item['id'] ?>, 'increment')">&#43;</button>
        <button type="button" class="tq-btn tq-btn--ghost" onclick="updatePassengers(<?= $item['id'] ?>, 'max')">MAX</button>
        <span class="tq-spacer"></span>
        <?php if (!$isBoarding): ?>
            <button type="button" class="tq-btn tq-btn--go" onclick="updateStatus(<?= $item['id'] ?>, 'boarding', null, this)">
                <i class="fas fa-bullhorn"></i> Start Boarding
            </button>
        <?php else: ?>
            <button type="button" class="tq-btn tq-btn--go" data-bs-toggle="modal" data-bs-target="#confirmDepartModal<?= $item['id'] ?>">
                <i class="fas fa-plane-departure"></i> Depart
            </button>
        <?php endif; ?>
        <button type="button" class="tq-btn tq-btn--danger"
            onclick="if(confirm('Cancel this trip?')) updateStatus(<?= $item['id'] ?>, 'canceled', null, this)">Cancel</button>
    </footer>
</article>
