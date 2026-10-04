<?php foreach (($queue ?? []) as $trip): ?>
    <label class="queue-cancel-item" data-cancel-route="<?= esc($trip['terminal_id'] . '|' . $trip['destination'], 'attr') ?>">
        <input type="checkbox" name="cancel_queue_ids[]" value="<?= (int) $trip['id'] ?>" aria-label="Select <?= esc($trip['plate_number'], 'attr') ?> for cancellation">
        <div class="queue-cancel-copy">
            <div class="queue-cancel-heading"><strong><?= esc($trip['plate_number']) ?></strong><span class="badge-modern badge-modern-primary"><?= esc(vehicle_type_label($trip['vehicle_type'] ?? '')) ?></span><span class="small text-muted"><?= ($trip['status'] ?? '') === 'boarding' ? 'Boarding' : 'Waiting' ?></span></div>
            <dl class="queue-cancel-details"><div><dt>Operator</dt><dd><?= esc($trip['operator_name'] ?: ($trip['owner_name'] ?? '—')) ?></dd></div><div><dt>Driver</dt><dd><?= esc($trip['driver_name'] ?: '—') ?></dd></div><div class="queue-cancel-destination"><dt>Destination</dt><dd><?= esc($trip['origin'] . ' → ' . $trip['destination']) ?></dd></div></dl>
        </div>
    </label>
<?php endforeach; ?>
