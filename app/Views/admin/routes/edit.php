<?= view('templates/header', ['title' => $title]) ?>

<div class="row mb-3">
    <div class="col-md-6">
        <h2>Edit Route / Fare</h2>
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

        <form action="<?= base_url('admin/routes/update/' . $route['id']) ?>" method="post">
            <?= csrf_field() ?>

            <?php $onlyTerminal = (is_array($terminals) && count($terminals) === 1); ?>
            <div class="mb-3">
                <label for="terminal_id" class="form-label fw-semibold">Terminal</label>
                <select class="form-select" id="terminal_id" name="terminal_id" required>
                    <?php if (!$onlyTerminal): ?>
                        <option value="">Select Terminal</option>
                    <?php endif; ?>
                    <?php foreach ($terminals as $terminal): ?>
                        <option value="<?= $terminal['id'] ?>"
                                data-name="<?= esc($terminal['name']) ?>"
                                <?= ($onlyTerminal || old('terminal_id', $route['terminal_id']) == $terminal['id']) ? 'selected' : '' ?>>
                            <?= esc($terminal['name']) ?> (<?= esc($terminal['location']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="form-text text-muted">
                    <i class="bi bi-info-circle me-1"></i>This terminal is used as the route source.
                </div>
            </div>

            <div class="mb-3">
                <label for="destination" class="form-label fw-semibold">Destination</label>
                <input type="text" class="form-control" id="destination" name="destination"
                       value="<?= old('destination', $route['destination']) ?>" placeholder="e.g. ORMOC, TACLOBAN" required minlength="2" maxlength="100">
                <div class="form-text text-muted">Type the destination city/town name.</div>
            </div>

            <div class="mb-3">
                <label for="fare" class="form-label fw-semibold">Fare (PHP)</label>
                <div class="input-group">
                    <span class="input-group-text">₱</span>
                    <input type="number" step="0.01" class="form-control" id="fare" name="fare"
                           value="<?= old('fare', $route['fare']) ?>" min="1" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold d-block">Vehicle Type</label>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="vehicle_type" id="type_van" value="van"
                           <?= old('vehicle_type', $route['vehicle_type']) == 'van' ? 'checked' : '' ?>>
                    <label class="form-check-label" for="type_van">🚐 Van</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="vehicle_type" id="type_jeepney" value="jeepney"
                           <?= old('vehicle_type', $route['vehicle_type']) == 'jeepney' ? 'checked' : '' ?>>
                    <label class="form-check-label" for="type_jeepney">🚌 Jeepney</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="vehicle_type" id="type_minibus" value="minibus"
                           <?= old('vehicle_type', $route['vehicle_type']) == 'minibus' ? 'checked' : '' ?>>
                    <label class="form-check-label" for="type_minibus">🚍 Mini Bus</label>
                </div>
            </div>

            <button type="submit" class="btn btn-warning">
                <i class="bi bi-pencil-square"></i> Update Route
            </button>
        </form>
    </div>
</div>

<?= view('templates/footer') ?>
