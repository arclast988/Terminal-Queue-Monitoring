<?= $this->include('templates/header') ?>

<?php
$groups   = $groups ?? [];
$settings = $settings ?? [];
?>

<style>
.content-manager-page {
    width: min(1120px, 100%);
    margin: 24px auto 48px;
    color: var(--text-main, #172033);
}

.content-manager-hero {
    display: grid;
    grid-template-columns: minmax(0, 1fr) auto;
    align-items: center;
    gap: 24px;
    margin-bottom: 16px;
    padding: 22px 24px;
    border-radius: 18px;
    background: linear-gradient(135deg, var(--primary-dark, #7f1212), var(--primary, #b71c1c));
    color: var(--on-primary, #fff) !important;
    box-shadow: 0 14px 30px color-mix(in srgb, var(--primary, #b71c1c) 22%, transparent);
}

.content-manager-hero-main {
    display: grid;
    grid-template-columns: 52px minmax(0, 1fr);
    align-items: center;
    gap: 16px;
    min-width: 0;
}

.content-manager-hero-icon {
    width: 52px;
    height: 52px;
    border-radius: 14px;
    display: grid;
    place-items: center;
    align-self: start;
    background: rgba(255, 255, 255, .16);
    color: var(--on-primary, #fff);
    font-size: 22px;
}

.content-manager-hero h1 {
    margin: 0 0 4px !important;
    color: var(--on-primary, #fff) !important;
    -webkit-text-fill-color: var(--on-primary, #fff) !important;
    font-size: clamp(21px, 2vw, 26px);
    line-height: 1.2;
    font-weight: 800;
}

.content-manager-hero p {
    max-width: 760px;
    margin: 0 0 8px;
    color: color-mix(in srgb, var(--on-primary, #fff) 88%, transparent) !important;
    font-size: 13.5px;
    line-height: 1.5;
}

.content-manager-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    white-space: nowrap;
    padding: 8px 12px;
    border: 1px solid rgba(255, 255, 255, .32);
    border-radius: 999px;
    background: rgba(255, 255, 255, .12);
    color: var(--on-primary, #fff);
    font-size: 12px;
    font-weight: 800;
}

.content-manager-back,
body.admin-theme a.content-manager-back:not(.btn):not(.nav-link) {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    color: var(--on-primary, #fff) !important;
    -webkit-text-fill-color: var(--on-primary, #fff) !important;
    font-size: 12px;
    font-weight: 700;
    text-decoration: none;
    opacity: .9;
}

.content-manager-back:hover {
    color: var(--on-primary, #fff) !important;
    opacity: 1;
    text-decoration: underline;
}

.content-manager-note {
    display: grid;
    grid-template-columns: 20px minmax(0, 1fr);
    gap: 10px;
    align-items: start;
    margin-bottom: 16px;
    padding: 13px 15px;
    border: 1px solid color-mix(in srgb, var(--primary, #b71c1c) 25%, var(--border, #dce4ed));
    border-radius: 12px;
    background: color-mix(in srgb, var(--primary-soft, #fbe9e9) 55%, #fff);
    color: var(--text-main, #1e293b);
    font-size: 13px;
    line-height: 1.5;
}

.content-manager-note i {
    margin-top: 3px;
    color: var(--primary, #b71c1c);
    text-align: center;
}

.content-group {
    margin-bottom: 12px;
    overflow: clip;
    border: 1px solid var(--border, #dce4ed);
    border-radius: 14px;
    background: var(--surface, #fff);
    box-shadow: 0 4px 14px rgba(15, 23, 42, .05);
}

.content-group > summary {
    list-style: none;
    display: grid;
    grid-template-columns: 40px minmax(0, 1fr) 20px;
    align-items: center;
    gap: 13px;
    min-height: 76px;
    padding: 14px 18px;
    cursor: pointer;
    user-select: none;
}

.content-group > summary::-webkit-details-marker { display: none; }
.content-group > summary:hover { background: color-mix(in srgb, var(--primary-soft, #fbe9e9) 30%, transparent); }
.content-group[open] > summary { background: color-mix(in srgb, var(--primary-soft, #fbe9e9) 42%, transparent); }

.content-group-icon {
    width: 40px;
    height: 40px;
    border-radius: 11px;
    display: grid;
    place-items: center;
    background: var(--primary-soft, #fbe9e9);
    color: var(--primary, #b91c1c);
}

.content-group-heading { min-width: 0; }
.content-group-heading strong { display: block; color: var(--text-main, #172033); font-size: 15.5px; line-height: 1.25; }
.content-group-heading small { display: block; margin-top: 4px; color: var(--text-muted, #64748b); line-height: 1.35; }
.content-group-chevron { color: var(--text-faint, #64748b); transition: transform .18s ease; }
.content-group[open] .content-group-chevron { transform: rotate(180deg); }

.content-fields {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 16px;
    padding: 2px 18px 20px;
    border-top: 1px solid var(--border, #edf1f5);
}

.content-field { min-width: 0; padding-top: 16px; }
.content-field.is-body { grid-column: 1 / -1; }
.content-field label { display: flex; justify-content: space-between; gap: 10px; margin-bottom: 7px; color: var(--text-main, #334155); font-size: 12.5px; font-weight: 800; }
.content-field-status { flex: 0 0 auto; color: var(--primary, #15803d); font-size: 11px; font-weight: 700; }

.content-field input,
.content-field textarea {
    width: 100%;
    box-sizing: border-box;
    border: 1px solid var(--border-strong, #cbd5e1);
    border-radius: 10px;
    padding: 11px 12px;
    background: var(--surface-sunken, #fbfdff);
    color: var(--text-main, #172033);
    font: inherit;
    font-size: 13px;
    line-height: 1.55;
    transition: border-color .15s, box-shadow .15s, background-color .15s;
}

.content-field textarea { min-height: 122px; resize: vertical; }
.content-field input:focus,
.content-field textarea:focus { outline: none; border-color: var(--primary, #b91c1c); box-shadow: 0 0 0 3px var(--primary-soft, rgba(185, 28, 28, .1)); background: var(--surface, #fff); }
.content-field-help { display: flex; justify-content: space-between; gap: 10px; margin-top: 6px; color: var(--text-faint, #64748b); font-size: 11px; }

.content-actions {
    display: grid;
    grid-template-columns: minmax(0, 1fr) auto;
    align-items: center;
    gap: 16px;
    margin-top: 18px;
    padding: 13px 15px;
    border: 1px solid var(--border, #dce4ed);
    border-radius: 14px;
    background: var(--surface, #fff);
    box-shadow: 0 8px 22px rgba(15, 23, 42, .08);
}

.content-actions p { margin: 0; color: var(--text-muted, #64748b); font-size: 12px; line-height: 1.4; }
.content-actions p i { margin-right: 4px; color: var(--primary, #b91c1c); }
.content-save { display: inline-flex; align-items: center; justify-content: center; gap: 7px; border: 0; border-radius: 10px; background: var(--primary, #b71c1c); color: var(--on-primary, #fff); padding: 12px 18px; font-weight: 800; white-space: nowrap; box-shadow: 0 5px 14px color-mix(in srgb, var(--primary, #b71c1c) 24%, transparent); }
.content-save:hover { background: var(--primary-dark, #7f1212); color: var(--on-primary, #fff); }

@media (max-width: 720px) {
    .content-manager-page { width: 100%; margin: 14px auto 36px; }
    .content-manager-hero { grid-template-columns: 1fr; gap: 12px; padding: 17px 15px; border-radius: 14px; }
    .content-manager-hero-main { grid-template-columns: 42px minmax(0, 1fr); gap: 12px; }
    .content-manager-hero-icon { width: 42px; height: 42px; border-radius: 11px; font-size: 18px; }
    .content-manager-hero h1 { font-size: 19px; }
    .content-manager-hero p { font-size: 12.5px; }
    .content-manager-badge { display: none; }
    .content-manager-note { padding: 12px; font-size: 12px; }
    .content-group > summary { grid-template-columns: 36px minmax(0, 1fr) 16px; gap: 10px; min-height: 68px; padding: 12px; }
    .content-group-icon { width: 36px; height: 36px; }
    .content-group-heading strong { font-size: 14px; }
    .content-group-heading small { font-size: 11.5px; }
    .content-fields { display: block; padding: 0 12px 16px; }
    .content-field.is-body { grid-column: auto; }
    .content-field label,
    .content-field-help { align-items: flex-start; }
    .content-actions { grid-template-columns: 1fr; padding: 12px; }
    .content-actions p { font-size: 11.5px; }
    .content-save { width: 100%; }
}
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
