<?= view('templates/header', ['title' => $title]) ?>

<div class="row mb-3">
    <div class="col-md-6">
        <h2>Edit Terminal</h2>
    </div>
    <div class="col-md-6 text-end">
        <a href="<?= base_url('admin/terminals') ?>" class="btn btn-secondary">
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

        <form action="<?= base_url('admin/terminals/update/'.$terminal['id']) ?>" method="post">
            <?= csrf_field() ?>
            
            <div class="mb-3">
                <label for="name" class="form-label">Terminal Name</label>
                <input type="text" class="form-control" id="name" name="name" value="<?= old('name', $terminal['name']) ?>" required>
            </div>
            
            <div class="mb-3">
                <label for="location" class="form-label">Location</label>
                <input type="text" class="form-control" id="location" name="location" value="<?= old('location', $terminal['location']) ?>" required>
            </div>
            
            <div class="mb-3">
                <label for="capacity" class="form-label">Capacity (Max Vehicles)</label>
                <input type="number" class="form-control" id="capacity" name="capacity" value="<?= old('capacity', $terminal['capacity']) ?>" min="0" required>
            </div>
            
            <button type="submit" class="btn btn-warning">
                <i class="bi bi-pencil-square"></i> Update Terminal
            </button>
        </form>
    </div>
</div>

<?= view('templates/footer') ?>
