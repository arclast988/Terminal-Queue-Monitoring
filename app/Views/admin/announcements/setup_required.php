<?= view('templates/header', ['title' => $title]) ?>

<div class="row mb-3">
    <div class="col-md-12">
        <h2>Announcements</h2>
    </div>
</div>

<div class="alert alert-warning">
    <h5 class="alert-heading"><i class="bi bi-exclamation-triangle"></i> Table not found</h5>
    <p class="mb-0">The <code>announcements</code> table does not exist yet. Create it once using phpMyAdmin (or any MySQL client), then refresh this page.</p>
</div>

<div class="card shadow">
    <div class="card-header bg-secondary text-white">
        <h5 class="mb-0">Run this SQL in phpMyAdmin</h5>
    </div>
    <div class="card-body">
        <p class="text-muted">Open <a href="http://localhost/phpmyadmin" target="_blank">phpMyAdmin</a>, select database <strong>jeepneynvans</strong>, go to the <strong>SQL</strong> tab, paste the code below, and click <strong>Go</strong>.</p>
        <pre class="bg-dark text-light p-3 rounded" style="max-height: 320px; overflow: auto;"><code>CREATE TABLE IF NOT EXISTS `announcements` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `message` text,
  `is_active` tinyint(1) DEFAULT 1,
  `sort_order` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `announcements_is_active_index` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;</code></pre>
        <a href="<?= base_url('admin/announcements') ?>" class="btn btn-primary">Refresh after running SQL</a>
    </div>
</div>

<?= view('templates/footer') ?>
