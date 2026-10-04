<?= view('templates/header', ['title' => $title, 'pageStyles' => ['assets/css/dispatch-controls.css']]) ?>


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
    position: relative !important;
    display: flex !important;
    align-items: stretch !important;
    width: 100% !important;
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
    width: 44px;
    min-width: 44px;
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

/* Compact, touch-friendly Time Picker Popover */
.military-time-popover {
    display: none;
    position: absolute;
    top: calc(100% + 4px);
    right: 0;
    background: #ffffff;
    border: 1px solid #cbd5e1;
    border-radius: 0.75rem;
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.18), 0 4px 10px -2px rgba(0, 0, 0, 0.08);
    z-index: 1050;
    padding: 0.5rem 0.65rem;
    box-sizing: border-box;
}

.military-time-popover.open {
    display: flex;
    align-items: center;
    gap: 0.45rem;
    animation: tpFadeIn 0.15s ease-out;
}

@keyframes tpFadeIn {
    from { opacity: 0; transform: translateY(-4px); }
    to { opacity: 1; transform: translateY(0); }
}

.tp-col {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 3px;
}

.tp-btn {
    width: 54px;
    height: 27px;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    background: #f8fafc;
    color: #475569;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0;
    margin: 0;
    cursor: pointer;
    user-select: none;
    -webkit-user-select: none;
    touch-action: manipulation;
    transition: all 0.12s ease;
}

.tp-btn:hover {
    background: #f1f5f9;
    border-color: #cbd5e1;
    color: #0f172a;
}

.tp-btn:active {
    background: #e2e8f0;
    transform: scale(0.95);
    color: var(--primary, #c62828);
}

.tp-btn i {
    font-size: 13px;
    line-height: 1;
    pointer-events: none;
}

.tp-input {
    width: 54px;
    height: 38px;
    border: 1.5px solid #cbd5e1;
    border-radius: 6px;
    text-align: center;
    font-size: 1.25rem;
    font-weight: 700;
    font-variant-numeric: tabular-nums;
    color: #1e293b;
    background: #ffffff;
    outline: none;
    padding: 0;
    margin: 0;
    box-sizing: border-box;
    transition: border-color 0.15s, box-shadow 0.15s;
    -moz-appearance: textfield;
}

.tp-input::-webkit-inner-spin-button,
.tp-input::-webkit-outer-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

.tp-input:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 2px rgba(13, 110, 253, 0.18);
}

.tp-sep {
    font-size: 1.5rem;
    font-weight: 800;
    color: #334155;
    user-select: none;
    margin: 0 1px;
    line-height: 1;
}

.tp-set-btn {
    align-self: stretch;
    min-height: 98px;
    padding: 0 16px;
    border: none;
    border-radius: 8px;
    background: var(--primary, #c62828);
    color: #ffffff;
    font-size: 0.95rem;
    font-weight: 700;
    cursor: pointer;
    transition: background 0.15s, transform 0.1s;
    white-space: nowrap;
    display: inline-flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 4px;
    user-select: none;
    -webkit-user-select: none;
    touch-action: manipulation;
}

.tp-set-btn:hover {
    background: var(--primary, #b71c1c);
    color: #ffffff;
}

.tp-set-btn:active {
    transform: scale(0.97);
}

.tp-set-btn i {
    font-size: 1.25rem;
    line-height: 1;
}

.tp-set-btn:hover {
    background: var(--primary-dark, #0D47A1);
}

@media (max-width: 768px) {
    .military-time-wrap {
        display: grid !important;
        grid-template-columns: minmax(0, 1fr) 48px;
    }
    .military-time-wrap .input-modern {
        grid-column: 1;
        grid-row: 1;
        width: 100% !important;
        min-width: 0;
    }
    .military-time-btn {
        grid-column: 2;
        grid-row: 1;
        width: 48px;
        min-width: 48px;
    }
    .military-time-popover {
        position: static;
        grid-column: 1 / -1;
        grid-row: 2;
        width: 100%;
        max-width: 100%;
        margin-top: 8px;
        padding: 10px 8px;
    }
    .military-time-popover.open { justify-content: center; gap: 8px; }
    .tp-btn { width: 48px; height: 44px; }
    .military-time-popover .tp-input {
        width: 48px;
        height: 44px;
        min-height: 44px !important;
        padding: 0 !important;
        font-size: 18px !important;
    }
    .tp-set-btn { min-height: 0; padding-inline: 12px; }
}
</style>

<?php
$oldWaitMinutes = old('wait_minutes') ?? ($rule['wait_minutes'] ?? 30);
$defaultHours = old('wait_hours') ?? (int) floor($oldWaitMinutes / 60);
$defaultMins = old('wait_mins') ?? ($oldWaitMinutes % 60);
$defaultWaitValue = old('wait_duration') ?? sprintf('%02d:%02d', $defaultHours, $defaultMins);
?>

<div class="page-header-modern fade-in">
    <div>
        <h1 class="page-title-modern">
            <i class="bi bi-pencil-square"></i> Edit Departure Rule
        </h1>
        <p class="text-muted mb-0">Update departure schedule rule</p>
    </div>
    <div class="mt-3 mt-md-0">
        <a href="<?= esc($listUrl) ?>" class="btn btn-modern btn-modern-outline">
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

            <?= $this->include('partials/flash_notices') ?>

            <div class="card-modern fade-in">
                <div class="card-header-modern">
                    <span class="card-title-modern"><i class="bi bi-gear me-2"></i> Rule Configuration</span>
                </div>
                <div class="card-body-modern">
                    <form action="<?= base_url($prefix . '/departure-rules/update/' . $rule['id']) ?>" method="post" id="departureRuleForm" data-no-change-guard>
                        <?= csrf_field() ?>
                        <input type="hidden" name="return_route" value="<?= esc($returnRoute) ?>">

                        <?php $onlyTerminal = (is_array($terminals) && count($terminals) === 1); ?>
                        <div class="mb-4">
                            <?php if ($onlyTerminal): ?>
                                <?php $singleTerminal = reset($terminals); ?>
                                <label for="terminal_name_display" class="form-label-modern">Terminal <span class="text-danger">*</span></label>
                                <input type="text" id="terminal_name_display" class="input-modern" value="<?= esc($singleTerminal['name']) ?>" readonly style="background-color: var(--surface-sunken, #f8fafc); cursor: not-allowed;">
                                <input type="hidden" name="terminal_id" id="terminal_id" value="<?= $singleTerminal['id'] ?>">
                            <?php else: ?>
                                <label for="terminal_id" class="form-label-modern">Terminal <span class="text-danger">*</span></label>
                                <select class="select-modern" id="terminal_id" name="terminal_id" required>
                                    <option value="">— Select Terminal —</option>
                                    <?php foreach ($terminals as $t): ?>
                                        <option value="<?= $t['id'] ?>" <?= ($onlyTerminal || old('terminal_id', $rule['terminal_id'] ?? '') == $t['id']) ? 'selected' : '' ?>>
                                            <?= esc($t['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            <?php endif; ?>
                        </div>

                        <div class="mb-4">
                            <label for="route_id" class="form-label-modern">Destination</label>
                            <select class="select-modern" id="route_id" name="route_id">
                                <option value="">— No specific destination —</option>
                                <?php foreach (($routes ?? []) as $r): ?>
                                    <option value="<?= $r['id'] ?>" data-round-scope="<?= esc(departure_round_scope((int) $r['terminal_id'], $r['destination']), 'attr') ?>" <?= old('route_id', $rule['route_id'] ?? '') == $r['id'] ? 'selected' : '' ?>>
                                        <?= strtoupper(esc($r['destination'])) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text-modern">Choose a destination, or leave blank for a terminal-wide default.</div>
                        </div>

                        <?= view('admin/departure-rules/day-round-fields', ['rule' => $rule, 'existingRules' => $existingRules ?? []]) ?>

                        <div id="rule-contradiction-alert" class="departure-rule-conflict mb-4" style="display:none" role="alert">
                            <i class="bi bi-exclamation-triangle-fill" aria-hidden="true"></i><span id="rule-contradiction-msg"></span>
                        </div>

                        <?php if (($prefix ?? 'admin') === 'staff'): ?>
                        <?= view('admin/departure-rules/staff-time-fields', ['rule' => $rule, 'defaultWaitValue' => $defaultWaitValue, 'existingRules' => $existingRules ?? []]) ?>
                        <?php else: ?>
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label for="time_from" class="form-label-modern">Time From (HH:MM) <span class="text-danger">*</span></label>
                                <div class="military-time-wrap">
                                    <input type="text" class="input-modern military-time-input" id="time_from" name="time_from"
                                        value="<?= old('time_from') ?? date('H:i', strtotime($rule['time_from'])) ?>" placeholder="05:00" maxlength="5" required autocomplete="off">
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
                                        value="<?= old('time_to') ?? date('H:i', strtotime($rule['time_to'])) ?>" placeholder="09:00" maxlength="5" required autocomplete="off">
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
                                <input type="text" class="input-modern" id="label" name="label"
                                    value="<?= old('label') ?? $rule['label'] ?>" placeholder="e.g. Morning Rush">
                                <div class="form-text-modern">Descriptive name for this rule (e.g. Morning Rush, Regular Interval).</div>
                            </div>
                        </div>

                        <?php endif; ?>

                        <div class="d-flex gap-2 mt-5">
                            <button type="submit" class="btn btn-modern btn-modern-primary" id="btn-update-rule">
                                <i class="bi bi-save"></i> Update Rule
                            </button>
                            <a href="<?= esc($listUrl) ?>" class="btn btn-modern btn-modern-outline">
                                <i class="bi bi-x-circle"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

<?= view('templates/footer') ?>

<?php if (($prefix ?? 'admin') === 'staff'): ?>
<script src="<?= app_asset_url('js/dispatch-rule-times.js') ?>" defer></script>
<?php else: ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const existingRules = <?= json_encode($existingRules ?? []) ?>;
    const currentRuleId = <?= (int)$rule['id'] ?>;

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

            // Contradiction: Overlap with existing rule in same scope (excluding this rule)
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
                const selectedDays = Array.from(document.querySelectorAll('#ruleDays input[name="days_of_week[]"]:checked')).map(box => Number(box.value));
                const existingDays = r.days_of_week ? String(r.days_of_week).split(',').map(Number) : (r.day_of_week ? [Number(r.day_of_week)] : [1,2,3,4,5,6,7]);
                if (String(r.round_number || 1) !== document.getElementById('round_number').value
                    || (selectedDays.length === 7) !== (existingDays.length === 7)
                    || !selectedDays.some(day => existingDays.includes(day))) continue;

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
    document.getElementById('ruleDays').addEventListener('change', checkContradiction);
    document.getElementById('round_number').addEventListener('change', checkContradiction);

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
        btn.setAttribute('aria-expanded', 'false');

        let popover = document.createElement('div');
        popover.className = 'military-time-popover';

        // Hours Column (Up, Input, Down)
        let hhCol = document.createElement('div');
        hhCol.className = 'tp-col';

        let hhUpBtn = document.createElement('button');
        hhUpBtn.type = 'button';
        hhUpBtn.className = 'tp-btn tp-btn-up';
        hhUpBtn.tabIndex = -1;
        hhUpBtn.setAttribute('aria-label', 'Increase hours');
        hhUpBtn.innerHTML = '<i class="bi bi-chevron-up"></i>';
        hhCol.appendChild(hhUpBtn);

        let hhInput = document.createElement('input');
        hhInput.type = 'text';
        hhInput.id = (input.id || 'time') + '_popover_hh';
        hhInput.className = 'tp-input';
        hhInput.maxLength = 2;
        hhInput.inputMode = 'numeric';
        hhInput.placeholder = 'HH';
        hhInput.setAttribute('aria-label', (input.id || 'Time') + ' hours');
        hhCol.appendChild(hhInput);

        let hhDownBtn = document.createElement('button');
        hhDownBtn.type = 'button';
        hhDownBtn.className = 'tp-btn tp-btn-down';
        hhDownBtn.tabIndex = -1;
        hhDownBtn.setAttribute('aria-label', 'Decrease hours');
        hhDownBtn.innerHTML = '<i class="bi bi-chevron-down"></i>';
        hhCol.appendChild(hhDownBtn);

        popover.appendChild(hhCol);

        // Separator
        let sep = document.createElement('span');
        sep.className = 'tp-sep';
        sep.textContent = ':';
        popover.appendChild(sep);

        // Minutes Column (Up, Input, Down)
        let mmCol = document.createElement('div');
        mmCol.className = 'tp-col';

        let mmUpBtn = document.createElement('button');
        mmUpBtn.type = 'button';
        mmUpBtn.className = 'tp-btn tp-btn-up';
        mmUpBtn.tabIndex = -1;
        mmUpBtn.setAttribute('aria-label', 'Increase minutes');
        mmUpBtn.innerHTML = '<i class="bi bi-chevron-up"></i>';
        mmCol.appendChild(mmUpBtn);

        let mmInput = document.createElement('input');
        mmInput.type = 'text';
        mmInput.id = (input.id || 'time') + '_popover_mm';
        mmInput.className = 'tp-input';
        mmInput.maxLength = 2;
        mmInput.inputMode = 'numeric';
        mmInput.placeholder = 'MM';
        mmInput.setAttribute('aria-label', (input.id || 'Time') + ' minutes');
        mmCol.appendChild(mmInput);

        let mmDownBtn = document.createElement('button');
        mmDownBtn.type = 'button';
        mmDownBtn.className = 'tp-btn tp-btn-down';
        mmDownBtn.tabIndex = -1;
        mmDownBtn.setAttribute('aria-label', 'Decrease minutes');
        mmDownBtn.innerHTML = '<i class="bi bi-chevron-down"></i>';
        mmCol.appendChild(mmDownBtn);

        popover.appendChild(mmCol);

        // Set Button
        let setBtn = document.createElement('button');
        setBtn.type = 'button';
        setBtn.className = 'tp-set-btn';
        setBtn.innerHTML = '<i class="bi bi-check2"></i><span>Set</span>';
        popover.appendChild(setBtn);

        wrap.appendChild(popover);

        function stepHour(delta) {
            let cur = parseInt(hhInput.value, 10) || 0;
            cur = (cur + delta + 24) % 24;
            hhInput.value = String(cur).padStart(2, '0');
        }

        function stepMinute(delta) {
            let cur = parseInt(mmInput.value, 10) || 0;
            cur = (cur + delta + 60) % 60;
            mmInput.value = String(cur).padStart(2, '0');
        }

        function setupSpin(btnEl, stepCallback) {
            let timer = null;
            let interval = null;

            function onStart(e) {
                if (e.button && e.button !== 0) return;
                e.preventDefault();
                e.stopPropagation();
                stepCallback();

                timer = setTimeout(function() {
                    interval = setInterval(stepCallback, 80);
                }, 300);
            }

            function onStop(e) {
                if (timer) { clearTimeout(timer); timer = null; }
                if (interval) { clearInterval(interval); interval = null; }
            }

            btnEl.addEventListener('mousedown', onStart);
            btnEl.addEventListener('touchstart', onStart, { passive: false });

            btnEl.addEventListener('mouseup', onStop);
            btnEl.addEventListener('mouseleave', onStop);
            btnEl.addEventListener('touchend', onStop);
            btnEl.addEventListener('touchcancel', onStop);
        }

        setupSpin(hhUpBtn, function() { stepHour(1); });
        setupSpin(hhDownBtn, function() { stepHour(-1); });
        setupSpin(mmUpBtn, function() { stepMinute(1); });
        setupSpin(mmDownBtn, function() { stepMinute(-1); });

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
            hhInput.value = String(h).padStart(2, '0');
            mmInput.value = String(m).padStart(2, '0');
        }

        // Clamp & pad inputs
        hhInput.addEventListener('change', function() {
            let n = parseInt(this.value, 10) || 0;
            if (n < 0) n = 0;
            if (n > 23) n = 23;
            this.value = String(n).padStart(2, '0');
        });

        mmInput.addEventListener('change', function() {
            let n = parseInt(this.value, 10) || 0;
            if (n < 0) n = 0;
            if (n > 59) n = 59;
            this.value = String(n).padStart(2, '0');
        });

        // Mouse wheel adjustment
        hhInput.addEventListener('wheel', function(e) {
            e.preventDefault();
            let cur = parseInt(hhInput.value, 10) || 0;
            cur = (cur + (e.deltaY < 0 ? 1 : -1) + 24) % 24;
            hhInput.value = String(cur).padStart(2, '0');
        }, { passive: false });

        mmInput.addEventListener('wheel', function(e) {
            e.preventDefault();
            let cur = parseInt(mmInput.value, 10) || 0;
            cur = (cur + (e.deltaY < 0 ? 1 : -1) + 60) % 60;
            mmInput.value = String(cur).padStart(2, '0');
        }, { passive: false });

        function applyTime() {
            let h = String(Math.min(23, Math.max(0, parseInt(hhInput.value, 10) || 0))).padStart(2, '0');
            let m = String(Math.min(59, Math.max(0, parseInt(mmInput.value, 10) || 0))).padStart(2, '0');
            input.value = h + ':' + m;
            popover.classList.remove('open');
            btn.setAttribute('aria-expanded', 'false');
            activePopover = null;
            input.dispatchEvent(new Event('input', { bubbles: true }));
            input.dispatchEvent(new Event('change', { bubbles: true }));
            checkContradiction();
        }

        setBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            applyTime();
        });

        popover.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                applyTime();
            } else if (e.key === 'Escape') {
                popover.classList.remove('open');
                btn.setAttribute('aria-expanded', 'false');
                activePopover = null;
            }
        });

        popover.addEventListener('click', function(e) {
            e.stopPropagation();
        });

        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            if (activePopover && activePopover !== popover) {
                activePopover.classList.remove('open');
                activePopover.parentElement.querySelector('.military-time-btn')?.setAttribute('aria-expanded', 'false');
            }
            syncFromMain();
            popover.classList.toggle('open');
            btn.setAttribute('aria-expanded', String(popover.classList.contains('open')));
            if (popover.classList.contains('open')) {
                activePopover = popover;
                // Keep the mobile keyboard closed until a time field is tapped.
                if (e.detail === 0 || window.matchMedia('(pointer: fine)').matches) {
                    hhInput.focus();
                    hhInput.select();
                }
            } else {
                activePopover = null;
            }
        });
    });

    document.addEventListener('click', function() {
        if (activePopover) {
            activePopover.classList.remove('open');
            activePopover.parentElement.querySelector('.military-time-btn')?.setAttribute('aria-expanded', 'false');
            activePopover = null;
        }
    });
});
</script>

<?php endif; ?>
