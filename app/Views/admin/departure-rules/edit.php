<?= view('templates/header', ['title' => $title]) ?>

<!-- Modern Frontend Styles -->
<link rel="stylesheet" href="<?= base_url('assets/css/modern-frontend.css') ?>">

<style>
/* Button sizing (always applies) */
.card-modern a.btn-modern-outline,
.page-header-modern a.btn-modern-outline {
    padding: 10px 20px !important;
    font-size: 14px !important;
}
/* Dark mode only */
@media (prefers-color-scheme: dark) {
    .card-modern a.btn-modern-outline,
    .page-header-modern a.btn-modern-outline,
    .card-modern a.btn-modern-outline .bi,
    .card-modern a.btn-modern-outline i,
    .page-header-modern a.btn-modern-outline .bi,
    .page-header-modern a.btn-modern-outline i {
        background: #334155 !important;
        border-color: #475569 !important;
        color: #f1f5f9 !important;
        -webkit-text-fill-color: #f1f5f9 !important;
    }
    .card-modern a.btn-modern-outline:hover,
    .page-header-modern a.btn-modern-outline:hover,
    .card-modern a.btn-modern-outline:hover .bi,
    .card-modern a.btn-modern-outline:hover i,
    .page-header-modern a.btn-modern-outline:hover .bi,
    .page-header-modern a.btn-modern-outline:hover i {
        background: #475569 !important;
        border-color: #64748b !important;
        color: #fff !important;
        -webkit-text-fill-color: #fff !important;
    }
}
</style>

<?php
$oldWaitMinutes = old('wait_minutes') ?? ($rule['wait_minutes'] ?? 30);
$defaultHours = old('wait_hours') ?? (int) floor($oldWaitMinutes / 60);
$defaultMins = old('wait_mins') ?? ($oldWaitMinutes % 60);
$defaultWaitValue = old('wait_duration') ?? sprintf('%02d:%02d', $defaultHours, $defaultMins);
?>

<div class="page-header-modern">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="page-title-modern">
                    <i class="bi bi-pencil-square"></i> Edit Departure Rule
                </h1>
                <p class="text-muted mb-0">Update departure schedule rule</p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <a href="<?= base_url($prefix . '/departure-rules') ?>" class="btn btn-modern btn-modern-outline">
                    <i class="bi bi-arrow-left"></i> Back to List
                </a>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <?php if (session()->getFlashdata('errors')): ?>
                <div class="alert-modern alert-modern-danger mb-4">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <strong>Validation Errors:</strong>
                    <ul class="mb-0 mt-2">
                        <?php foreach (session()->getFlashdata('errors') as $error): ?>
                            <li><?= esc($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="card-modern">
                <div class="card-header-modern">
                    <i class="bi bi-gear me-2"></i> Rule Configuration
                </div>
                <div class="card-body-modern">
                    <form action="<?= base_url($prefix . '/departure-rules/update/' . $rule['id']) ?>" method="post">
                        <?= csrf_field() ?>

                        <?php $onlyTerminal = (is_array($terminals) && count($terminals) === 1); ?>
                        <div class="mb-4">
                            <label for="terminal_id" class="form-label-modern">Terminal <span class="text-danger">*</span></label>
                            <select class="select-modern" id="terminal_id" name="terminal_id" required>
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

                        <div class="mb-4">
                            <label for="route_id" class="form-label-modern">Destination (Optional)</label>
                            <select class="select-modern" id="route_id" name="route_id">
                                <option value="">— No specific destination —</option>
                                <?php foreach (($routes ?? []) as $r): ?>
                                    <option value="<?= $r['id'] ?>" <?= old('route_id', $rule['route_id'] ?? '') == $r['id'] ? 'selected' : '' ?>>
                                        <?= esc($r['destination']) ?> (<?= esc(ucfirst($r['vehicle_type'])) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text-modern">Pick a destination for a route-specific interval, or leave blank for a terminal-wide default rule.</div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label for="time_from" class="form-label-modern">Time From <span class="text-danger">*</span></label>
                                <div class="time-shortcut-wrap">
                                    <input type="text" class="input-modern time-24-input" id="time_from" name="time_from"
                                        value="<?= old('time_from') ?? date('H:i', strtotime($rule['time_from'])) ?>"
                                        placeholder="HH:MM" inputmode="numeric" maxlength="5"
                                        pattern="(?:[01]\d|2[0-3]):[0-5]\d" required>
                                    <button type="button" class="time-shortcut-btn" data-time-shortcut="time_from" title="Pick time">
                                        <i class="bi bi-clock"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-6 mb-4">
                                <label for="time_to" class="form-label-modern">Time To <span class="text-danger">*</span></label>
                                <div class="time-shortcut-wrap">
                                    <input type="text" class="input-modern time-24-input" id="time_to" name="time_to"
                                        value="<?= old('time_to') ?? date('H:i', strtotime($rule['time_to'])) ?>"
                                        placeholder="HH:MM" inputmode="numeric" maxlength="5"
                                        pattern="(?:[01]\d|2[0-3]):[0-5]\d" required>
                                    <button type="button" class="time-shortcut-btn" data-time-shortcut="time_to" title="Pick time">
                                        <i class="bi bi-clock"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label for="wait_duration" class="form-label-modern">Wait Time <span class="text-danger">*</span></label>
                                <div class="time-shortcut-wrap">
                                    <input type="text" class="input-modern time-24-input" id="wait_duration" name="wait_duration"
                                        value="<?= esc($defaultWaitValue) ?>" placeholder="HH:MM" inputmode="numeric"
                                        maxlength="5" pattern="(?:[01]?\d|2[0-3]):[0-5]\d" required>
                                    <button type="button" class="time-shortcut-btn" data-time-shortcut="wait_duration" title="Pick wait time">
                                        <i class="bi bi-clock"></i>
                                    </button>
                                </div>
                                <div class="form-text-modern">Hours and minutes the vehicle waits before departing.</div>
                            </div>
                            <div class="col-md-6 mb-4">
                                <label for="label" class="form-label-modern">Label (Optional)</label>
                                <input type="text" class="input-modern" id="label" name="label"
                                    value="<?= old('label') ?? $rule['label'] ?>" placeholder="e.g. Morning Rush">
                            </div>
                        </div>

                        <div class="d-flex gap-2 mt-5">
                            <button type="submit" class="btn btn-modern btn-modern-primary">
                                <i class="bi bi-save"></i> Update Rule
                            </button>
                            <a href="<?= base_url($prefix . '/departure-rules') ?>" class="btn btn-modern btn-modern-outline">
                                <i class="bi bi-x-circle"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?= view('templates/footer') ?>

<style>
    .card, .card-body {
        overflow: visible !important;
    }

    .time-shortcut-wrap {
        display: flex;
        align-items: stretch;
        gap: 0.5rem;
    }

    .time-shortcut-wrap .input-modern {
        flex: 1;
        min-width: 0;
        font-variant-numeric: tabular-nums;
    }

    .time-shortcut-btn {
        width: 42px;
        border: 1px solid #cbd5e1;
        border-radius: 0.375rem;
        background: #fff;
        color: #475569;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
    }

    .time-shortcut-btn:hover,
    .time-shortcut-btn:focus {
        border-color: #b91c1c;
        color: #b91c1c;
        outline: none;
    }

    .time-shortcut-panel {
        display: none;
        position: fixed;
        z-index: 2000;
        gap: 0.5rem;
        align-items: center;
        background: #fff;
        border: 1px solid #cbd5e1;
        border-radius: 0.375rem;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.18);
        padding: 0.65rem;
    }

    .time-shortcut-panel.open {
        display: flex;
    }

    .time-shortcut-panel select {
        border: 1px solid #cbd5e1;
        border-radius: 0.375rem;
        padding: 0.35rem 0.45rem;
        font-variant-numeric: tabular-nums;
    }

    .time-shortcut-panel button {
        border: 0;
        border-radius: 0.375rem;
        background: #b91c1c;
        color: #fff;
        font-weight: 600;
        padding: 0.4rem 0.65rem;
    }

    /* Duration Picker - mimics native time input */
    .dp-wrap-modern {
        display: flex;
        align-items: center;
        cursor: default;
        user-select: none;
        padding: 0.375rem 0.75rem;
        gap: 0;
        position: relative;
        min-height: 38px;
    }

    .dp-wrap-modern:focus {
        outline: none;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, .25);
        border-color: #86b7fe;
    }

    .dp-seg-modern {
        display: inline-block;
        min-width: 22px;
        text-align: center;
        border-radius: 3px;
        padding: 0 2px;
        font-variant-numeric: tabular-nums;
        color: #212529;
    }

    .dp-seg-modern.active {
        background: #0d6efd;
        color: #fff;
        border-radius: 3px;
    }

    .dp-seg-modern[data-empty="true"] {
        color: #6c757d;
    }

    .dp-colon-modern {
        padding: 0 1px;
        color: #212529;
    }

    .dp-clock-icon-modern {
        margin-left: auto;
        color: #6c757d;
        font-size: 1rem;
        pointer-events: auto;
        cursor: pointer;
    }

    .dp-panel-modern {
        display: none;
        position: absolute;
        top: 100%;
        right: 0;
        width: 140px;
        height: 200px;
        background: #fff;
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        z-index: 1000;
        padding: 0.5rem 0.25rem;
        gap: 0.25rem;
        overflow: hidden;
    }

    .dp-panel-modern.open {
        display: flex !important;
    }

    .dp-col-modern {
        flex: 1;
        display: flex;
        flex-direction: column;
        min-width: 0;
        align-items: center;
    }

    .dp-col-modern-label {
        font-weight: 600;
        font-size: 0.7rem;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        padding-bottom: 0.25rem;
        border-bottom: 1px solid #dee2e6;
        margin-bottom: 0.25rem;
        width: 100%;
        text-align: center;
    }

    .dp-col-modern-items {
        display: flex;
        flex-direction: column;
        gap: 2px;
        overflow-y: auto;
        flex: 1;
        width: 100%;
        scrollbar-width: thin;
        scrollbar-color: #cbd5e1 transparent;
    }

    .dp-col-modern-items::-webkit-scrollbar {
        width: 4px;
    }

    .dp-col-modern-items::-webkit-scrollbar-track {
        background: transparent;
    }

    .dp-col-modern-items::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 2px;
    }

    .dp-item-modern {
        padding: 0.25rem 0;
        margin: 0 0.125rem;
        border-radius: 0.25rem;
        cursor: pointer;
        font-size: 0.875rem;
        text-align: center;
        user-select: none;
        font-variant-numeric: tabular-nums;
        color: #212529;
        width: calc(100% - 0.25rem);
        display: block;
    }

    .dp-item-modern:hover {
        background: #e9ecef;
    }

    .dp-item-modern.selected {
        background: #0d6efd;
        color: #fff;
        font-weight: 600;
    }

    /* Time Input Wrapper for consistency */
    .time-input-wrap-modern {
        position: relative;
        display: flex;
        align-items: center;
    }

    .time-input-wrap-modern input[type="time"] {
        padding-right: 2.5rem;
    }

    .time-input-wrap-modern input[type="time"]::-webkit-calendar-picker-indicator {
        display: none;
        -webkit-appearance: none;
    }

    .time-input-wrap-modern .time-icon {
        position: absolute;
        right: 0.75rem;
        color: #6c757d;
        font-size: 1rem;
        cursor: pointer;
        z-index: 2;
    }
</style>

<script>
    (function () {
        function pad(value) {
            return String(value).padStart(2, '0');
        }

        function normalizeInput(input) {
            const digits = input.value.replace(/\D/g, '');
            if (digits.length === 3 || digits.length === 4) {
                const padded = digits.padStart(4, '0');
                const hours = Number(padded.slice(0, 2));
                const mins = Number(padded.slice(2));
                if (hours <= 23 && mins <= 59) {
                    input.value = `${pad(hours)}:${pad(mins)}`;
                }
            }
        }

        function buildOptions(select, max) {
            select.innerHTML = '';
            for (let value = 0; value <= max; value++) {
                const option = document.createElement('option');
                option.value = pad(value);
                option.textContent = pad(value);
                select.appendChild(option);
            }
        }

        const panel = document.createElement('div');
        panel.className = 'time-shortcut-panel';
        panel.innerHTML = '<select aria-label="Hour"></select><span>:</span><select aria-label="Minute"></select><button type="button">Set</button>';
        document.body.appendChild(panel);

        const hourSelect = panel.querySelector('select:first-child');
        const minuteSelect = panel.querySelector('select:nth-of-type(2)');
        const setButton = panel.querySelector('button');
        let activeInput = null;
        buildOptions(hourSelect, 23);
        buildOptions(minuteSelect, 59);

        document.querySelectorAll('.time-24-input').forEach((input) => {
            input.addEventListener('blur', () => normalizeInput(input));
        });

        document.querySelectorAll('[data-time-shortcut]').forEach((button) => {
            button.addEventListener('click', (event) => {
                event.stopPropagation();
                activeInput = document.getElementById(button.dataset.timeShortcut);
                if (!activeInput) return;

                const match = activeInput.value.match(/^(\d{1,2}):(\d{2})$/);
                hourSelect.value = match ? pad(Math.min(Number(match[1]), 23)) : '00';
                minuteSelect.value = match ? pad(Math.min(Number(match[2]), 59)) : '00';

                const rect = button.getBoundingClientRect();
                panel.style.top = `${rect.bottom + 6}px`;
                panel.style.left = `${Math.min(rect.left, window.innerWidth - 190)}px`;
                panel.classList.add('open');
            });
        });

        setButton.addEventListener('click', (event) => {
            event.stopPropagation();
            if (activeInput) {
                activeInput.value = `${hourSelect.value}:${minuteSelect.value}`;
                activeInput.focus();
            }
            panel.classList.remove('open');
        });

        panel.addEventListener('click', (event) => event.stopPropagation());
        document.addEventListener('click', () => panel.classList.remove('open'));
    })();

    (function () {
        function setupDurationPicker(wrapId, hidH, hidM, panelId, hoursContainerId, minsContainerId, initH, initM) {
            const wrap = document.getElementById(wrapId);
            if (!wrap) return;
            const segH = wrap.querySelector('[data-seg="h"]');
            const segM = wrap.querySelector('[data-seg="m"]');
            const inpH = document.getElementById(hidH);
            const inpM = document.getElementById(hidM);
            const panel = document.getElementById(panelId);
            const hoursContainer = document.getElementById(hoursContainerId);
            const minsContainer = document.getElementById(minsContainerId);
            if (!segH || !segM || !inpH || !inpM || !panel || !hoursContainer || !minsContainer) return;

            let hVal = initH;
            let mVal = initM;
            let active = null; // 'h' or 'm'
            let typed = '';

            function render() {
                segH.textContent = (hVal === null) ? '--' : String(hVal).padStart(2, '0');
                segM.textContent = (mVal === null) ? '--' : String(mVal).padStart(2, '0');
                segH.dataset.empty = (hVal === null) ? 'true' : 'false';
                segM.dataset.empty = (mVal === null) ? 'true' : 'false';
                inpH.value = (hVal === null) ? '' : hVal;
                inpM.value = (mVal === null) ? '' : mVal;

                // Sync panel selections
                hoursContainer.querySelectorAll('.dp-item-modern').forEach((item, idx) => {
                    item.classList.toggle('selected', idx === hVal);
                });
                minsContainer.querySelectorAll('.dp-item-modern').forEach((item, idx) => {
                    item.classList.toggle('selected', idx === mVal);
                });
            }

            function setActive(seg) {
                active = seg;
                typed = '';
                segH.classList.toggle('active', seg === 'h');
                segM.classList.toggle('active', seg === 'm');
            }

            function clearActive() {
                active = null; typed = '';
                segH.classList.remove('active');
                segM.classList.remove('active');
            }

            // Generate hours (00-23)
            hoursContainer.innerHTML = '';
            for (let h = 0; h < 24; h++) {
                const item = document.createElement('div');
                item.className = 'dp-item';
                item.textContent = String(h).padStart(2, '0');
                item.addEventListener('click', function (e) {
                    e.stopPropagation();
                    hVal = h;
                    render();
                });
                hoursContainer.appendChild(item);
            }

            // Generate minutes (00-59)
            minsContainer.innerHTML = '';
            for (let m = 0; m < 60; m++) {
                const item = document.createElement('div');
                item.className = 'dp-item';
                item.textContent = String(m).padStart(2, '0');
                item.addEventListener('click', function (e) {
                    e.stopPropagation();
                    mVal = m;
                    render();
                    closePanel();
                });
                minsContainer.appendChild(item);
            }

            function openPanel() {
                panel.classList.add('open');
                render();
                // Scroll to selected items
                const hItems = hoursContainer.querySelectorAll('.dp-item-modern');
                const mItems = minsContainer.querySelectorAll('.dp-item-modern');
                if (hVal !== null && hItems[hVal]) hItems[hVal].scrollIntoView({ block: 'nearest' });
                if (mVal !== null && mItems[mVal]) mItems[mVal].scrollIntoView({ block: 'nearest' });
            }

            function closePanel() {
                panel.classList.remove('open');
            }

            // Toggle panel click handler on clock icon
            const icon = wrap.querySelector('.dp-clock-icon-modern');
            if (icon) {
                icon.addEventListener('click', function (e) {
                    e.stopPropagation();
                    if (panel.classList.contains('open')) {
                        closePanel();
                    } else {
                        openPanel();
                    }
                });
            }

            // Close on outside click
            document.addEventListener('click', function (e) {
                if (!wrap.contains(e.target) && !panel.contains(e.target)) {
                    closePanel();
                }
            });

            // Click segments
            segH.addEventListener('mousedown', function (e) { e.preventDefault(); wrap.focus(); setActive('h'); });
            segM.addEventListener('mousedown', function (e) { e.preventDefault(); wrap.focus(); setActive('m'); });
            wrap.addEventListener('mousedown', function (e) {
                if (e.target === wrap) { wrap.focus(); if (!active) setActive('h'); }
            });
            wrap.addEventListener('blur', clearActive);

            wrap.addEventListener('keydown', function (e) {
                if (!active) { setActive('h'); }

                if (e.key === 'Tab') { clearActive(); return; }
                e.preventDefault();

                if (e.key === 'ArrowRight' || e.key === ':') {
                    setActive(active === 'h' ? 'm' : 'h');
                    return;
                }
                if (e.key === 'ArrowLeft') {
                    setActive(active === 'h' ? 'm' : 'h');
                    return;
                }
                if (e.key === 'ArrowUp') {
                    if (active === 'h') { hVal = (hVal === null ? 0 : (hVal + 1) % 24); }
                    if (active === 'm') { mVal = (mVal === null ? 0 : (mVal + 1) % 60); }
                    render(); return;
                }
                if (e.key === 'ArrowDown') {
                    if (active === 'h') { hVal = (hVal === null ? 0 : (hVal <= 0 ? 23 : hVal - 1)); }
                    if (active === 'm') { mVal = (mVal === null ? 0 : (mVal <= 0 ? 59 : mVal - 1)); }
                    render(); return;
                }
                if (e.key === 'Backspace' || e.key === 'Delete') {
                    if (active === 'h') hVal = null;
                    if (active === 'm') mVal = null;
                    typed = '';
                    render(); return;
                }

                if (e.key >= '0' && e.key <= '9') {
                    typed += e.key;
                    if (active === 'h') {
                        let n = parseInt(typed, 10);
                        if (n > 23) { typed = e.key; n = parseInt(typed, 10); }
                        hVal = n;
                        if (typed.length >= 2) { typed = ''; setActive('m'); }
                    } else if (active === 'm') {
                        let n = parseInt(typed, 10);
                        if (n > 59) { typed = e.key; n = parseInt(typed, 10); }
                        mVal = n;
                        if (typed.length >= 2) { typed = ''; }
                    }
                    render();
                }
            });

            render();
        }

        // Setup native time pickers triggers with custom clock icon
        document.querySelectorAll('.time-input-wrap-modern').forEach(wrap => {
            const input = wrap.querySelector('input[type="time"]');
            const icon = wrap.querySelector('.time-icon');
            if (input && icon) {
                icon.addEventListener('click', function (e) {
                    e.stopPropagation();
                    try {
                        input.showPicker();
                    } catch (err) {
                        input.focus();
                    }
                });
            }
        });
    })();
</script>
