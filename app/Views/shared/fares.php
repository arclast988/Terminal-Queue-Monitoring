<?= $this->include('templates/header') ?>

<style>
.page-hero { padding: 40px 30px; margin: -20px -24px 30px -24px; border-radius: 0 0 24px 24px; box-shadow: 0 4px 20px rgba(0,0,0,0.15); }
.page-hero.hero-admin { background: linear-gradient(135deg, rgba(30,58,95,0.95), rgba(198,40,40,0.85)) !important; }
.page-hero.hero-staff { background: linear-gradient(135deg, rgba(15,32,65,0.95), rgba(21,101,192,0.85)) !important; }
.page-hero h1 { color: white !important; font-size: 32px; font-weight: 800; margin-bottom: 8px; display: flex; align-items: center; gap: 12px; }
.page-hero .subtitle { color: rgba(255,255,255,0.8); font-size: 15px; margin-bottom: 20px; }
.page-hero .stats-row { display: flex; gap: 25px; flex-wrap: wrap; align-items: center; }
.page-hero .stat-item { display: flex; align-items: center; gap: 8px; color: rgba(255,255,255,0.9); font-size: 14px; }
.page-hero .stat-value { font-weight: 700; font-size: 18px; color: white; }
.fare-action-btn { width: 30px; height: 30px; padding: 0; display: inline-flex; align-items: center; justify-content: center; border-radius: 6px; font-size: 13px; }
.discount-badge { display: inline-flex; align-items: center; gap: 6px; padding: 6px 14px; border-radius: 999px; font-weight: 700; font-size: 14px; }
.discount-card { border-radius: 16px; overflow: hidden; border: none; box-shadow: 0 2px 12px rgba(0,0,0,0.08); }
.discount-card .card-header { padding: 16px 20px; border-bottom: 1px solid rgba(0,0,0,0.06); }
.discount-row { display: flex; align-items: center; justify-content: space-between; padding: 14px 20px; border-bottom: 1px solid #f1f5f9; }
.discount-row:last-child { border-bottom: none; }
.discount-type-icon { width: 42px; height: 42px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 18px; }
.modal-content { border-radius: 12px !important; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.1) !important; }
.modal-header { border-bottom: 1px solid #f1f5f9 !important; padding: 1.25rem 1.5rem !important; }
.modal-title { color: #1e3a5f !important; font-size: 1.1rem !important; }
.modal-body { padding: 1.5rem !important; }
.form-label { color: #475569; font-size: 0.875rem; }
.form-control, .form-select { border-color: #e2e8f0; padding: 0.6rem 0.75rem; font-size: 0.95rem; }
.form-control:focus, .form-select:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); }
.fare-section-title { transition: color 0.15s ease; }
body .card .fare-section-title.vehicle-type-jeepney,
body.admin-theme .card .fare-section-title.vehicle-type-jeepney,
body.staff-theme .card .fare-section-title.vehicle-type-jeepney { color: #1565c0 !important; }
body .card .fare-section-title.vehicle-type-van,
body.admin-theme .card .fare-section-title.vehicle-type-van,
body.staff-theme .card .fare-section-title.vehicle-type-van     { color: #c62828 !important; }
body .card .fare-section-title.vehicle-type-minibus,
body.admin-theme .card .fare-section-title.vehicle-type-minibus,
body.staff-theme .card .fare-section-title.vehicle-type-minibus { color: #2e7d32 !important; }
.fare-section-card.vehicle-type-jeepney .card-header { border-top: 3px solid #1565c0; }
.fare-section-card.vehicle-type-van     .card-header { border-top: 3px solid #c62828; }
.fare-section-card.vehicle-type-minibus .card-header { border-top: 3px solid #2e7d32; }
</style>

<?php $heroClass = (session()->get('role') === 'staff') ? 'hero-staff' : 'hero-admin'; ?>

<!-- Hero -->
<div class="page-hero <?= $heroClass ?>">
    <h1><i class="fas fa-tags"></i> Route Fares</h1>
    <p class="subtitle">Official fare rates for all van, jeepney, and minibus destinations</p>
    <div class="stats-row">
        <div class="stat-item">
            <img src="<?= base_url('images/van.png') ?>" alt="Van" style="width:42px;height:auto;">
            <div><div class="stat-value"><?= count($van_routes ?? []) ?></div><small>Van Routes</small></div>
        </div>
        <div class="stat-item">
            <img src="<?= base_url('images/jeep.png') ?>" alt="Jeepney" style="width:42px;height:auto;">
            <div><div class="stat-value"><?= count($jeepney_routes ?? []) ?></div><small>Jeepney Routes</small></div>
        </div>
        <div class="stat-item">
            <img src="<?= base_url('images/minibus.png') ?>" alt="Minibus" style="width:42px;height:auto;">
            <div><div class="stat-value"><?= count($minibus_routes ?? []) ?></div><small>Minibus Routes</small></div>
        </div>
        <?php if (session()->get('role') === 'admin'): ?>
        <div class="ms-auto">
            <button class="btn btn-light fw-bold px-4" data-bs-toggle="modal" data-bs-target="#addFareModal">
                <i class="fas fa-plus-circle me-2 text-success"></i>Add Fare
            </button>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Flash messages -->
<?php if (session()->getFlashdata('success')): ?>
<div class="alert alert-success alert-dismissible mx-3 mt-2 fade show" role="alert">
    <i class="fas fa-check-circle me-2"></i><?= esc(session()->getFlashdata('success')) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
<div class="alert alert-danger alert-dismissible mx-3 mt-2 fade show" role="alert">
    <i class="fas fa-exclamation-circle me-2"></i><?= esc(session()->getFlashdata('error')) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>
<?php if (session()->getFlashdata('errors') && is_array(session()->getFlashdata('errors'))): ?>
<div class="alert alert-danger mx-3 mt-2">
    <ul class="mb-0"><?php foreach (session()->getFlashdata('errors') as $e): ?><li><?= esc($e) ?></li><?php endforeach; ?></ul>
</div>
<?php endif; ?>

<div class="row px-3 mt-2">

<?php
$fareTypes = [
    ['key' => 'van_routes',     'type' => 'van',     'label' => 'Van Routes',     'img' => 'van.png',     'badge' => vehicle_type_badge('van'),     'badgeClass' => 'vehicle-type-chip vehicle-type-van'],
    ['key' => 'jeepney_routes', 'type' => 'jeepney', 'label' => 'Jeepney Routes', 'img' => 'jeep.png',    'badge' => vehicle_type_badge('jeepney'), 'badgeClass' => 'vehicle-type-chip vehicle-type-jeepney'],
    ['key' => 'minibus_routes', 'type' => 'minibus', 'label' => 'Minibus Routes', 'img' => 'minibus.png', 'badge' => vehicle_type_badge('minibus'), 'badgeClass' => 'vehicle-type-chip vehicle-type-minibus'],
];
?>

<?php foreach ($fareTypes as $ft): ?>
<div class="col-lg-4 mb-4">
    <div class="card shadow-sm h-100 fare-section-card <?= vehicle_type_class($ft['type']) ?>">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold d-flex align-items-center gap-2 fare-section-title <?= vehicle_type_class($ft['type']) ?>">
                <img src="<?= base_url('images/' . $ft['img']) ?>" alt="<?= $ft['label'] ?>" style="width:32px;height:auto;">
                <?= $ft['label'] ?>
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="list-group list-group-flush fare-list">
                <?php $routes = ${$ft['key']}; ?>
                <?php if (!empty($routes)): ?>
                    <?php foreach ($routes as $route): ?>
                    <div class="list-group-item fare-item" style="flex-direction: column; align-items: stretch; padding: 16px 20px;">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong class="d-block" style="font-size: 15px;"><?= strtoupper(esc($route['origin'])) ?> → <?= strtoupper(esc($route['destination'])) ?></strong>
                                <small class="text-muted">Regular Fare</small>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-success fs-6 px-3 py-2">₱<?= number_format($route['fare'], 0) ?></span>
                                <?php if (session()->get('role') === 'admin'): ?>
                                <button type="button"
                                   class="btn btn-sm btn-outline-primary fare-action-btn" title="Edit Fare"
                                   data-bs-toggle="modal"
                                   data-bs-target="#editFareModal"
                                   data-id="<?= $route['id'] ?>"
                                   data-origin="<?= esc($route['origin']) ?>"
                                   data-destination="<?= esc($route['destination']) ?>"
                                   data-fare="<?= $route['fare'] ?>"
                                   data-vehicle-type="<?= esc($route['vehicle_type']) ?>"
                                   data-terminal-id="<?= $route['terminal_id'] ?>">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form action="<?= base_url('admin/routes/delete/' . $route['id']) ?>" method="post" class="d-inline"
                                      onsubmit="return confirm('Delete this fare (<?= strtoupper(esc($route['origin'])) ?> → <?= strtoupper(esc($route['destination'])) ?>)?');">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-sm btn-outline-danger fare-action-btn" title="Delete Fare">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php if (!empty($route['discounted_fares'])): ?>
                        <div style="margin-top: 10px; padding-top: 10px; border-top: 1px solid #e2e8f0;">
                            <?php foreach ($route['discounted_fares'] as $type => $df): ?>
                            <div class="d-flex justify-content-between align-items-center py-1">
                                <span style="font-size: 13px; color: #475569; font-weight: 500;">
                                    <i class="fas fa-tag me-1" style="font-size: 10px; color: #94a3b8;"></i>
                                    <?= esc($df['label']) ?> <span class="text-muted" style="font-size: 12px;">(<?= number_format($df['discount_percent'], 0) ?>% off)</span>
                                </span>
                                <span style="font-size: 14px; font-weight: 700; color: #16a34a;">₱<?= number_format($df['amount'], 2) ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="list-group-item text-center text-muted py-4">
                        <i class="fas fa-route mb-2 d-block" style="font-size:24px;"></i>
                        No fares listed yet.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php endforeach; ?>
<!-- ══════════════════════════════════════════════════ -->
<!--  Discount Rates Section                           -->
<!-- ══════════════════════════════════════════════════ -->
<?php
$discountMeta = [
    'pwd'            => ['icon' => 'fa-wheelchair',     'badge' => '<span class="badge bg-info">PWD</span>'],
    'senior_citizen' => ['icon' => 'fa-user-shield',    'badge' => '<span class="badge" style="background:#6d28d9;">Senior Citizen</span>'],
    'student'        => ['icon' => 'fa-graduation-cap', 'badge' => '<span class="badge bg-warning text-dark">Student</span>'],
];
$hasDiscounts = !empty($discounts);
$isManager = session()->get('role') === 'admin';
?>

<?php if ($hasDiscounts || $isManager): ?>
<div class="px-3 mb-4">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h5 class="fw-bold mb-0"><i class="fas fa-percent me-2"></i>Passenger Discount Rates</h5>
        <?php if ($isManager): ?>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addDiscountModal">
            <i class="fas fa-plus me-1"></i> Add Discount
        </button>
        <?php endif; ?>
    </div>

    <div class="row">
        <?php if ($hasDiscounts): ?>
        <?php foreach ($discounts as $disc):
            if (!$disc['is_active']) continue;
            $meta = $discountMeta[$disc['type']] ?? ['icon' => 'fa-tag', 'badge' => '<span class="badge bg-secondary">' . esc(ucwords(str_replace('_', ' ', $disc['type']))) . '</span>'];
        ?>
        <div class="col-lg-4 mb-4">
            <div class="card shadow-sm h-100 text-center">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold d-flex align-items-center gap-2">
                        <i class="fas <?= $meta['icon'] ?>"></i>
                        <?= esc($disc['label']) ?>
                    </h5>
                    <?= $meta['badge'] ?>
                </div>
                <div class="card-body py-4">
                    <?php if (!empty($disc['terminal_name'])): ?>
                        <div class="text-muted mb-2" style="font-size: 13px;"><?= esc($disc['terminal_name']) ?></div>
                    <?php endif; ?>
                    <div class="fw-bold" style="font-size: 56px; line-height: 1; color: #1e293b;">
                        <?= number_format($disc['discount_percent'], 0) ?><span style="font-size: 28px;">%</span>
                    </div>
                    <div class="text-muted mt-2" style="font-size: 13px;">off the regular fare</div>
                </div>
                <?php if ($isManager): ?>
                <div class="card-footer bg-white d-flex justify-content-center gap-2">
                    <button class="btn btn-sm btn-outline-secondary"
                        data-bs-toggle="modal"
                        data-bs-target="#editDiscountModal"
                        data-id="<?= $disc['id'] ?>"
                        data-label="<?= esc($disc['label']) ?>"
                        data-percent="<?= $disc['discount_percent'] ?>"
                        data-active="<?= $disc['is_active'] ?>">
                        <i class="fas fa-edit me-1"></i> Edit
                    </button>
                    <form action="<?= base_url('admin/routes/discounts/delete/' . $disc['id']) ?>" method="post" class="d-inline"
                          onsubmit="return confirm('Delete this discount (<?= esc($disc['label']) ?>)?');">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-sm btn-outline-danger">
                            <i class="fas fa-trash-alt me-1"></i> Delete
                        </button>
                    </form>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
        <?php else: ?>
        <div class="col-12">
            <div class="card shadow-sm text-center py-5">
                <div class="card-body">
                    <i class="fas fa-percent text-muted mb-3" style="font-size: 48px;"></i>
                    <h5 class="text-muted">No Discounts Added Yet</h5>
                    <p class="text-muted mb-0">Click the "Add Discount" button above to create your first passenger discount rate.</p>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>


<!-- ══════════════════════════════════════════════ -->
<!--  MODALS                                       -->
<!-- ══════════════════════════════════════════════ -->
<?php if (session()->get('role') === 'admin'): ?>

<!-- ADD FARE MODAL -->
<div class="modal fade" id="addFareModal" tabindex="-1" aria-labelledby="addFareModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-white">
        <h5 class="modal-title fw-bold" id="addFareModalLabel"><i class="fas fa-plus me-2 text-primary"></i>Add New Fare</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="addFareForm" action="<?= base_url('admin/routes/store') ?>" method="post">
          <?= csrf_field() ?>

          <?php $onlyTerminal = (is_array($terminals) && count($terminals) === 1); ?>
          <div class="mb-3">
            <label class="form-label fw-semibold">Terminal</label>
            <select name="terminal_id" id="add_fare_terminal_id" class="form-select" required>
              <?php if (!$onlyTerminal): ?>
                <option value="">— Select Terminal —</option>
              <?php endif; ?>
              <?php foreach ($terminals as $t): ?>
                <option value="<?= $t['id'] ?>" <?= $onlyTerminal ? 'selected' : '' ?>><?= esc($t['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Destination</label>
            <select name="destination" id="add_destination" class="form-select" required>
              <option value="">— Select Destination —</option>
              <?php foreach ($all_locations as $loc): ?>
                <?php if (strtoupper($loc) !== 'PALOMPON'): ?>
                  <option value="<?= esc($loc) ?>"><?= esc($loc) ?></option>
                <?php endif; ?>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Fare Amount (₱)</label>
            <div class="input-group">
              <span class="input-group-text">₱</span>
              <input type="number" name="fare" class="form-control" step="0.01" min="1" placeholder="e.g. 150.00" required>
            </div>
          </div>

          <div class="mb-4">
            <label class="form-label fw-semibold mb-2">Vehicle Type</label>
            <div class="d-flex gap-4 flex-wrap">
              <div class="form-check">
                <input class="form-check-input" type="radio" name="vehicle_type" id="add_van" value="van" checked>
                <label class="form-check-label" for="add_van"><i class="fas fa-shuttle-van me-1 text-muted"></i> Van</label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="radio" name="vehicle_type" id="add_jeepney" value="jeepney">
                <label class="form-check-label" for="add_jeepney"><i class="fas fa-bus me-1 text-muted"></i> Jeepney</label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="radio" name="vehicle_type" id="add_minibus" value="minibus">
                <label class="form-check-label" for="add_minibus"><i class="fas fa-bus-alt me-1 text-muted"></i> Minibus</label>
              </div>
            </div>
          </div>

          <!-- Validation Message Container -->
          <div id="routeValidationMsg" class="alert alert-danger py-2 px-3 small d-none mb-3">
             <i class="fas fa-exclamation-circle me-1"></i> This route already exists.
          </div>

          <div class="d-grid mt-2">
            <button type="submit" class="btn btn-primary fw-bold py-2">
              <i class="fas fa-save me-2"></i>Save Fare
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- EDIT FARE MODAL -->
<div class="modal fade" id="editFareModal" tabindex="-1" aria-labelledby="editFareModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-white">
        <h5 class="modal-title fw-bold" id="editFareModalLabel"><i class="fas fa-edit me-2 text-primary"></i>Edit Fare</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="editFareForm" method="post">
          <?= csrf_field() ?>

          <?php $onlyTerminalEdit = (is_array($terminals) && count($terminals) === 1); ?>
          <div class="mb-3">
            <label class="form-label fw-semibold">Terminal</label>
            <select name="terminal_id" id="edit_fare_terminal" class="form-select" required>
              <?php if (!$onlyTerminalEdit): ?>
                <option value="">— Select Terminal —</option>
              <?php endif; ?>
              <?php foreach ($terminals as $t): ?>
                <option value="<?= $t['id'] ?>" <?= $onlyTerminalEdit ? 'selected' : '' ?>><?= esc($t['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Destination</label>
            <input type="text" name="destination" id="edit_fare_destination" class="form-control" required>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Fare Amount (₱)</label>
            <div class="input-group">
              <span class="input-group-text">₱</span>
              <input type="number" name="fare" id="edit_fare_amount" class="form-control" step="0.01" min="1" required>
            </div>
          </div>

          <div class="mb-4">
            <label class="form-label fw-semibold mb-2">Vehicle Type</label>
            <div class="d-flex gap-4 flex-wrap">
              <div class="form-check">
                <input class="form-check-input" type="radio" name="vehicle_type" id="edit_van" value="van">
                <label class="form-check-label" for="edit_van"><i class="fas fa-shuttle-van me-1 text-muted"></i> Van</label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="radio" name="vehicle_type" id="edit_jeepney" value="jeepney">
                <label class="form-check-label" for="edit_jeepney"><i class="fas fa-bus me-1 text-muted"></i> Jeepney</label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="radio" name="vehicle_type" id="edit_minibus" value="minibus">
                <label class="form-check-label" for="edit_minibus"><i class="fas fa-bus-alt me-1 text-muted"></i> Minibus</label>
              </div>
            </div>
          </div>

          <div class="d-grid mt-2">
            <button type="submit" class="btn btn-primary fw-bold py-2">
              <i class="fas fa-save me-2"></i>Update Fare
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- EDIT DISCOUNT MODAL -->
<div class="modal fade" id="editDiscountModal" tabindex="-1" aria-labelledby="editDiscountModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content">
      <div class="modal-header bg-white">
        <h5 class="modal-title fw-bold" id="editDiscountModalLabel"><i class="fas fa-percent me-2 text-primary"></i>Edit Discount</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="editDiscountForm" method="post">
          <?= csrf_field() ?>
          <div class="mb-3">
            <label class="form-label fw-semibold">Label / Name</label>
            <input type="text" name="label" id="edit_disc_label" class="form-control" required maxlength="100">
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Discount Percentage (%)</label>
            <div class="input-group">
              <input type="number" name="discount_percent" id="edit_disc_percent" class="form-control" step="0.01" min="0" max="100" required>
              <span class="input-group-text">%</span>
            </div>
            <div class="form-text">Enter a value between 0 and 100.</div>
          </div>
          <div class="mb-3">
            <div class="form-check form-switch">
              <input class="form-check-input" type="checkbox" id="edit_disc_active" name="is_active" value="1">
              <label class="form-check-label" for="edit_disc_active">Active</label>
            </div>
          </div>
          <div class="d-grid mt-2">
            <button type="submit" class="btn btn-primary fw-bold py-2">
              <i class="fas fa-check me-2"></i>Update Discount
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- ADD DISCOUNT MODAL -->
<div class="modal fade" id="addDiscountModal" tabindex="-1" aria-labelledby="addDiscountModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-white">
        <h5 class="modal-title fw-bold" id="addDiscountModalLabel"><i class="fas fa-plus me-2 text-primary"></i>Add New Discount</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form action="<?= base_url('admin/routes/discounts/store') ?>" method="post" id="addDiscountForm">
          <?= csrf_field() ?>
          <?php $onlyTerminalDisc = (is_array($terminals) && count($terminals) === 1); ?>
          <div class="mb-3">
            <label class="form-label fw-semibold">Terminal</label>
            <select name="terminal_id" id="add_terminal_id" class="form-select" required>
              <?php if (!$onlyTerminalDisc): ?>
                <option value="">— Select Terminal —</option>
              <?php endif; ?>
              <?php foreach ($terminals as $t): ?>
                <option value="<?= $t['id'] ?>" <?= $onlyTerminalDisc ? 'selected' : '' ?>><?= esc($t['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Discount Label</label>
            <input type="text" name="label" id="add_disc_label" class="form-control" placeholder="e.g. PWD Discount, Military Discount" required maxlength="100">
            <div class="form-text">The display name shown to users.</div>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Type Key</label>
            <input type="text" name="type" id="add_disc_type" class="form-control" placeholder="e.g. pwd, senior_citizen, military" required maxlength="50" pattern="[a-z0-9_]+" title="Only lowercase letters, numbers, and underscores">
            <div class="form-text">Auto-generated from label. Use lowercase with underscores (e.g. <code>senior_citizen</code>).</div>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Discount Percentage (%)</label>
            <div class="input-group">
              <input type="number" name="discount_percent" class="form-control" step="0.01" min="0" max="100" placeholder="e.g. 20" required>
              <span class="input-group-text">%</span>
            </div>
            <div class="form-text">Enter a value between 0 and 100.</div>
          </div>
          <div class="d-grid mt-2">
            <button type="submit" class="btn btn-primary fw-bold py-2"><i class="fas fa-save me-2"></i>Save Discount</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?php endif; ?>

<?= $this->include('templates/footer') ?>

<script>
// Prevent same origin = destination in add fare modal
document.getElementById('addFareForm')?.addEventListener('submit', function(e) {
    const terminalId = document.getElementById('add_fare_terminal_id').value;
    const dest = document.getElementById('add_destination').value;
    const vehicleType = document.querySelector('#addFareForm input[name="vehicle_type"]:checked')?.value;

    // Double-check existence before final submit
    if (checkRouteExists(terminalId, dest, vehicleType)) {
        e.preventDefault();
        document.getElementById('routeValidationMsg').classList.remove('d-none');
    }
});

// Real-time validation for existing routes
const existingRoutes = {
    van: <?= json_encode(array_map(fn($r) => ['terminal_id' => (string) $r['terminal_id'], 'destination' => strtoupper($r['destination'])], $van_routes ?? [])) ?>,
    jeepney: <?= json_encode(array_map(fn($r) => ['terminal_id' => (string) $r['terminal_id'], 'destination' => strtoupper($r['destination'])], $jeepney_routes ?? [])) ?>,
    minibus: <?= json_encode(array_map(fn($r) => ['terminal_id' => (string) $r['terminal_id'], 'destination' => strtoupper($r['destination'])], $minibus_routes ?? [])) ?>
};

function checkRouteExists(terminalId, dest, type) {
    if (!terminalId || !dest || !type) return false;
    const routes = existingRoutes[type] || [];
    return routes.some(r => r.terminal_id === String(terminalId) && r.destination === dest.toUpperCase());
}

const addForm = document.getElementById('addFareForm');
if (addForm) {
    const inputs = addForm.querySelectorAll('select[name="terminal_id"], select[name="destination"], input[name="vehicle_type"]');
    inputs.forEach(input => {
        input.addEventListener('change', () => {
            const terminalId = document.getElementById('add_fare_terminal_id').value;
            const dest = document.getElementById('add_destination').value;
            const type = document.querySelector('#addFareForm input[name="vehicle_type"]:checked')?.value;
            const msgEl = document.getElementById('routeValidationMsg');
            const submitBtn = addForm.querySelector('button[type="submit"]');

            if (checkRouteExists(terminalId, dest, type)) {
                msgEl.classList.remove('d-none');
                msgEl.innerHTML = `<i class="fas fa-exclamation-circle me-1"></i> A <strong>${type.toUpperCase()}</strong> route already exists for this terminal and destination.`;
                submitBtn.disabled = true;
            } else {
                msgEl.classList.add('d-none');
                submitBtn.disabled = false;
            }
        });
    });
}

// Populate edit fare modal
var editFareModal = document.getElementById('editFareModal');
if (editFareModal) {
    editFareModal.addEventListener('show.bs.modal', function(event) {
        var btn = event.relatedTarget;
        var id          = btn.getAttribute('data-id');
        var destination = btn.getAttribute('data-destination');
        var fare        = btn.getAttribute('data-fare');
        var vehicleType = btn.getAttribute('data-vehicle-type');
        var terminalId  = btn.getAttribute('data-terminal-id');
        var form        = document.getElementById('editFareForm');
        form.action     = '<?= base_url('admin/routes/update') ?>/' + id;
        document.getElementById('edit_fare_destination').value  = destination;
        document.getElementById('edit_fare_amount').value       = fare;
        document.getElementById('edit_fare_terminal').value     = terminalId;
        // Set vehicle type radio
        var radios = form.querySelectorAll('input[name="vehicle_type"]');
        radios.forEach(function(r) { r.checked = (r.value === vehicleType); });
    });
}

// Populate edit discount modal
var editDiscountModal = document.getElementById('editDiscountModal');
if (editDiscountModal) {
    editDiscountModal.addEventListener('show.bs.modal', function(event) {
        var btn = event.relatedTarget;
        var id      = btn.getAttribute('data-id');
        var label   = btn.getAttribute('data-label');
        var percent = btn.getAttribute('data-percent');
        var active  = btn.getAttribute('data-active');
        var form    = document.getElementById('editDiscountForm');
        form.action = '<?= base_url('admin/routes/discounts/update') ?>/' + id;
        document.getElementById('edit_disc_label').value   = label;
        document.getElementById('edit_disc_percent').value = percent;
        document.getElementById('edit_disc_active').checked = (active == '1');
    });
}

// Fare search filter
function filterFares() {
    const query = document.getElementById('fareSearch')?.value.toLowerCase() ?? '';
    document.querySelectorAll('.fare-item').forEach(item => {
        item.style.display = item.textContent.toLowerCase().includes(query) ? '' : 'none';
    });
}

// Auto-generate type key from discount label
var addDiscLabel = document.getElementById('add_disc_label');
var addDiscType  = document.getElementById('add_disc_type');
if (addDiscLabel && addDiscType) {
    addDiscLabel.addEventListener('input', function() {
        addDiscType.value = this.value
            .toLowerCase()
            .trim()
            .replace(/[^a-z0-9\s]/g, '')
            .replace(/\s+/g, '_');
    });
}
</script>
