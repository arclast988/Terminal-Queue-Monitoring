<?= $this->include('templates/header') ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="fas fa-history text-primary me-2"></i> Departure History</h1>
</div>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-body p-4">
        <!-- Search Form -->
        <form method="get" action="<?= base_url('history') ?>">
            <div class="input-group input-group-lg">
                <span class="input-group-text bg-primary text-white border-0"><i class="bi bi-search"></i></span>
                <input type="text" class="form-control bg-light border-0" name="q" placeholder="Search plate number, destination, or owner..." value="<?= esc($search ?? '') ?>">
                <button type="submit" class="btn btn-primary px-4"><i class="bi bi-search"></i> Search</button>
            </div>
        </form>
        <?php if (!empty($search)): ?>
            <div class="mt-3">
                <span class="badge bg-info p-2 fs-6">Search results for: "<?= esc($search) ?>"</span>
                <a href="<?= base_url('history') ?>" class="btn btn-sm btn-outline-secondary ms-2"><i class="bi bi-x"></i> Clear</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-secondary text-white py-3">
        <h5 class="mb-0"><i class="bi bi-list-ul"></i> All Past Departures</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive table-responsive-card">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="px-4">Plate Number</th>
                        <th>Operator</th>
                        <th>Driver</th>
                        <th>Route</th>
                        <th>Departure Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($departures)): ?>
                        <?php foreach ($departures as $item): ?>
                            <tr>
                                <td class="fw-bold px-4" data-label="Plate Number"><?= esc($item['plate_number']) ?></td>
                                <td data-label="Operator">
                                    <?php
                                        $opName = $item['operator_name'] ?: ($item['owner_name'] ?? '');
                                        $drName = $item['driver_name'] ?? '';
                                    ?>
                                    <?php if (!empty($opName) && strtolower(trim($opName)) !== strtolower(trim($drName))): ?>
                                        <div class="fw-bold"><i class="bi bi-building me-1 text-muted"></i><?= esc($opName) ?></div>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td data-label="Driver">
                                    <div class="fw-bold"><i class="bi bi-person-badge me-1 text-primary"></i><?= esc($item['driver_name'] ?? '—') ?></div>
                                </td>
                                <td data-label="Route">
                                    <small class="text-muted"><?= esc($item['origin']) ?></small>
                                    <i class="bi bi-arrow-right text-primary mx-1"></i>
                                    <strong><?= esc($item['destination']) ?></strong>
                                </td>
                                <td data-label="Departure Time">
                                    <div style="white-space: nowrap;">
                                        <span style="font-weight: 700; color: var(--primary-dark); font-size: 13px;">
                                            <?= date('H:i', strtotime($item['departure_time'])) ?>
                                        </span>
                                        <div style="font-size: 12px; color: var(--text-muted); margin-top: 2px;">
                                            <i class="bi bi-calendar3 me-1"></i><?= date('M d, Y', strtotime($item['departure_time'])) ?>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                                No departure history found.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php if ($pager): ?>
        <div class="card-footer bg-white py-3 d-flex justify-content-center">
            <?= $pager->links() ?>
        </div>
    <?php endif; ?>
</div>

<?= $this->include('templates/footer') ?>
