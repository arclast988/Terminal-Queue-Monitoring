<!-- Report Filter Modal (Reusable Partial) -->
<style>
.report-filter-modal,
.report-filter-modal *,
.report-filter-modal *::before,
.report-filter-modal *::after {
    -webkit-tap-highlight-color: transparent !important;
}
.report-filter-modal button,
.report-filter-modal .btn,
.report-filter-modal .btn-close,
.report-filter-modal .report-filter-submit,
.report-filter-modal .date-preset,
.report-filter-modal .form-control,
.report-filter-modal .form-select {
    -webkit-tap-highlight-color: transparent !important;
    user-select: none;
    -webkit-user-select: none;
}
.report-filter-modal button:focus,
.report-filter-modal button:active,
.report-filter-modal .btn:focus,
.report-filter-modal .btn:active,
.report-filter-modal .report-filter-submit:focus,
.report-filter-modal .report-filter-submit:active {
    outline: none !important;
    box-shadow: none !important;
}
.report-filter-modal .form-control:focus,
.report-filter-modal .form-select:focus {
    border-color: #C62828 !important;
    box-shadow: 0 0 0 0.2rem rgba(198, 40, 40, 0.12) !important;
}
/* Date input styling with calendar icon & watermark cue */
.report-date-group {
    position: relative;
    border-radius: 6px;
}
.report-date-icon {
    background-color: #f8fafc !important;
    color: #C62828 !important;
    border-right: none !important;
    font-size: 0.95rem;
    cursor: pointer;
    border-color: #ced4da !important;
}
.report-date-input {
    border-left: none !important;
    padding-left: 6px !important;
    color: #1e293b;
    font-weight: 500;
    border-color: #ced4da !important;
}
.report-date-group:focus-within .report-date-icon,
.report-date-group:focus-within .report-date-input {
    border-color: #C62828 !important;
}
.report-date-group:focus-within {
    box-shadow: 0 0 0 0.2rem rgba(198, 40, 40, 0.12) !important;
    border-radius: 6px;
}
.report-date-group .form-control:focus {
    box-shadow: none !important;
}
.report-date-watermark {
    position: absolute;
    left: 48px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 0.85rem;
    color: #94a3b8;
    pointer-events: none;
    z-index: 4;
    user-select: none;
    -webkit-user-select: none;
    transition: opacity 0.15s ease;
    letter-spacing: 0.3px;
}
.report-date-group.has-value .report-date-watermark,
.report-date-group:focus-within .report-date-watermark {
    display: none !important;
    opacity: 0 !important;
}
.report-date-group:not(.has-value):not(:focus-within) .report-date-input::-webkit-datetime-edit {
    color: transparent !important;
}
.report-date-group.has-value .report-date-input::-webkit-datetime-edit,
.report-date-group:focus-within .report-date-input::-webkit-datetime-edit {
    color: inherit !important;
}
@supports (-moz-appearance: none) {
    .report-date-watermark {
        display: none !important;
    }
}
</style>
<div class="modal fade report-filter-modal" id="reportFilterModal" tabindex="-1" aria-labelledby="reportFilterModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered report-filter-dialog">
        <div class="modal-content border-0 shadow report-filter-content">
            <div class="modal-header text-white p-3 report-filter-header" style="background: linear-gradient(135deg, #C62828 0%, #B71C1C 100%) !important; border-top-left-radius: 8px; border-top-right-radius: 8px;">
                <h5 class="modal-title fw-bold" id="reportFilterModalLabel" style="color: #ffffff !important; display: flex; align-items: center; font-size: 1.15rem;">
                    <i class="fas fa-clock-rotate-left me-2" style="color: #ffffff !important;"></i> Departure Report Configuration
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" style="filter: brightness(0) invert(1) !important; opacity: 0.9 !important;"></button>
            </div>
            <form id="reportForm" class="report-filter-form" method="get" target="_blank" action="<?= base_url('admin/history/print') ?>">
                <div class="modal-body p-4 report-filter-body">
                    <!-- Date Presets -->
                    <label class="form-label small fw-bold text-muted text-uppercase mb-3">Quick Date Selection</label>
                    <div class="report-filter-presets mb-4">
                        <button type="button" class="btn btn-sm btn-outline-secondary date-preset" data-range="today">Today</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary date-preset" data-range="week">Last 7 Days</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary date-preset" data-range="month">This Month</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary date-preset" data-range="all">Clear</button>
                    </div>

                    <div class="row g-3 mb-4 report-filter-dates">
                        <div class="col-6">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-0">From Date</label>
                                <span class="badge bg-light text-muted border px-2 py-0" style="font-size: 10px; font-weight: 500;">dd/mm/yyyy</span>
                            </div>
                            <div class="input-group report-date-group" id="group_modal_from_date">
                                <span class="input-group-text report-date-icon" title="Choose date"><i class="bi bi-calendar3"></i></span>
                                <input type="date" class="form-control report-date-input" name="from_date" id="modal_from_date" placeholder="dd/mm/yyyy">
                                <span class="report-date-watermark text-muted">dd/mm/yyyy</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label small fw-bold text-muted text-uppercase mb-0">To Date</label>
                                <span class="badge bg-light text-muted border px-2 py-0" style="font-size: 10px; font-weight: 500;">dd/mm/yyyy</span>
                            </div>
                            <div class="input-group report-date-group" id="group_modal_to_date">
                                <span class="input-group-text report-date-icon" title="Choose date"><i class="bi bi-calendar3"></i></span>
                                <input type="date" class="form-control report-date-input" name="to_date" id="modal_to_date" placeholder="dd/mm/yyyy">
                                <span class="report-date-watermark text-muted">dd/mm/yyyy</span>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Filters -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted text-uppercase">Vehicle Type</label>
                        <select class="form-select" name="vehicle_type" id="modal_vehicle_type">
                            <option value="">-- All Types --</option>
                            <?php if (!empty($vehicleTypes)): ?>
                                <?php foreach ($vehicleTypes as $type): ?>
                                    <?php
                                    $typeSlug = is_array($type) ? $type['slug'] : $type;
                                    $typeName = is_array($type) ? $type['name'] : vehicle_type_label($type);
                                    $typeIcon = is_array($type) && !empty($type['icon']) ? $type['icon'] : vehicle_type_icon($typeSlug);
                                    $typeColor = is_array($type) && !empty($type['color']) ? $type['color'] : vehicle_type_color($typeSlug);
                                    ?>
                                    <option value="<?= esc($typeSlug) ?>"
                                            data-icon="<?= esc($typeIcon) ?>"
                                            data-color="<?= esc($typeColor) ?>"><?= esc($typeName) ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-bold text-muted text-uppercase">Specific Destination</label>
                        <select class="form-select" name="destination" id="modal_destination">
                            <option value="" data-vtype="all">-- All Destinations --</option>
                            <?php if (!empty($destinations)): ?>
                                <?php foreach ($destinations as $route): ?>
                                    <option value="<?= esc($route['destination']) ?>" data-vtype="<?= esc($route['vehicle_type']) ?>">
                                        <?= strtoupper(esc($route['destination'])) ?> (<?= ucfirst(esc($route['vehicle_type'])) ?>)
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div id="validation-msg" class="text-center small text-danger mb-0">
                        Please select a date range to generate a departure report.
                    </div>
                </div>
                <div class="modal-footer bg-light p-4 report-filter-footer">
                    <button type="button" id="btn-pdf" class="btn btn-danger w-100 action-btn report-filter-submit" data-action="print" disabled>
                        <i class="fas fa-file-pdf me-1"></i> Generate Departure Report
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const reportForm = document.getElementById('reportForm');
    const fromInput = document.getElementById('modal_from_date');
    const toInput = document.getElementById('modal_to_date');
    const typeSelect = document.getElementById('modal_vehicle_type');
    const destSelect = document.getElementById('modal_destination');
    const destOptions = Array.from(destSelect.options);
    
    const btnPdf = document.getElementById('btn-pdf');
    const validationMsg = document.getElementById('validation-msg');

    function syncDateStates() {
        [fromInput, toInput].forEach(inp => {
            if (!inp) return;
            const group = inp.closest('.report-date-group');
            if (group) {
                if (inp.value && inp.value.trim() !== '') {
                    group.classList.add('has-value');
                } else {
                    group.classList.remove('has-value');
                }
            }
        });
    }

    function validateForm() {
        syncDateStates();
        const isValid = fromInput.value !== '' && toInput.value !== '';
        btnPdf.disabled = !isValid;
        
        if (isValid) {
            validationMsg.classList.add('d-none');
        } else {
            validationMsg.classList.remove('d-none');
        }
    }

    [fromInput, toInput].forEach(el => {
        el.addEventListener('change', validateForm);
        el.addEventListener('input', validateForm);
    });

    // Calendar icon click helper to trigger native date picker
    document.querySelectorAll('.report-filter-modal .report-date-icon').forEach(icon => {
        icon.addEventListener('click', function() {
            const input = this.closest('.input-group')?.querySelector('input[type="date"]');
            if (input) {
                if (typeof input.showPicker === 'function') {
                    try { input.showPicker(); } catch (err) { input.focus(); }
                } else {
                    input.focus();
                }
            }
        });
    });

    const modalEl = document.getElementById('reportFilterModal');
    if (modalEl) {
        modalEl.addEventListener('show.bs.modal', function() {
            syncDateStates();
            validateForm();
        });
    }

    // Initial state check
    syncDateStates();

    // Dynamic Filtering: Vehicle Type -> Destination
    typeSelect.addEventListener('change', function() {
        const selectedType = this.value;
        const currentDest = destSelect.value;
        
        destSelect.innerHTML = '';
        destOptions.forEach(opt => {
            const vtype = opt.getAttribute('data-vtype');
            if (selectedType === '' || vtype === 'all' || vtype === selectedType) {
                destSelect.appendChild(opt);
            }
        });

        const stillExists = Array.from(destSelect.options).some(o => o.value === currentDest);
        destSelect.value = stillExists ? currentDest : '';
    });

    // Date Presets Logic
    document.querySelectorAll('.date-preset').forEach(btn => {
        btn.addEventListener('click', function() {
            const range = this.getAttribute('data-range');
            const today = new Date().toISOString().split('T')[0];
            
            document.querySelectorAll('.date-preset').forEach(b => {
                b.classList.remove('btn-secondary');
                b.classList.add('btn-outline-secondary');
            });
            this.classList.remove('btn-outline-secondary');
            this.classList.add('btn-secondary');

            if (range === 'today') {
                fromInput.value = today;
                toInput.value = today;
            } else if (range === 'week') {
                let d = new Date();
                d.setDate(d.getDate() - 7);
                fromInput.value = d.toISOString().split('T')[0];
                toInput.value = today;
            } else if (range === 'month') {
                let d = new Date();
                d.setDate(1);
                fromInput.value = d.toISOString().split('T')[0];
                toInput.value = today;
            } else {
                fromInput.value = '';
                toInput.value = '';
            }
            validateForm();
        });
    });

    // Handle Actions
    document.querySelectorAll('.action-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            reportForm.submit();
        });
    });
});
</script>
