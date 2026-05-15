<!-- Report Filter Modal (Reusable Partial) -->
<div class="modal fade" id="reportFilterModal" tabindex="-1" aria-labelledby="reportFilterModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white p-3">
                <h5 class="modal-title" id="reportFilterModalLabel"><i class="fas fa-filter me-2 text-white"></i> Report Configuration</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="reportForm" method="get" target="_blank" action="<?= base_url('admin/history/print') ?>">
                <div class="modal-body p-4">
                    <!-- Date Presets -->
                    <label class="form-label small fw-bold text-muted text-uppercase mb-3">Quick Date Selection</label>
                    <div class="d-flex flex-wrap gap-2 mb-4">
                        <button type="button" class="btn btn-sm btn-outline-secondary date-preset" data-range="today">Today</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary date-preset" data-range="week">Last 7 Days</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary date-preset" data-range="month">This Month</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary date-preset" data-range="all">Clear</button>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-6">
                            <label class="form-label small fw-bold text-muted text-uppercase">From Date</label>
                            <input type="date" class="form-control" name="from_date" id="modal_from_date">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold text-muted text-uppercase">To Date</label>
                            <input type="date" class="form-control" name="to_date" id="modal_to_date">
                        </div>
                    </div>

                    <!-- Additional Filters -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted text-uppercase">Vehicle Type</label>
                        <select class="form-select" name="vehicle_type" id="modal_vehicle_type">
                            <option value="">-- All Types --</option>
                            <?php if (!empty($vehicleTypes)): ?>
                                <?php foreach ($vehicleTypes as $type): ?>
                                    <option value="<?= esc($type) ?>"><?= ucfirst(esc($type)) ?></option>
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
                                        <?= esc($route['destination']) ?> (<?= ucfirst(esc($route['vehicle_type'])) ?>)
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div id="validation-msg" class="text-center small text-danger mb-0">
                        Please select a date range to generate a report.
                    </div>
                </div>
                <div class="modal-footer bg-light border-top-0 p-4">
                    <button type="button" id="btn-pdf" class="btn btn-danger w-100 action-btn" data-action="print" disabled>
                        <i class="fas fa-file-pdf me-1"></i> Generate PDF Report
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

    function validateForm() {
        const isValid = fromInput.value !== '' && toInput.value !== '';
        btnPdf.disabled = !isValid;
        
        if (isValid) {
            validationMsg.classList.add('d-none');
        } else {
            validationMsg.classList.remove('d-none');
        }
    }

    [fromInput, toInput].forEach(el => el.addEventListener('change', validateForm));

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
