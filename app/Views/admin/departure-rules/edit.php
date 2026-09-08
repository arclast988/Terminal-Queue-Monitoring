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

            <?= $this->include('partials/flash_notices') ?>

            <div class="card-modern">
                <div class="card-header-modern">
                    <i class="bi bi-gear me-2"></i> Rule Configuration
                </div>
                <div class="card-body-modern">
                    <form action="<?= base_url($prefix . '/departure-rules/update/' . $rule['id']) ?>" method="post" data-no-change-guard>
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
                                        <option value="<?= $t['id'] ?>" <?= ($onlyTerminal || old('terminal_id', $rule['terminal_id'] ?? '') == $t['id']) ? 'selected' : '' ?>>
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
                                    <option value="<?= $r['id'] ?>" <?= old('route_id', $rule['route_id'] ?? '') == $r['id'] ? 'selected' : '' ?>>
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
                                        value="<?= old('time_from') ?? date('H:i', strtotime($rule['time_from'])) ?>" placeholder="HH:MM (e.g. 05:00, 13:30)" maxlength="5" required>
                                    <button type="button" class="military-time-btn" data-time-target="time_from" title="Open 24-hour time picker">
                                        <i class="bi bi-clock"></i>
                                    </button>
                                </div>
                                <div class="form-text-modern">24-hour military time format (e.g. 08:00 or 14:30).</div>
                            </div>
                            <div class="col-md-6 mb-4">
                                <label for="time_to" class="form-label-modern">Time To (HH:MM) <span class="text-danger">*</span></label>
                                <div class="military-time-wrap">
                                    <input type="text" class="input-modern military-time-input" id="time_to" name="time_to"
                                        value="<?= old('time_to') ?? date('H:i', strtotime($rule['time_to'])) ?>" placeholder="HH:MM (e.g. 09:00, 18:00)" maxlength="5" required>
                                    <button type="button" class="military-time-btn" data-time-target="time_to" title="Open 24-hour time picker">
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
                                        value="<?= esc($defaultWaitValue) ?>" placeholder="HH:MM (e.g. 00:30)" maxlength="5" required>
                                    <button type="button" class="military-time-btn" data-time-target="wait_duration" title="Open 24-hour time picker">
                                        <i class="bi bi-clock"></i>
                                    </button>
                                </div>
                                <div class="form-text-modern">Hours and minutes in 24-hour format (e.g. 00:30 = 30 mins).</div>
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

    .military-time-wrap {
        position: relative;
        display: flex;
        align-items: stretch;
        gap: 0.5rem;
    }

    .military-time-wrap .input-modern {
        flex: 1;
        min-width: 0;
        font-variant-numeric: tabular-nums;
        font-weight: 500;
        letter-spacing: 0.05em;
    }

    .military-time-btn {
        width: 44px;
        border: 1px solid #cbd5e1;
        border-radius: 0.5rem;
        background: #f8fafc;
        color: #475569;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.15s ease-in-out;
    }

    .military-time-btn:hover {
        background: #0d6efd;
        border-color: #0d6efd;
        color: #fff;
    }

    .military-time-popover {
        display: none;
        position: fixed;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 0.75rem;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.15), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
        z-index: 9999;
        padding: 0.75rem 1rem;
    }

    .military-time-popover.open {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .military-time-popover .tp-input {
        width: 80px;
        height: 48px;
        border: 1px solid #cbd5e1;
        border-radius: 0.5rem;
        text-align: center;
        font-size: 1.25rem;
        font-weight: 600;
        font-variant-numeric: tabular-nums;
        color: #1e293b;
        outline: none;
        transition: border-color 0.15s;
        -moz-appearance: textfield;
    }

    /* Show spinner arrows */
    .military-time-popover .tp-input::-webkit-inner-spin-button,
    .military-time-popover .tp-input::-webkit-outer-spin-button {
        opacity: 1;
        height: 44px;
        cursor: pointer;
    }

    .military-time-popover .tp-input:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 2px rgba(13, 110, 253, 0.15);
    }

    .military-time-popover .tp-sep {
        font-size: 1.5rem;
        font-weight: 700;
        color: #334155;
        user-select: none;
    }

    .military-time-popover .tp-set-btn {
        height: 48px;
        padding: 0 22px;
        border: none;
        border-radius: 0.5rem;
        background: var(--primary, #1565C0);
        color: #fff;
        font-size: 1rem;
        font-weight: 700;
        cursor: pointer;
        transition: background 0.15s;
        white-space: nowrap;
    }

    .military-time-popover .tp-set-btn:hover {
        background: var(--primary-dark, #0D47A1);
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
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
        });

        input.addEventListener('blur', function() {
            let val = input.value.trim();
            if (!val) return;
            let digits = val.replace(/\D/g, '');
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
        });
    });

    let activePopover = null;

    document.querySelectorAll('.military-time-wrap').forEach(function(wrap) {
        let input = wrap.querySelector('.military-time-input');
        let btn = wrap.querySelector('.military-time-btn');
        if (!input || !btn) return;

        let popover = document.createElement('div');
        popover.className = 'military-time-popover';

        let hhInput = document.createElement('input');
        hhInput.type = 'number';
        hhInput.className = 'tp-input';
        hhInput.min = 0;
        hhInput.max = 23;
        hhInput.step = 1;
        hhInput.placeholder = 'HH';
        popover.appendChild(hhInput);

        let sep = document.createElement('span');
        sep.className = 'tp-sep';
        sep.textContent = ':';
        popover.appendChild(sep);

        let mmInput = document.createElement('input');
        mmInput.type = 'number';
        mmInput.className = 'tp-input';
        mmInput.min = 0;
        mmInput.max = 59;
        mmInput.step = 1;
        mmInput.placeholder = 'MM';
        popover.appendChild(mmInput);

        let setBtn = document.createElement('button');
        setBtn.type = 'button';
        setBtn.className = 'tp-set-btn';
        setBtn.textContent = 'Set';
        popover.appendChild(setBtn);

        document.body.appendChild(popover);

        // Clamp hour to 00-23
        hhInput.addEventListener('change', function() {
            let n = parseInt(this.value, 10) || 0;
            if (n < 0) n = 0;
            if (n > 23) n = 23;
            this.value = n;
        });

        // Clamp minute to 00-59
        mmInput.addEventListener('change', function() {
            let n = parseInt(this.value, 10) || 0;
            if (n < 0) n = 0;
            if (n > 59) n = 59;
            this.value = n;
        });

        function syncFromMain() {
            let val = input.value.trim();
            let h = 0, m = 0;
            if (val.includes(':')) {
                let parts = val.split(':');
                h = Math.min(23, parseInt(parts[0], 10) || 0);
                m = Math.min(59, parseInt(parts[1], 10) || 0);
            }
            hhInput.value = h;
            mmInput.value = m;
        }

        function positionPopover() {
            let rect = btn.getBoundingClientRect();
            let popH = popover.offsetHeight || 50;
            let spaceBelow = window.innerHeight - rect.bottom - 10;
            if (spaceBelow >= popH) {
                popover.style.top = (rect.bottom + 6) + 'px';
            } else {
                popover.style.top = Math.max(10, rect.top - popH - 6) + 'px';
            }
            popover.style.left = Math.min(rect.left, window.innerWidth - 260) + 'px';
        }

        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            if (activePopover && activePopover !== popover) {
                activePopover.classList.remove('open');
            }
            syncFromMain();
            popover.classList.toggle('open');
            if (popover.classList.contains('open')) {
                positionPopover();
                hhInput.focus();
                hhInput.select();
            }
            activePopover = popover.classList.contains('open') ? popover : null;
        });

        setBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            let h = String(Math.min(23, parseInt(hhInput.value, 10) || 0)).padStart(2, '0');
            let m = String(Math.min(59, parseInt(mmInput.value, 10) || 0)).padStart(2, '0');
            input.value = h + ':' + m;
            popover.classList.remove('open');
            activePopover = null;
        });

        // Allow Enter key to set
        popover.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                setBtn.click();
            }
        });

        popover.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    });

    document.addEventListener('click', function() {
        if (activePopover) {
            activePopover.classList.remove('open');
            activePopover = null;
        }
    });
});
</script>
