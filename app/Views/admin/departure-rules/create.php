<?= view('templates/header', ['title' => $title]) ?>

<?php
$oldWaitMinutes = old('wait_minutes') ?? 30;
$defaultHours = old('wait_hours') ?? (int) floor($oldWaitMinutes / 60);
$defaultMins = old('wait_mins') ?? ($oldWaitMinutes % 60);
$defaultWaitValue = sprintf('%02d:%02d', $defaultHours, $defaultMins);
?>

<div class="row mb-3">
    <div class="col-md-6">
        <h2>Add Departure Rule</h2>
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

        <form action="<?= base_url($prefix . '/departure-rules/store') ?>" method="post">
            <?= csrf_field() ?>

            <?php $onlyTerminal = (is_array($terminals) && count($terminals) === 1); ?>
            <div class="mb-3">
                <label for="terminal_id" class="form-label">Terminal</label>
                <select class="form-select" id="terminal_id" name="terminal_id" required>
                    <?php if (!$onlyTerminal): ?>
                        <option value="">— Select Terminal —</option>
                    <?php endif; ?>
                    <?php foreach ($terminals as $t): ?>
                        <option value="<?= $t['id'] ?>" <?= ($onlyTerminal || old('terminal_id') == $t['id']) ? 'selected' : '' ?>>
                            <?= esc($t['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="route_id" class="form-label">Destination (Optional)</label>
                <select class="form-select" id="route_id" name="route_id">
                    <option value="">— No specific destination —</option>
                    <?php foreach (($routes ?? []) as $r): ?>
                        <option value="<?= $r['id'] ?>" <?= old('route_id') == $r['id'] ? 'selected' : '' ?>>
                            <?= esc($r['destination']) ?> (<?= esc(ucfirst($r['vehicle_type'])) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <small class="text-muted">Pick a destination for a route-specific interval, or leave blank for a terminal-wide default rule.</small>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="time_from" class="form-label">Time From</label>
                    <div class="time-input-wrap">
                        <input type="time" class="form-control" id="time_from" name="time_from"
                            value="<?= old('time_from') ?>" required>
                        <i class="bi bi-clock time-icon"></i>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="time_to" class="form-label">Time To</label>
                    <div class="time-input-wrap">
                        <input type="time" class="form-control" id="time_to" name="time_to"
                            value="<?= old('time_to') ?>" required>
                        <i class="bi bi-clock time-icon"></i>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Wait Time</label>
                    <div class="dp-wrap form-control" id="dp_wrap_create" tabindex="0">
                        <span class="dp-seg" id="dp_h_c" data-seg="h">--</span><span class="dp-colon">:</span><span
                            class="dp-seg" id="dp_m_c" data-seg="m">--</span>
                        <i class="bi bi-clock dp-clock-icon"></i>
                        <input type="hidden" name="wait_hours" id="wait_hours_c" value="<?= $defaultHours ?>">
                        <input type="hidden" name="wait_mins" id="wait_mins_c" value="<?= $defaultMins ?>">
                        <div class="dp-panel" id="dp_panel_c">
                            <div class="dp-col">
                                <div class="dp-col-label">Hours</div>
                                <div class="dp-col-items" id="dp_hours_c"></div>
                            </div>
                            <div class="dp-col">
                                <div class="dp-col-label">Minutes</div>
                                <div class="dp-col-items" id="dp_mins_c"></div>
                            </div>
                        </div>
                    </div>
                    <small class="text-muted">Hours and minutes the vehicle waits before departing.</small>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="label" class="form-label">Label (Optional)</label>
                    <input type="text" class="form-control" id="label" name="label" value="<?= old('label') ?>"
                        placeholder="e.g. Morning Rush">
                </div>
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save"></i> Save Rule
            </button>
        </form>
    </div>
</div>

<?= view('templates/footer') ?>

<style>
    .card, .card-body {
        overflow: visible !important;
    }

    /* Duration Picker - mimics native time input */
    .dp-wrap {
        display: flex;
        align-items: center;
        cursor: default;
        user-select: none;
        padding: 0.375rem 0.75rem;
        gap: 0;
        position: relative;
        min-height: 38px;
    }

    .dp-wrap:focus {
        outline: none;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, .25);
        border-color: #86b7fe;
    }

    .dp-seg {
        display: inline-block;
        min-width: 22px;
        text-align: center;
        border-radius: 3px;
        padding: 0 2px;
        font-variant-numeric: tabular-nums;
        color: #212529;
    }

    .dp-seg.active {
        background: #0d6efd;
        color: #fff;
        border-radius: 3px;
    }

    .dp-seg[data-empty="true"] {
        color: #6c757d;
    }

    .dp-colon {
        padding: 0 1px;
        color: #212529;
    }

    .dp-clock-icon {
        margin-left: auto;
        color: #6c757d;
        font-size: 1rem;
        pointer-events: auto;
        cursor: pointer;
    }

    .dp-panel {
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

    .dp-panel.open {
        display: flex !important;
    }

    .dp-col {
        flex: 1;
        display: flex;
        flex-direction: column;
        min-width: 0;
        align-items: center;
    }

    .dp-col-label {
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

    .dp-col-items {
        display: flex;
        flex-direction: column;
        gap: 2px;
        overflow-y: auto;
        flex: 1;
        width: 100%;
        scrollbar-width: thin;
        scrollbar-color: #cbd5e1 transparent;
    }

    .dp-col-items::-webkit-scrollbar {
        width: 4px;
    }

    .dp-col-items::-webkit-scrollbar-track {
        background: transparent;
    }

    .dp-col-items::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 2px;
    }

    .dp-item {
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

    .dp-item:hover {
        background: #e9ecef;
    }

    .dp-item.selected {
        background: #0d6efd;
        color: #fff;
        font-weight: 600;
    }

    /* Time Input Wrapper for consistency */
    .time-input-wrap {
        position: relative;
        display: flex;
        align-items: center;
    }

    .time-input-wrap input[type="time"] {
        padding-right: 2.5rem;
    }

    .time-input-wrap input[type="time"]::-webkit-calendar-picker-indicator {
        display: none;
        -webkit-appearance: none;
    }

    .time-input-wrap .time-icon {
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
                hoursContainer.querySelectorAll('.dp-item').forEach((item, idx) => {
                    item.classList.toggle('selected', idx === hVal);
                });
                minsContainer.querySelectorAll('.dp-item').forEach((item, idx) => {
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
                const hItems = hoursContainer.querySelectorAll('.dp-item');
                const mItems = minsContainer.querySelectorAll('.dp-item');
                if (hVal !== null && hItems[hVal]) hItems[hVal].scrollIntoView({ block: 'nearest' });
                if (mVal !== null && mItems[mVal]) mItems[mVal].scrollIntoView({ block: 'nearest' });
            }

            function closePanel() {
                panel.classList.remove('open');
            }

            // Toggle panel click handler on clock icon
            const icon = wrap.querySelector('.dp-clock-icon');
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

        // Init form picker
        var initH = <?= (int) $defaultHours ?>;
        var initM = <?= (int) $defaultMins ?>;
        setupDurationPicker('dp_wrap_create', 'wait_hours_c', 'wait_mins_c', 'dp_panel_c', 'dp_hours_c', 'dp_mins_c', initH, initM);

        // Setup native time pickers triggers with custom clock icon
        document.querySelectorAll('.time-input-wrap').forEach(wrap => {
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