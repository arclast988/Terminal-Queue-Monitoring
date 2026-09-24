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

.card, .card-body {
    overflow: visible !important;
}

/* Seamless input + button union for time input */
.military-time-wrap {
    position: relative;
    display: flex;
    align-items: stretch;
    width: 100%;
}

.military-time-wrap .input-modern {
    flex: 1 1 auto;
    min-width: 0;
    font-variant-numeric: tabular-nums;
    font-weight: 600;
    letter-spacing: 0.05em;
    border-top-right-radius: 0 !important;
    border-bottom-right-radius: 0 !important;
}

.military-time-btn {
    width: 46px;
    min-width: 46px;
    border: 1.5px solid #cbd5e1;
    border-left: none;
    border-top-right-radius: var(--radius-md, 0.5rem);
    border-bottom-right-radius: var(--radius-md, 0.5rem);
    background: #f8fafc;
    color: #475569;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.15s ease-in-out;
    font-size: 1.1rem;
}

.military-time-btn:hover {
    background: var(--primary, #1565C0);
    border-color: var(--primary, #1565C0);
    color: #fff;
}

.military-time-wrap:focus-within .military-time-btn {
    border-color: #3b82f6;
}

/* Clean, balanced Time Picker Popover */
.military-time-popover {
    display: none;
    position: fixed;
    width: 280px;
    background: #ffffff;
    border: 1px solid #cbd5e1;
    border-radius: 0.75rem;
    box-shadow: 0 14px 32px -4px rgba(0, 0, 0, 0.18), 0 4px 12px -2px rgba(0, 0, 0, 0.08);
    z-index: 99999;
    padding: 0.85rem 1rem;
    box-sizing: border-box;
}

.military-time-popover.open {
    display: block;
    animation: tpFadeIn 0.15s ease-out;
}

@keyframes tpFadeIn {
    from { opacity: 0; transform: translateY(-4px); }
    to { opacity: 1; transform: translateY(0); }
}

.tp-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 0.65rem;
    padding-bottom: 0.4rem;
    border-bottom: 1px solid #f1f5f9;
}

.tp-title {
    font-size: 0.8rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #475569;
}

.tp-close-btn {
    background: none;
    border: none;
    font-size: 1.25rem;
    line-height: 1;
    color: #94a3b8;
    cursor: pointer;
    padding: 0 4px;
    transition: color 0.15s;
}

.tp-close-btn:hover {
    color: #0f172a;
}

.tp-picker-body {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.65rem;
    margin-bottom: 0.75rem;
}

.tp-col {
    display: flex;
    flex-direction: column;
    align-items: center;
    width: 72px;
}

.tp-arrow-btn {
    width: 100%;
    height: 28px;
    border: 1px solid #e2e8f0;
    background: #f8fafc;
    border-radius: 0.375rem;
    color: #475569;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 0.85rem;
    transition: all 0.12s;
}

.tp-arrow-btn:hover {
    background: #e2e8f0;
    color: #0f172a;
}

.tp-val-input {
    width: 100%;
    height: 44px;
    border: 1.5px solid #cbd5e1;
    border-radius: 0.5rem;
    margin: 4px 0;
    text-align: center;
    font-size: 1.35rem;
    font-weight: 700;
    font-variant-numeric: tabular-nums;
    color: #1e293b;
    background: #ffffff;
    outline: none;
    transition: border-color 0.15s, box-shadow 0.15s;
    -moz-appearance: textfield;
}

.tp-val-input::-webkit-inner-spin-button,
.tp-val-input::-webkit-outer-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

.tp-val-input:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 2px rgba(13, 110, 253, 0.18);
}

.tp-col-label {
    font-size: 0.7rem;
    font-weight: 600;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-top: 2px;
}

.tp-colon {
    font-size: 1.6rem;
    font-weight: 800;
    color: #334155;
    margin-bottom: 14px;
    user-select: none;
}

.tp-presets {
    display: flex;
    gap: 6px;
    justify-content: center;
    margin-bottom: 0.75rem;
}

.tp-preset-chip {
    flex: 1;
    padding: 4px 0;
    background: #f1f5f9;
    border: 1px solid #e2e8f0;
    border-radius: 0.375rem;
    font-size: 0.75rem;
    font-weight: 600;
    color: #475569;
    cursor: pointer;
    transition: all 0.12s;
}

.tp-preset-chip:hover {
    background: #e2e8f0;
    color: #0f172a;
}

.tp-set-btn {
    width: 100%;
    height: 38px;
    border: none;
    border-radius: 0.5rem;
    background: var(--primary, #1565C0);
    color: #fff;
    font-size: 0.875rem;
    font-weight: 700;
    cursor: pointer;
    transition: background 0.15s;
}

.tp-set-btn:hover {
    background: var(--primary-dark, #0D47A1);
}
</style>

<?php
$oldWaitMinutes = old('wait_minutes') ?? 30;
$defaultHours = old('wait_hours') ?? (int) floor($oldWaitMinutes / 60);
$defaultMins = old('wait_mins') ?? ($oldWaitMinutes % 60);
$defaultWaitValue = old('wait_duration') ?? sprintf('%02d:%02d', $defaultHours, $defaultMins);
?>

<div class="page-header-modern fade-in">
    <div>
        <h1 class="page-title-modern">
            <i class="bi bi-clock-history"></i> Add Departure Rule
        </h1>
        <p class="text-muted mb-0">Create a new departure schedule rule</p>
    </div>
    <div class="mt-3 mt-md-0">
        <a href="<?= base_url($prefix . '/departure-rules') ?>" class="btn btn-modern btn-modern-outline">
            <i class="bi bi-arrow-left"></i> Back to List
        </a>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
            <!-- Flash Warning/Error Notification -->
            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert-modern alert-modern-danger mb-4">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <div><?= esc(session()->getFlashdata('error')) ?></div>
                </div>
            <?php endif; ?>

            <?php if (session()->getFlashdata('errors')): ?>
                <div class="alert-modern alert-modern-danger mb-4">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <strong>Validation Errors:</strong>
                    <ul class="mb-0 mt-2">
                        <?php foreach ((array)session()->getFlashdata('errors') as $error): ?>
                            <?php if ($error !== session()->getFlashdata('error')): ?>
                                <li><?= esc($error) ?></li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <!-- Live Client-side Contradiction Warning -->
            <div id="rule-contradiction-alert" class="alert-modern alert-modern-danger mb-4" style="display: none;">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <span id="rule-contradiction-msg"></span>
            </div>

            <div class="card-modern">
                <div class="card-header-modern">
                    <span class="card-title-modern"><i class="bi bi-gear me-2"></i> Rule Configuration</span>
                </div>
                <div class="card-body-modern">
                    <form action="<?= base_url($prefix . '/departure-rules/store') ?>" method="post" id="departureRuleForm">
                        <?= csrf_field() ?>

                        <?php $onlyTerminal = (is_array($terminals) && count($terminals) === 1); ?>
                        <div class="mb-4">
                            <label for="terminal_id" class="form-label-modern">Terminal <span class="text-danger">*</span></label>
                            <?php if ($onlyTerminal): ?>
                                <?php $singleTerminal = reset($terminals); ?>
                                <input type="text" class="input-modern" value="<?= esc($singleTerminal['name']) ?>" readonly style="background-color: var(--surface-sunken, #f8fafc); cursor: not-allowed;">
                                <input type="hidden" name="terminal_id" id="terminal_id" value="<?= $singleTerminal['id'] ?>">
                            <?php else: ?>
                                <select class="select-modern" id="terminal_id" name="terminal_id" required>
                                    <option value="">— Select Terminal —</option>
                                    <?php foreach ($terminals as $t): ?>
                                        <option value="<?= $t['id'] ?>" <?= (old('terminal_id') == $t['id']) ? 'selected' : '' ?>>
                                            <?= esc($t['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            <?php endif; ?>
                        </div>

                        <div class="mb-4">
                            <label for="route_id" class="form-label-modern">Destination (Optional)</label>
                            <select class="select-modern" id="route_id" name="route_id">
                                <option value="">— No specific destination —</option>
                                <?php foreach (($routes ?? []) as $r): ?>
                                <option value="<?= $r['id'] ?>" <?= old('route_id', $selectedRouteId ?? '') == $r['id'] ? 'selected' : '' ?>>
                                    <?= strtoupper(esc($r['destination'])) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text-modern">Pick a destination for a route-specific interval, or leave blank for a terminal-wide default rule.</div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label for="time_from" class="form-label-modern">Time From (HH:MM) <span class="text-danger">*</span></label>
                                <div class="military-time-wrap">
                                    <input type="text" class="input-modern military-time-input" id="time_from" name="time_from"
                                        value="<?= old('time_from') ?>" placeholder="05:00" maxlength="5" required autocomplete="off">
                                    <button type="button" class="military-time-btn" data-time-target="time_from" title="Open 24-hour time picker" aria-label="Open time picker for Time From">
                                        <i class="bi bi-clock"></i>
                                    </button>
                                </div>
                                <div class="form-text-modern">24-hour military time format (e.g. 08:00 or 14:30).</div>
                            </div>
                            <div class="col-md-6 mb-4">
                                <label for="time_to" class="form-label-modern">Time To (HH:MM) <span class="text-danger">*</span></label>
                                <div class="military-time-wrap">
                                    <input type="text" class="input-modern military-time-input" id="time_to" name="time_to"
                                        value="<?= old('time_to') ?>" placeholder="09:00" maxlength="5" required autocomplete="off">
                                    <button type="button" class="military-time-btn" data-time-target="time_to" title="Open 24-hour time picker" aria-label="Open time picker for Time To">
                                        <i class="bi bi-clock"></i>
                                    </button>
                                </div>
                                <div class="form-text-modern">24-hour military time format (e.g. 17:00 or 21:00).</div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label for="wait_duration" class="form-label-modern">Wait Time (HH:MM) <span class="text-danger">*</span></label>
                                <div class="military-time-wrap">
                                    <input type="text" class="input-modern military-time-input" id="wait_duration" name="wait_duration"
                                        value="<?= esc($defaultWaitValue) ?>" placeholder="00:30" maxlength="5" required autocomplete="off">
                                    <button type="button" class="military-time-btn" data-time-target="wait_duration" title="Open 24-hour time picker" aria-label="Open time picker for Wait Time">
                                        <i class="bi bi-clock"></i>
                                    </button>
                                </div>
                                <div class="form-text-modern">Hours and minutes in 24-hour format (e.g. 00:30 = 30 mins).</div>
                            </div>
                            <div class="col-md-6 mb-4">
                                <label for="label" class="form-label-modern">Label (Optional)</label>
                                <input type="text" class="input-modern" id="label" name="label" value="<?= old('label') ?>"
                                    placeholder="e.g. Morning Rush">
                                <div class="form-text-modern">Descriptive name for this rule (e.g. Morning Rush, Regular Interval).</div>
                            </div>
                        </div>

                        <div class="d-flex gap-2 mt-5">
                            <button type="submit" class="btn btn-modern btn-modern-primary" id="btn-save-rule">
                                <i class="bi bi-save"></i> Save Rule
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

<?= view('templates/footer') ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const existingRules = <?= json_encode($existingRules ?? []) ?>;
    const currentRuleId = null; // Adding new rule

    const form = document.getElementById('departureRuleForm');
    const timeFromInput = document.getElementById('time_from');
    const timeToInput = document.getElementById('time_to');
    const waitDurationInput = document.getElementById('wait_duration');
    const terminalSelect = document.getElementById('terminal_id');
    const routeSelect = document.getElementById('route_id');
    const alertBox = document.getElementById('rule-contradiction-alert');
    const alertMsg = document.getElementById('rule-contradiction-msg');

    // 1. Text input formatting
    document.querySelectorAll('.military-time-input').forEach(function(input) {
        input.addEventListener('input', function(e) {
            let val = input.value.replace(/[^\d:]/g, '');
            if (!val.includes(':') && val.length === 2 && e.inputType !== 'deleteContentBackward') {
                let hh = parseInt(val, 10);
                if (hh > 23) hh = 23;
                val = String(hh).padStart(2, '0') + ':';
            }
            if (val.length > 5) val = val.slice(0, 5);
            input.value = val;
            checkContradiction();
        });

        input.addEventListener('blur', function() {
            let val = input.value.trim();
            if (!val) return;
            let digits = val.replace(/\D/g, '');

            if (input.id === 'wait_duration') {
                if (digits.length === 1 || digits.length === 2) {
                    let num = parseInt(digits, 10) || 0;
                    if (num <= 59) {
                        input.value = '00:' + String(num).padStart(2, '0');
                    } else {
                        let hh = Math.min(23, Math.floor(num / 60));
                        let mm = num % 60;
                        input.value = String(hh).padStart(2, '0') + ':' + String(mm).padStart(2, '0');
                    }
                } else if (digits.length === 3) {
                    let hh = Math.min(23, parseInt(digits.slice(0, 1), 10) || 0);
                    let mm = Math.min(59, parseInt(digits.slice(1), 10) || 0);
                    input.value = String(hh).padStart(2, '0') + ':' + String(mm).padStart(2, '0');
                } else if (digits.length >= 4) {
                    let hh = Math.min(23, parseInt(digits.slice(0, 2), 10) || 0);
                    let mm = Math.min(59, parseInt(digits.slice(2, 4), 10) || 0);
                    input.value = String(hh).padStart(2, '0') + ':' + String(mm).padStart(2, '0');
                }
            } else {
                if (digits.length === 1 || digits.length === 2) {
                    let hh = Math.min(23, parseInt(digits, 10) || 0);
                    input.value = String(hh).padStart(2, '0') + ':00';
                } else if (digits.length === 3) {
                    let hh = Math.min(23, parseInt(digits.slice(0, 1), 10) || 0);
                    let mm = Math.min(59, parseInt(digits.slice(1), 10) || 0);
                    input.value = String(hh).padStart(2, '0') + ':' + String(mm).padStart(2, '0');
                } else if (digits.length >= 4) {
                    let hh = Math.min(23, parseInt(digits.slice(0, 2), 10) || 0);
                    let mm = Math.min(59, parseInt(digits.slice(2, 4), 10) || 0);
                    input.value = String(hh).padStart(2, '0') + ':' + String(mm).padStart(2, '0');
                }
            }

            checkContradiction();
        });
    });

    // 2. Contradiction Checker Function
    function checkContradiction() {
        if (!timeFromInput || !timeToInput) return null;

        timeFromInput.setCustomValidity('');
        timeToInput.setCustomValidity('');
        if (alertBox) alertBox.style.display = 'none';

        const fromVal = timeFromInput.value.trim();
        const toVal = timeToInput.value.trim();

        if (!fromVal || !toVal) return null;

        const timeRegex = /^(?:[01]\d|2[0-3]):[0-5]\d$/;
        if (fromVal.length === 5 && !timeRegex.test(fromVal)) {
            const msg = 'Time From must be in 24-hour HH:MM format.';
            timeFromInput.setCustomValidity(msg);
            showContradiction(timeFromInput, msg);
            return { input: timeFromInput, msg: msg };
        }
        if (toVal.length === 5 && !timeRegex.test(toVal)) {
            const msg = 'Time To must be in 24-hour HH:MM format.';
            timeToInput.setCustomValidity(msg);
            showContradiction(timeToInput, msg);
            return { input: timeToInput, msg: msg };
        }

        if (fromVal.length === 5 && toVal.length === 5) {
            // Contradiction: Time From >= Time To
            if (fromVal >= toVal) {
                const msg = 'Time From (' + fromVal + ') must be earlier than Time To (' + toVal + ').';
                timeToInput.setCustomValidity(msg);
                showContradiction(timeToInput, msg);
                return { input: timeToInput, msg: msg };
            }

            // Contradiction: Overlap with existing rule in same scope
            const curTerminalId = terminalSelect ? terminalSelect.value : '';
            const curRouteId = routeSelect ? routeSelect.value : '';
            const curFromSec = fromVal + ':00';
            const curToSec = toVal + ':00';

            for (let i = 0; i < existingRules.length; i++) {
                let r = existingRules[i];
                if (currentRuleId && parseInt(r.id, 10) === parseInt(currentRuleId, 10)) {
                    continue;
                }

                let sameScope = false;
                if (curRouteId) {
                    sameScope = (String(r.route_id) === String(curRouteId));
                } else {
                    sameScope = (String(r.terminal_id) === String(curTerminalId) && (!r.route_id || r.route_id === 'null' || r.route_id === ''));
                }

                if (!sameScope) continue;

                let rFrom = r.time_from.length === 5 ? r.time_from + ':00' : r.time_from;
                let rTo = r.time_to.length === 5 ? r.time_to + ':00' : r.time_to;

                if (rFrom < curToSec && rTo > curFromSec) {
                    let labelStr = r.label ? ' (' + r.label + ')' : '';
                    let msg = 'This time range (' + fromVal + ' - ' + toVal + ') overlaps with an existing rule: ' + r.time_from.substring(0, 5) + ' - ' + r.time_to.substring(0, 5) + labelStr + '.';
                    timeToInput.setCustomValidity(msg);
                    showContradiction(timeToInput, msg);
                    return { input: timeToInput, msg: msg };
                }
            }
        }

        return null;
    }

    function showContradiction(inputEl, msg) {
        if (alertBox && alertMsg) {
            alertMsg.textContent = msg;
            alertBox.style.display = 'flex';
        }
    }

    if (terminalSelect) terminalSelect.addEventListener('change', checkContradiction);
    if (routeSelect) routeSelect.addEventListener('change', checkContradiction);

    // Form submit guard
    if (form) {
        form.addEventListener('submit', function(e) {
            let contradiction = checkContradiction();
            if (contradiction) {
                e.preventDefault();
                e.stopPropagation();
                contradiction.input.focus();
                contradiction.input.reportValidity();
                if (alertBox) {
                    alertBox.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }
                return false;
            }
        });
    }

    // 3. Time Picker Popover Creation & Clean Positioning
    let activePopover = null;
    let activeWrap = null;

    document.querySelectorAll('.military-time-wrap').forEach(function(wrap) {
        let input = wrap.querySelector('.military-time-input');
        let btn = wrap.querySelector('.military-time-btn');
        if (!input || !btn) return;

        let popover = document.createElement('div');
        popover.className = 'military-time-popover';

        popover.innerHTML = `
            <div class="tp-header">
                <span class="tp-title"><i class="bi bi-clock me-1"></i> Set Time (24h)</span>
                <button type="button" class="tp-close-btn" aria-label="Close">&times;</button>
            </div>
            <div class="tp-picker-body">
                <div class="tp-col">
                    <button type="button" class="tp-arrow-btn tp-up" data-unit="hour" title="Increase hour"><i class="bi bi-chevron-up"></i></button>
                    <input type="text" class="tp-val-input tp-hour-input" maxlength="2" inputmode="numeric" value="00">
                    <button type="button" class="tp-arrow-btn tp-down" data-unit="hour" title="Decrease hour"><i class="bi bi-chevron-down"></i></button>
                    <span class="tp-col-label">Hours</span>
                </div>
                <div class="tp-colon">:</div>
                <div class="tp-col">
                    <button type="button" class="tp-arrow-btn tp-up" data-unit="min" title="Increase minute"><i class="bi bi-chevron-up"></i></button>
                    <input type="text" class="tp-val-input tp-min-input" maxlength="2" inputmode="numeric" value="00">
                    <button type="button" class="tp-arrow-btn tp-down" data-unit="min" title="Decrease minute"><i class="bi bi-chevron-down"></i></button>
                    <span class="tp-col-label">Mins</span>
                </div>
            </div>
            <div class="tp-presets">
                <button type="button" class="tp-preset-chip" data-min="00">:00</button>
                <button type="button" class="tp-preset-chip" data-min="15">:15</button>
                <button type="button" class="tp-preset-chip" data-min="30">:30</button>
                <button type="button" class="tp-preset-chip" data-min="45">:45</button>
            </div>
            <div class="tp-footer">
                <button type="button" class="tp-set-btn">Apply Time</button>
            </div>
        `;

        document.body.appendChild(popover);

        const hourInput = popover.querySelector('.tp-hour-input');
        const minInput = popover.querySelector('.tp-min-input');
        const setBtn = popover.querySelector('.tp-set-btn');
        const closeBtn = popover.querySelector('.tp-close-btn');

        function syncFromMain() {
            let val = input.value.trim();
            let h = 0, m = 0;
            if (val.includes(':')) {
                let parts = val.split(':');
                h = Math.min(23, Math.max(0, parseInt(parts[0], 10) || 0));
                m = Math.min(59, Math.max(0, parseInt(parts[1], 10) || 0));
            } else if (val) {
                let d = parseInt(val.replace(/\D/g, ''), 10) || 0;
                if (input.id === 'wait_duration') {
                    if (d <= 59) { m = d; } else { h = Math.min(23, Math.floor(d / 60)); m = d % 60; }
                } else {
                    h = Math.min(23, d);
                }
            }
            hourInput.value = String(h).padStart(2, '0');
            minInput.value = String(m).padStart(2, '0');
        }

        function stepHour(delta) {
            let cur = parseInt(hourInput.value, 10) || 0;
            cur = (cur + delta + 24) % 24;
            hourInput.value = String(cur).padStart(2, '0');
        }

        function stepMin(delta) {
            let cur = parseInt(minInput.value, 10) || 0;
            cur = (cur + delta + 60) % 60;
            minInput.value = String(cur).padStart(2, '0');
        }

        popover.querySelectorAll('.tp-arrow-btn').forEach(function(arrBtn) {
            arrBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                let unit = arrBtn.dataset.unit;
                let isUp = arrBtn.classList.contains('tp-up');
                let delta = isUp ? 1 : -1;
                if (unit === 'hour') stepHour(delta);
                else stepMin(delta);
            });
        });

        popover.querySelectorAll('.tp-preset-chip').forEach(function(chip) {
            chip.addEventListener('click', function(e) {
                e.stopPropagation();
                minInput.value = chip.dataset.min;
            });
        });

        hourInput.addEventListener('change', function() {
            let n = parseInt(this.value, 10) || 0;
            if (n < 0) n = 0;
            if (n > 23) n = 23;
            this.value = String(n).padStart(2, '0');
        });

        minInput.addEventListener('change', function() {
            let n = parseInt(this.value, 10) || 0;
            if (n < 0) n = 0;
            if (n > 59) n = 59;
            this.value = String(n).padStart(2, '0');
        });

        // Mouse wheel adjustment over inputs
        hourInput.addEventListener('wheel', function(e) {
            e.preventDefault();
            stepHour(e.deltaY < 0 ? 1 : -1);
        }, { passive: false });

        minInput.addEventListener('wheel', function(e) {
            e.preventDefault();
            stepMin(e.deltaY < 0 ? 1 : -1);
        }, { passive: false });

        function applyTime() {
            let h = String(Math.min(23, Math.max(0, parseInt(hourInput.value, 10) || 0))).padStart(2, '0');
            let m = String(Math.min(59, Math.max(0, parseInt(minInput.value, 10) || 0))).padStart(2, '0');
            input.value = h + ':' + m;
            popover.classList.remove('open');
            activePopover = null;
            activeWrap = null;
            input.dispatchEvent(new Event('input', { bubbles: true }));
            input.dispatchEvent(new Event('change', { bubbles: true }));
            checkContradiction();
        }

        setBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            applyTime();
        });

        closeBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            popover.classList.remove('open');
            activePopover = null;
            activeWrap = null;
        });

        popover.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                applyTime();
            } else if (e.key === 'Escape') {
                popover.classList.remove('open');
                activePopover = null;
                activeWrap = null;
            }
        });

        popover.addEventListener('click', function(e) {
            e.stopPropagation();
        });

        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            if (activePopover && activePopover !== popover) {
                activePopover.classList.remove('open');
            }
            syncFromMain();
            popover.classList.toggle('open');
            if (popover.classList.contains('open')) {
                activePopover = popover;
                activeWrap = wrap;
                positionActivePopover();
                hourInput.focus();
                hourInput.select();
            } else {
                activePopover = null;
                activeWrap = null;
            }
        });
    });

    function positionActivePopover() {
        if (!activePopover || !activeWrap) return;
        let wrapRect = activeWrap.getBoundingClientRect();
        let popW = 280;
        let popH = activePopover.offsetHeight || 220;

        // Align right edge of popover with right edge of wrap
        let left = wrapRect.right - popW;
        if (left < 10) left = wrapRect.left;
        if (left < 10) left = 10;
        if (left + popW > window.innerWidth - 10) {
            left = window.innerWidth - popW - 10;
        }

        // Align vertically
        let spaceBelow = window.innerHeight - wrapRect.bottom - 10;
        if (spaceBelow >= popH) {
            activePopover.style.top = (wrapRect.bottom + 6) + 'px';
        } else {
            activePopover.style.top = Math.max(10, wrapRect.top - popH - 6) + 'px';
        }
        activePopover.style.left = left + 'px';
    }

    window.addEventListener('resize', positionActivePopover);
    window.addEventListener('scroll', positionActivePopover, { passive: true });

    document.addEventListener('click', function() {
        if (activePopover) {
            activePopover.classList.remove('open');
            activePopover = null;
            activeWrap = null;
        }
    });
});
</script>
