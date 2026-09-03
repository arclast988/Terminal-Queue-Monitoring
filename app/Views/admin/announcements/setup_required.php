<?= view('templates/header', ['title' => $title]) ?>

<div class="row mb-3">
    <div class="col-md-12">
        <h2>Announcements</h2>
    </div>
</div>

<div class="alert alert-warning">
    <h5 class="alert-heading"><i class="bi bi-exclamation-triangle"></i> Table not found</h5>
    <p class="mb-0">The <code>announcements</code> table does not exist yet. Create it once using pgAdmin or <code>psql</code>, then refresh this page.</p>
</div>

<div class="card shadow">
    <div class="card-header bg-secondary text-white">
        <h5 class="mb-0">Run this SQL in PostgreSQL</h5>
    </div>
    <div class="card-body">
        <p class="text-muted">Connect to database <strong>jeepneynvans</strong> in pgAdmin or <code>psql</code>, then run the SQL below.</p>
        <pre class="bg-dark text-light p-3 rounded" style="max-height: 320px; overflow: auto;"><code>CREATE TABLE IF NOT EXISTS announcements (
  id SERIAL PRIMARY KEY,
  terminal_id INT NOT NULL DEFAULT 1 REFERENCES terminals(id) ON DELETE CASCADE,
  message TEXT NOT NULL,
  is_active SMALLINT NOT NULL DEFAULT 1,
  priority INT NOT NULL DEFAULT 0,
  sort_order INT NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_announcements_active_sort
  ON announcements (is_active, sort_order);</code></pre>
        <a href="<?= base_url('admin/announcements') ?>" class="btn btn-primary">Refresh after running SQL</a>
    </div>
</div>

<?= view('templates/footer') ?>
