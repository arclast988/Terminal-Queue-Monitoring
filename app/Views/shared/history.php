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
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="px-4">Plate Number</th>
                        <th>Owner</th>
                        <th>Route (Origin - Dest)</th>
                        <th>Departure Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($departures)): ?>
                        <?php foreach ($departures as $item): ?>
                            <tr>
                                <td class="fw-bold px-4"><?= esc($item['plate_number']) ?></td>
                                <td><?= esc($item['owner_name']) ?></td>
                                <td>
                                    <small class="text-muted"><?= esc($item['origin']) ?></small>
                                    <i class="bi bi-arrow-right text-primary mx-1"></i>
                                    <strong><?= esc($item['destination']) ?></strong>
                                </td>
                                <td>
                                    <span class="badge bg-secondary fs-6">
                                        <?= date('M d, Y h:i A', strtotime($item['departure_time'])) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center py-5 text-muted">
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
