<?= view('templates/header', ['title' => $title]) ?>

<div class="row mb-3">
    <div class="col-md-6">
        <h2>Add New Route / Fare</h2>
    </div>
    <div class="col-md-6 text-end">
        <a href="<?= base_url('admin/routes') ?>" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to List
        </a>
    </div>
</div>

<div class="card shadow">
    <div class="card-body">
        <?php if (session()->getFlashdata('errors')): ?>
            <div class="alert alert-danger">
                <ul>
                    <?php foreach (session()->getFlashdata('errors') as $error): ?>
                        <li><?= esc($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="<?= base_url('admin/routes/store') ?>" method="post">
            <?= csrf_field() ?>

            <div class="mb-3">
                <label for="origin" class="form-label fw-semibold">Origin</label>
                <select class="form-select" id="origin" name="origin" required>
                    <option value="">— Select Origin —</option>
                    <?php foreach ($all_locations as $loc): ?>
                        <option value="<?= esc($loc) ?>" <?= old('origin') === $loc ? 'selected' : '' ?>>
                            <?= esc($loc) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="form-text text-muted">Only locations already in the database are shown.</div>
            </div>

            <div class="mb-3">
                <label for="destination" class="form-label fw-semibold">Destination</label>
                <select class="form-select" id="destination" name="destination" required>
                    <option value="">— Select Destination —</option>
                    <?php foreach ($all_locations as $loc): ?>
                        <option value="<?= esc($loc) ?>" <?= old('destination') === $loc ? 'selected' : '' ?>>
                            <?= esc($loc) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="fare" class="form-label fw-semibold">Fare (PHP)</label>
                <div class="input-group">
                    <span class="input-group-text">₱</span>
                    <input type="number" step="0.01" class="form-control" id="fare" name="fare"
                           value="<?= old('fare') ?>" min="1" placeholder="e.g. 150.00" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold d-block">Vehicle Type</label>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="vehicle_type" id="type_van" value="van"
                           <?= old('vehicle_type', 'van') == 'van' ? 'checked' : '' ?>>
                    <label class="form-check-label" for="type_van">🚐 Van</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="vehicle_type" id="type_jeepney" value="jeepney"
                           <?= old('vehicle_type') == 'jeepney' ? 'checked' : '' ?>>
                    <label class="form-check-label" for="type_jeepney">🚌 Jeepney</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="vehicle_type" id="type_minibus" value="minibus"
                           <?= old('vehicle_type') == 'minibus' ? 'checked' : '' ?>>
                    <label class="form-check-label" for="type_minibus">🚍 Mini Bus</label>
                </div>
            </div>

            <div class="mb-3">
                <label for="terminal_id" class="form-label fw-semibold">Terminal</label>
                <select class="form-select" id="terminal_id" name="terminal_id" required>
                    <option value="">Select Terminal</option>
                    <?php foreach ($terminals as $terminal): ?>
                        <option value="<?= $terminal['id'] ?>"
                                <?= old('terminal_id') == $terminal['id'] ? 'selected' : '' ?>>
                            <?= esc($terminal['name']) ?> (<?= esc($terminal['location']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save"></i> Save Route
            </button>
        </form>
    </div>
</div>

<?= view('templates/footer') ?>
