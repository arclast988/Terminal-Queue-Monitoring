<?= view('templates/header', ['title' => $title]) ?>

<div class="row mb-3">
    <div class="col-md-6">
        <h2>Edit Departure Rule</h2>
    </div>
    <div class="col-md-6 text-end">
        <a href="<?= base_url($prefix . '/departure-rules') ?>" class="btn btn-secondary">
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

        <form action="<?= base_url($prefix . '/departure-rules/update/' . $rule['id']) ?>" method="post">
            <?= csrf_field() ?>

            <?php $onlyTerminal = (is_array($terminals) && count($terminals) === 1); ?>
            <div class="mb-3">
                <label for="terminal_id" class="form-label">Terminal</label>
                <select class="form-select" id="terminal_id" name="terminal_id" required>
                    <?php if (!$onlyTerminal): ?>
                        <option value="">— Select Terminal —</option>
                    <?php endif; ?>
                    <?php foreach ($terminals as $t): ?>
                        <option value="<?= $t['id'] ?>" <?= ($onlyTerminal || old('terminal_id', $rule['terminal_id'] ?? '') == $t['id']) ? 'selected' : '' ?>>
                            <?= esc($t['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="time_from" class="form-label">Time From</label>
                    <input type="time" class="form-control" id="time_from" name="time_from" value="<?= old('time_from') ?? date('H:i', strtotime($rule['time_from'])) ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="time_to" class="form-label">Time To</label>
                    <input type="time" class="form-control" id="time_to" name="time_to" value="<?= old('time_to') ?? date('H:i', strtotime($rule['time_to'])) ?>" required>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="wait_minutes" class="form-label">Wait Time (Minutes)</label>
                    <input type="number" class="form-control" id="wait_minutes" name="wait_minutes" value="<?= old('wait_minutes') ?? $rule['wait_minutes'] ?>" min="1" max="180" required>
                    <small class="text-muted">How many minutes a vehicle waits after arrival before departing.</small>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="label" class="form-label">Label (Optional)</label>
                    <input type="text" class="form-control" id="label" name="label" value="<?= old('label') ?? $rule['label'] ?>" placeholder="e.g. Morning Rush">
                </div>
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save"></i> Update Rule
            </button>
        </form>
    </div>
</div>

<?= view('templates/footer') ?>
