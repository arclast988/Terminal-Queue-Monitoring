<?= $this->include('templates/header') ?>

<?php
$groups   = $groups ?? [];
$settings = $settings ?? [];
?>

<style>
.content-manager-page{width:min(1180px,calc(100% - 32px));margin:28px auto 70px;color:#172033}.content-manager-hero{display:flex;align-items:center;justify-content:space-between;gap:24px;padding:24px 28px;border-radius:18px;background:linear-gradient(135deg,#7f1d1d,#b91c1c);color:#fff;box-shadow:0 14px 32px rgba(127,29,29,.2);margin-bottom:20px}.content-manager-hero-main{display:flex;align-items:center;gap:16px;min-width:0}.content-manager-hero-icon{width:54px;height:54px;border-radius:15px;display:grid;place-items:center;background:rgba(255,255,255,.16);font-size:24px;flex:0 0 auto}.content-manager-hero h1{margin:0 0 5px;font-size:25px;font-weight:800}.content-manager-hero p{margin:0;max-width:760px;font-size:13.5px;line-height:1.55;color:rgba(255,255,255,.86)}.content-manager-badge{white-space:nowrap;padding:8px 12px;border:1px solid rgba(255,255,255,.28);border-radius:999px;background:rgba(255,255,255,.12);font-size:12px;font-weight:800}.content-manager-note{display:flex;gap:12px;padding:15px 17px;margin-bottom:18px;border:1px solid #bfdbfe;border-radius:12px;background:#eff6ff;color:#1e3a8a;font-size:13px;line-height:1.55}.content-manager-note i{margin-top:3px}.content-group{margin-bottom:14px;border:1px solid #dce4ed;border-radius:15px;background:#fff;box-shadow:0 5px 18px rgba(15,23,42,.05);overflow:hidden}.content-group>summary{list-style:none;display:flex;align-items:center;gap:13px;padding:18px 20px;cursor:pointer;user-select:none}.content-group>summary::-webkit-details-marker{display:none}.content-group-icon{width:40px;height:40px;border-radius:11px;display:grid;place-items:center;background:#fef2f2;color:#b91c1c;flex:0 0 auto}.content-group-heading{min-width:0;flex:1}.content-group-heading strong{display:block;font-size:16px}.content-group-heading small{display:block;margin-top:3px;color:#64748b;line-height:1.4}.content-group-chevron{color:#64748b;transition:transform .18s ease}.content-group[open] .content-group-chevron{transform:rotate(180deg)}.content-fields{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px;padding:4px 20px 22px;border-top:1px solid #edf1f5}.content-field{padding-top:16px;min-width:0}.content-field.is-body{grid-column:span 2}.content-field label{display:flex;justify-content:space-between;gap:10px;margin-bottom:7px;font-size:12.5px;font-weight:800;color:#334155}.content-field-status{font-size:11px;font-weight:700;color:#15803d}.content-field input,.content-field textarea{width:100%;box-sizing:border-box;border:1px solid #cbd5e1;border-radius:10px;padding:11px 12px;background:#fbfdff;color:#172033;font:inherit;font-size:13px;line-height:1.55;transition:border-color .15s,box-shadow .15s}.content-field textarea{min-height:122px;resize:vertical}.content-field input:focus,.content-field textarea:focus{outline:none;border-color:#b91c1c;box-shadow:0 0 0 3px rgba(185,28,28,.1);background:#fff}.content-field-help{display:flex;justify-content:space-between;gap:10px;margin-top:6px;font-size:11px;color:#64748b}.content-actions{position:sticky;bottom:12px;z-index:5;display:flex;align-items:center;justify-content:space-between;gap:16px;padding:13px 15px;margin-top:20px;border:1px solid #dce4ed;border-radius:14px;background:rgba(255,255,255,.95);box-shadow:0 12px 30px rgba(15,23,42,.16);backdrop-filter:blur(10px)}.content-actions p{margin:0;font-size:12px;color:#64748b}.content-save{border:0;border-radius:10px;background:#15803d;color:#fff;padding:12px 19px;font-weight:800;white-space:nowrap;box-shadow:0 5px 14px rgba(21,128,61,.22)}.content-save:hover{background:#166534}.content-manager-back{display:inline-flex;align-items:center;gap:7px;color:#fff;font-size:12px;font-weight:800;text-decoration:none}.content-manager-back:hover{color:#fff;text-decoration:underline}@media(max-width:720px){.content-manager-page{width:calc(100% - 20px);margin:18px auto 55px}.content-manager-hero{align-items:flex-start;padding:18px 16px}.content-manager-hero-icon{width:44px;height:44px}.content-manager-hero h1{font-size:19px}.content-manager-badge{display:none}.content-group>summary{padding:15px 14px}.content-group-heading strong{font-size:14px}.content-group-heading small{font-size:11.5px}.content-fields{display:block;padding:2px 14px 18px}.content-field.is-body{grid-column:auto}.content-actions{bottom:8px;align-items:stretch;flex-direction:column}.content-actions p{font-size:11.5px}.content-save{width:100%}}
</style>

<main class="content-manager-page">
    <section class="content-manager-hero">
        <div class="content-manager-hero-main">
            <div class="content-manager-hero-icon"><i class="fas fa-pen-to-square"></i></div>
            <div>
                <h1>Support & Help Content Manager</h1>
                <p>Edit the public support information and the help guides for commuters, administrators, and dispatchers without changing their page design.</p>
                <a class="content-manager-back" href="<?= base_url('admin/settings') ?>"><i class="fas fa-arrow-left"></i> Back to System Settings</a>
            </div>
        </div>
        <span class="content-manager-badge"><i class="fas fa-shield-halved"></i> Super Admin Only</span>
    </section>

    <?= view('partials/flash_notices') ?>
    <div class="content-manager-note">
        <i class="fas fa-circle-info"></i>
        <div><strong>Safe editing:</strong> fields accept plain text only. Leave any field blank to keep the built-in wording and its original rich formatting. Only the section you replace changes.</div>
    </div>

    <form method="post" action="<?= base_url('admin/settings/content') ?>" id="managedContentForm">
        <?= csrf_field() ?>
        <?php foreach ($groups as $groupKey => $group): ?>
            <details class="content-group" id="content-<?= esc($groupKey) ?>">
                <summary>
                    <span class="content-group-icon"><i class="fas <?= $groupKey === 'terms' ? 'fa-file-shield' : ($groupKey === 'faq' ? 'fa-circle-question' : 'fa-book-open') ?>"></i></span>
                    <span class="content-group-heading">
                        <strong><?= esc($group['label']) ?></strong>
                        <small><?= esc($group['description']) ?></small>
                    </span>
                    <i class="fas fa-chevron-down content-group-chevron"></i>
                </summary>
                <div class="content-fields">
                    <?php foreach ($group['fields'] as $key => $field): ?>
                        <?php $value = old($key, $settings[$key] ?? ''); ?>
                        <div class="content-field <?= $field['type'] === 'textarea' ? 'is-body' : '' ?>">
                            <label for="<?= esc($key) ?>">
                                <span><?= esc($field['label']) ?></span>
                                <span class="content-field-status"><?= trim((string) $value) === '' ? 'Built-in copy' : 'Custom copy' ?></span>
                            </label>
                            <?php if ($field['type'] === 'textarea'): ?>
                                <textarea id="<?= esc($key) ?>" name="<?= esc($key) ?>" maxlength="5000" placeholder="<?= esc($field['placeholder']) ?>"><?= esc($value) ?></textarea>
                            <?php else: ?>
                                <input id="<?= esc($key) ?>" name="<?= esc($key) ?>" type="text" maxlength="180" value="<?= esc($value) ?>" placeholder="<?= esc($field['placeholder']) ?>">
                            <?php endif; ?>
                            <div class="content-field-help">
                                <span>Clear this field to restore the built-in version.</span>
                                <span class="content-count"></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </details>
        <?php endforeach; ?>

        <div class="content-actions">
            <p><i class="fas fa-clock-rotate-left"></i> Every save is recorded in Activity Logs. Terms changes also update the public policy date.</p>
            <button class="content-save" type="submit"><i class="fas fa-floppy-disk"></i> Save Content Changes</button>
        </div>
    </form>
</main>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var hash = window.location.hash.replace('#', '');
    var requested = hash ? document.getElementById('content-' + hash) : null;
    if (requested) requested.open = true;

    document.querySelectorAll('.content-group').forEach(function (group) {
        group.addEventListener('toggle', function () {
            if (!group.open) return;
            document.querySelectorAll('.content-group[open]').forEach(function (other) {
                if (other !== group) other.open = false;
            });
            history.replaceState(null, '', '#' + group.id.replace('content-', ''));
        });
    });

    document.querySelectorAll('.content-field input, .content-field textarea').forEach(function (field) {
        var wrapper = field.closest('.content-field');
        var status = wrapper.querySelector('.content-field-status');
        var count = wrapper.querySelector('.content-count');
        function refresh() {
            var length = field.value.trim().length;
            status.textContent = length ? 'Custom copy' : 'Built-in copy';
            count.textContent = length + ' / ' + field.maxLength;
        }
        field.addEventListener('input', refresh);
        refresh();
    });
});
</script>

<?= $this->include('templates/footer') ?>
