<?= $this->include('templates/header') ?>

<!-- Modern Frontend Styles -->
<link rel="stylesheet" href="<?= base_url('assets/css/modern-frontend.css') ?>">

<?php
$isAdmin = in_array(session()->get('role'), ['super_admin', 'admin'], true);
?>

<style>
/* Freeze card hover shifts while modal is open */
body.modal-open .fare-section-card,
body.modal-open .discount-rate-card,
body.modal-open .fare-item {
    transform: none !important;
    transition: none !important;
    box-shadow: none !important;
}
/* Fare Card/Item Hover & Transition Animation */
.fare-section-card {
    transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    display: flex;
    flex-direction: column;
}
.fare-section-card .modern-card-body {
    display: flex;
    flex-direction: column;
    flex: 1 1 auto;
}
.fare-section-card .fare-list {
    display: flex;
    flex-direction: column;
    flex: 1 1 auto;
}
.fare-empty-item {
    display: flex !important;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    padding: 40px 20px !important;
    flex: 1 1 auto;
    min-height: 180px;
    background: transparent !important;
    border: none !important;
}
.fare-empty-item i {
    font-size: 32px;
    color: #94a3b8;
    margin-bottom: 8px;
    opacity: 0.7;
}
.fare-section-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 24px rgba(0,0,0,0.12) !important;
}
.fare-item {
    transition: transform 0.2s cubic-bezier(0.4, 0, 0.2, 1), background-color 0.2s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
}
.fare-item:hover {
    background-color: var(--primary-soft, #f8fafc) !important;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    z-index: 2;
}
.discount-rate-card {
    transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.25s cubic-bezier(0.4, 0, 0.2, 1);
}
.discount-rate-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 24px rgba(0,0,0,0.12) !important;
}
.discount-rate-card .modern-card-header {
    transition: background-color 0.2s ease;
}
.discount-rate-card:hover .modern-card-header {
    background-color: var(--primary-soft, #f8fafc) !important;
}
.fare-action-btn { 
    width: 34px; 
    height: 34px; 
    padding: 0; 
    display: inline-flex; 
    align-items: center; 
    justify-content: center; 
    border-radius: 8px; 
    font-size: 14px; 
}
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
.stat-card-modern[style*="--card-accent"]::before {
    background: var(--card-accent) !important;
}
.stat-card-modern[style*="--card-accent"]:hover {
    border-color: var(--card-accent) !important;
}
.fare-section-card {
    transition: transform 0.15s ease, box-shadow 0.15s ease;
}
.fare-section-card:hover {
    transform: translateY(-2px);
}
.fare-route-text {
    font-size: 15px;
    color: var(--text-main, #1e293b);
}

</style>

<div class="page-header-modern fade-in">
    <h1 class="page-title-modern">
        <i class="bi bi-tags"></i>
        Route Fares
    </h1>
    <?php if ($isAdmin): ?>
    <div>
        <button class="btn-modern btn-modern-primary" data-bs-toggle="modal" data-bs-target="#addFareModal">
            <i class="bi bi-plus-circle"></i> Add Fare
        </button>
    </div>
    <?php endif; ?>
</div>

<!-- Flash messages -->
<?php if (session()->getFlashdata('success')): ?>
<div class="alert-modern alert-modern-success fade-in mx-3 mt-2">
    <i class="bi bi-check-circle-fill alert-modern-icon"></i>
    <div><?= esc(session()->getFlashdata('success')) ?></div>
</div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
<div class="alert-modern alert-modern-danger fade-in mx-3 mt-2">
    <i class="bi bi-exclamation-triangle-fill alert-modern-icon"></i>
    <div><?= esc(session()->getFlashdata('error')) ?></div>
</div>
<?php endif; ?>
<?php if (session()->getFlashdata('errors') && is_array(session()->getFlashdata('errors'))): ?>
<div class="alert-modern alert-modern-danger fade-in mx-3 mt-2">
    <i class="bi bi-exclamation-triangle-fill alert-modern-icon"></i>
    <ul class="mb-0"><?php foreach (session()->getFlashdata('errors') as $e): ?><li><?= esc($e) ?></li><?php endforeach; ?></ul>
</div>
<?php endif; ?>
<div class="mx-3 mt-2"><?= $this->include('partials/flash_notices') ?></div>

<?php
$fareTypes = array_map(static fn(array $type) => [
    'id' => $type['id'] ?? null,
    'key' => $type['slug'],
    'type' => $type['slug'],
    'name' => $type['name'],
    'label' => $type['name'] . ' Routes',
    'color' => !empty($type['color']) ? $type['color'] : vehicle_type_color($type['slug']),
    'icon' => !empty($type['icon']) ? $type['icon'] : vehicle_type_icon($type['slug']),
    'bi_icon' => vehicle_type_bi_icon($type['slug'], $type['icon'] ?? null),
    'img' => vehicle_type_image($type['slug']),
    'badge' => vehicle_type_badge($type['slug']),
    'badgeClass' => vehicle_type_class($type['slug']),
], $vehicleTypes ?? []);
?>

<!-- Vehicle-type totals are generated from the configured vehicle types with their assigned colors -->
<div class="row px-3 mt-2">
<?php foreach ($fareTypes as $ft): ?>
    <?php
        $vtColor = $ft['color'];
        $vtBiIcon = $ft['bi_icon'];
        $isLightColor = (contrast_text_color($vtColor) === '#0f172a');
        $cardAccent = $isLightColor ? '#94a3b8' : $vtColor;
        $statIconBg = $isLightColor ? '#f1f5f9' : ($vtColor . '18');
        $statIconColor = $isLightColor ? '#0f172a' : $vtColor;
        $statIconBorder = $isLightColor ? 'border: 1.5px solid #cbd5e1 !important;' : '';
    ?>
    <div class="col-12 col-md-4 mb-4">
        <div class="stat-card-modern fade-in" style="--card-accent: <?= esc($cardAccent) ?>;">
            <div class="stat-card-icon" style="background-color: var(--vehicle-<?= esc($ft['key']) ?>-soft, <?= esc($statIconBg) ?>) !important; color: var(--vehicle-<?= esc($ft['key']) ?>, <?= esc($statIconColor) ?>) !important; <?= $statIconBorder ?>">
                <i class="bi <?= esc($vtBiIcon) ?>"></i>
            </div>
            <div class="stat-card-value"><?= count(($routesByType ?? [])[$ft['key']] ?? []) ?></div>
            <div class="stat-card-label"><?= esc($ft['label']) ?></div>
        </div>
    </div>
<?php endforeach; ?>
</div>

<!-- Search & Filter Bar -->
<div class="modern-card shadow-modern fade-in mx-3 mb-4">
    <div class="modern-card-body py-3">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div class="fare-search-group" style="display:flex; align-items:center; max-width:420px; width:100%;">
                <span class="input-group-text" style="height:38px; border:1.5px solid #cbd5e1; border-right:none; border-radius:20px 0 0 20px; background:#fff; color:#64748b; padding:0 12px;"><i class="bi bi-search"></i></span>
                <input type="text" id="fareSearch" class="form-control" placeholder="Search destination, route, discount..." onkeyup="filterFares()" style="height:38px; border:1.5px solid #cbd5e1; border-left:none; border-radius:0 20px 20px 0; outline:none; font-size:13.5px; box-shadow:none;">
            </div>
            <div style="font-size: 13px; font-weight: 600; color: var(--text-muted, #64748b);">
                <i class="bi bi-info-circle me-1"></i> Showing fares across all configured vehicle types
            </div>
        </div>
    </div>
</div>

<div class="row px-3 mt-2" id="fareCardsGrid">

<?php foreach ($fareTypes as $ft): ?>
<?php
    $vtColor = $ft['color'];
    $vtIcon = $ft['icon'];
    $isLightColor = (contrast_text_color($vtColor) === '#0f172a');
    $cardTopBorder = $isLightColor ? '#94a3b8' : $vtColor;
    $headerTitleColor = $isLightColor ? '#0f172a' : $vtColor;
    $iconBoxBg = $vtColor;
    $iconBoxColor = $isLightColor ? '#0f172a' : '#ffffff';
    $iconBoxBorder = $isLightColor ? 'border: 1.5px solid #cbd5e1 !important;' : '';
?>
<div class="col-lg-4 mb-4">
    <div class="modern-card shadow-modern h-100 fare-section-card <?= vehicle_type_class($ft['type']) ?> fade-in" style="--fare-accent: <?= esc($vtColor) ?>; border-top: 3.5px solid var(--vehicle-<?= esc($ft['key']) ?>, <?= esc($cardTopBorder) ?>) !important;">
        <div class="modern-card-header d-flex justify-content-between align-items-center">
            <span class="modern-card-title fare-section-title d-flex align-items-center gap-2" style="color: var(--vehicle-<?= esc($ft['key']) ?>, <?= esc($headerTitleColor) ?>) !important;">
                <span class="d-inline-flex align-items-center justify-content-center rounded-2 flex-shrink-0" style="width: 28px; height: 28px; background-color: var(--vehicle-<?= esc($ft['key']) ?>, <?= esc($iconBoxBg) ?>); color: <?= esc($iconBoxColor) ?>; <?= $iconBoxBorder ?> font-size: 13px;">
                    <i class="fas <?= esc($vtIcon) ?>" style="color: <?= esc($iconBoxColor) ?> !important;"></i>
                </span>
                <span class="fw-bold" style="color: var(--vehicle-<?= esc($ft['key']) ?>, <?= esc($headerTitleColor) ?>) !important;"><?= esc($ft['label']) ?></span>
            </span>
            <?php if ($isAdmin && !empty($ft['id'])): ?>
            <form action="<?= base_url('admin/vehicle-types/delete/' . $ft['id']) ?>" method="post" class="d-inline"
                  onsubmit="return confirm('Delete vehicle type &quot;<?= esc($ft['name']) ?>&quot; and its fare routes? Registered vehicles will be preserved, but vehicles using this type must be reassigned or removed first.');">
                <?= csrf_field() ?>
                <button type="submit" class="btn-modern btn-modern-sm btn-action-delete fare-action-btn" title="Delete Vehicle Type & Card">
                    <i class="bi bi-trash"></i>
                </button>
            </form>
            <?php endif; ?>
        </div>
        <div class="modern-card-body p-0">
            <div class="list-group list-group-flush fare-list">
                <?php $routes = ($routesByType ?? [])[$ft['key']] ?? []; ?>
                <?php if (!empty($routes)): ?>
                    <?php foreach ($routes as $route): ?>
                    <div class="list-group-item fare-item" style="flex-direction: column; align-items: stretch; padding: 16px 20px;">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong class="d-block fare-route-text"><?= strtoupper(esc($route['origin'])) ?> → <?= strtoupper(esc($route['destination'])) ?></strong>
                                <small class="text-muted">Regular Fare</small>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge-modern badge-modern-success fs-6 px-3 py-2">₱<?= number_format($route['fare'], 0) ?></span>
                                <?php if ($isAdmin): ?>
                                <button type="button"
                                   class="btn-modern btn-modern-sm btn-modern-outline btn-action-edit fare-action-btn" title="Edit Fare"
                                   data-bs-toggle="modal"
                                   data-bs-target="#editFareModal"
                                   data-id="<?= $route['id'] ?>"
                                   data-origin="<?= esc($route['origin']) ?>"
                                   data-destination="<?= esc($route['destination']) ?>"
                                   data-fare="<?= $route['fare'] ?>"
                                   data-vehicle-type="<?= esc($route['vehicle_type']) ?>"
                                   data-terminal-id="<?= $route['terminal_id'] ?>">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form action="<?= base_url('admin/routes/delete/' . $route['id']) ?>" method="post" class="d-inline"
                                      onsubmit="return confirm('Delete this fare (<?= strtoupper(esc($route['origin'])) ?> → <?= strtoupper(esc($route['destination'])) ?>)?');">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn-modern btn-modern-sm btn-action-delete fare-action-btn" title="Delete Fare">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php if (!empty($route['discounted_fares'])): ?>
                        <div style="margin-top: 10px; padding-top: 10px; border-top: 1px solid var(--border, #e2e8f0);">
                            <?php foreach ($route['discounted_fares'] as $type => $df): ?>
                            <div class="d-flex justify-content-between align-items-center py-1">
                                <span class="fare-discount-label" style="font-size: 13.5px; color: var(--text-muted, #334155); font-weight: 600;">
                                    <i class="bi bi-tag me-1" style="font-size: 12.5px;"></i>
                                    <?= esc($df['label']) ?> <span class="text-muted" style="font-size: 12.5px; font-weight: 500;">(<?= number_format($df['discount_percent'], 0) ?>% off)</span>
                                </span>
                                <span style="font-size: 14px; font-weight: 700; color: var(--success, #16a34a);">₱<?= number_format($df['amount'], 2) ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="list-group-item text-center text-muted fare-empty-item">
                        <i class="bi bi-geo fs-3 mb-2 d-block" style="color:#94a3b8;"></i>
                        <span>No <?= esc(strtolower($ft['label'])) ?> listed yet.</span>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php endforeach; ?>
</div>
<!-- ══════════════════════════════════════════════════ -->
<!--  Discount Rates Section                           -->
<!-- ══════════════════════════════════════════════════ -->
<?php
$discountMeta = [
    'pwd'            => ['icon' => 'bi bi-person-fill-exclamation', 'badge' => '<span class="badge-modern badge-modern-info">PWD</span>'],
    'senior_citizen' => ['icon' => 'bi bi-shield-shaded',           'badge' => '<span class="badge-modern" style="background: rgba(109,40,217,0.1); color: #6d28d9;">Senior Citizen</span>'],
    'student'        => ['icon' => 'bi bi-mortarboard-fill',        'badge' => '<span class="badge-modern badge-modern-warning">Student</span>'],
];
$hasDiscounts = !empty($discounts);
$isManager = $isAdmin;
?>

<?php if ($hasDiscounts || $isManager): ?>
<div class="px-3 mb-4">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h5 class="fw-bold mb-0"><i class="bi bi-percent me-2" style="color: var(--primary-red);"></i>Passenger Discount Rates</h5>
        <?php if ($isManager): ?>
        <button class="btn-modern btn-modern-primary btn-modern-sm" data-bs-toggle="modal" data-bs-target="#addDiscountModal">
            <i class="bi bi-plus-circle"></i> Add Discount
        </button>
        <?php endif; ?>
    </div>

    <div class="row">
        <?php if ($hasDiscounts): ?>
        <?php foreach ($discounts as $disc):
            if (!$disc['is_active']) continue;
            $meta = $discountMeta[$disc['type']] ?? ['icon' => 'bi-tag', 'badge' => '<span class="badge-modern badge-modern-info">' . esc(ucwords(str_replace('_', ' ', $disc['type']))) . '</span>'];
        ?>
        <div class="col-lg-4 mb-4">
            <div class="modern-card shadow-modern h-100 text-center discount-rate-card fade-in">
                <div class="modern-card-header d-flex justify-content-between align-items-center">
                    <span class="modern-card-title">
                        <i class="<?= $meta['icon'] ?>" style="color: var(--primary-red); margin-right: 8px;"></i>
                        <?= esc($disc['label']) ?>
                    </span>
                    <?= $meta['badge'] ?>
                </div>
                <div class="modern-card-body py-4">
                    <?php if (!empty($disc['terminal_name'])): ?>
                        <div class="text-muted mb-2" style="font-size: 13px;"><?= strtoupper(esc($disc['terminal_name'])) ?></div>
                    <?php endif; ?>
                    <div class="fw-bold" style="font-size: 56px; line-height: 1;">
                        <?= number_format($disc['discount_percent'], 0) ?><span style="font-size: 28px;">%</span>
                    </div>
                    <div class="text-muted mt-2" style="font-size: 13px;">off the regular fare</div>
                </div>
                <?php if ($isManager): ?>
                <div class="modern-card-footer d-flex justify-content-center gap-2">
                    <button class="btn-modern btn-modern-sm btn-modern-outline btn-action-edit"
                        data-bs-toggle="modal"
                        data-bs-target="#editDiscountModal"
                        data-id="<?= $disc['id'] ?>"
                        data-label="<?= esc($disc['label']) ?>"
                        data-percent="<?= $disc['discount_percent'] ?>"
                        data-active="<?= $disc['is_active'] ?>">
                        <i class="bi bi-pencil"></i> Edit
                    </button>
                    <form action="<?= base_url('admin/routes/discounts/delete/' . $disc['id']) ?>" method="post" class="d-inline"
                          onsubmit="return confirm('Delete this discount (<?= esc($disc['label']) ?>)?');">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn-modern btn-modern-sm btn-action-delete">
                            <i class="bi bi-trash"></i> Delete
                        </button>
                    </form>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
        <?php else: ?>
        <div class="col-12">
            <div class="modern-card shadow-modern text-center py-5">
                <div class="modern-card-body">
                    <i class="bi bi-percent text-muted mb-3" style="font-size: 48px;"></i>
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
<?php if ($isAdmin): ?>

<!-- ADD FARE MODAL -->
<div class="modal fade" id="addFareModal" tabindex="-1" aria-labelledby="addFareModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title fw-bold" id="addFareModalLabel"><i class="fas fa-plus me-2 text-primary"></i>Add New Fare</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <?php if ((session()->getFlashdata('error') || session()->getFlashdata('errors')) && old('destination')): ?>
        <div class="alert alert-danger py-2 px-3 small mb-3" style="border-radius: 8px;">
          <i class="fas fa-exclamation-triangle me-1"></i>
          <?= esc(session()->getFlashdata('error') ?? (is_array(session()->getFlashdata('errors')) ? implode(', ', session()->getFlashdata('errors')) : '')) ?>
        </div>
        <?php endif; ?>

        <form id="addFareForm" action="<?= base_url('admin/routes/store') ?>" method="post">
          <?= csrf_field() ?>

          <?php $onlyTerminal = (is_array($terminals) && count($terminals) === 1); ?>
          <div class="mb-3">
            <label class="form-label fw-semibold">Terminal</label>
            <?php if ($onlyTerminal): ?>
              <?php $singleTerminal = reset($terminals); ?>
              <input type="text" class="form-control" value="<?= esc($singleTerminal['name']) ?>" readonly style="background-color: var(--bs-secondary-bg, #e9ecef); cursor: not-allowed;">
              <input type="hidden" name="terminal_id" id="add_fare_terminal_id" value="<?= $singleTerminal['id'] ?>">
            <?php else: ?>
              <select name="terminal_id" id="add_fare_terminal_id" class="form-select" required>
                <option value="">— Select Terminal —</option>
                <?php foreach ($terminals as $t): ?>
                  <option value="<?= $t['id'] ?>"><?= esc($t['name']) ?></option>
                <?php endforeach; ?>
              </select>
            <?php endif; ?>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Destination</label>
            <input type="text" name="destination" id="add_destination" class="form-control autocomplete-location" 
                   placeholder="Type or search destination (e.g. ORMOC, TACLOBAN)..." required autocomplete="off"
                   data-suggestions="<?= esc(json_encode(array_values(array_filter($all_locations ?? [], fn($l) => strtoupper($l) !== 'PALOMPON')))) ?>">
            <div id="add_dest_feedback" class="mt-1" style="display: none; color: #dc2626 !important; font-size: 13.5px; font-weight: 600;">This route already exists for JEEPNEY.</div>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Fare Amount (₱)</label>
            <div class="input-group">
              <span class="input-group-text">₱</span>
              <input type="number" name="fare" class="form-control" step="0.01" min="1" placeholder="0.00" required>
            </div>
          </div>

          <div class="mb-4">
            <label class="form-label fw-semibold mb-2">Vehicle Type</label>
            <div class="d-flex gap-4 flex-wrap">
              <?php foreach (($vehicleTypes ?? []) as $index => $vehicleType): ?>
                <?php
                    $vtCol = !empty($vehicleType['color']) ? $vehicleType['color'] : vehicle_type_color($vehicleType['slug']);
                    $vtIco = !empty($vehicleType['icon']) ? $vehicleType['icon'] : vehicle_type_icon($vehicleType['slug']);
                ?>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="vehicle_type" id="add_<?= esc($vehicleType['slug']) ?>" value="<?= esc($vehicleType['slug']) ?>" <?= $index === 0 ? 'checked' : '' ?>>
                  <label class="form-check-label d-inline-flex align-items-center gap-1" for="add_<?= esc($vehicleType['slug']) ?>">
                    <i class="fas <?= esc($vtIco) ?>" style="color: <?= esc($vtCol) ?>;"></i> 
                    <span><?= esc($vehicleType['name']) ?></span>
                  </label>
                </div>
              <?php endforeach; ?>
            </div>
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
      <div class="modal-header">
        <h5 class="modal-title fw-bold" id="editFareModalLabel"><i class="fas fa-edit me-2 text-primary"></i>Edit Fare</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="editFareForm" method="post" data-no-change-guard>
          <?= csrf_field() ?>

          <?php $onlyTerminalEdit = (is_array($terminals) && count($terminals) === 1); ?>
          <div class="mb-3">
            <label class="form-label fw-semibold">Terminal</label>
            <?php if ($onlyTerminalEdit): ?>
              <?php $singleTerminalEdit = reset($terminals); ?>
              <input type="text" class="form-control" value="<?= esc($singleTerminalEdit['name']) ?>" readonly style="background-color: var(--bs-secondary-bg, #e9ecef); cursor: not-allowed;">
              <input type="hidden" name="terminal_id" id="edit_fare_terminal" value="<?= $singleTerminalEdit['id'] ?>">
            <?php else: ?>
              <select name="terminal_id" id="edit_fare_terminal" class="form-select" required>
                <option value="">— Select Terminal —</option>
                <?php foreach ($terminals as $t): ?>
                  <option value="<?= $t['id'] ?>"><?= esc($t['name']) ?></option>
                <?php endforeach; ?>
              </select>
            <?php endif; ?>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Destination</label>
            <input type="text" name="destination" id="edit_fare_destination" class="form-control autocomplete-location" 
                   placeholder="Type or search destination..." required autocomplete="off"
                   data-suggestions="<?= esc(json_encode(array_values(array_filter($all_locations ?? [], fn($l) => strtoupper($l) !== 'PALOMPON')))) ?>">
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
              <?php foreach (($vehicleTypes ?? []) as $vehicleType): ?>
                <?php
                    $vtCol = !empty($vehicleType['color']) ? $vehicleType['color'] : vehicle_type_color($vehicleType['slug']);
                    $vtIco = !empty($vehicleType['icon']) ? $vehicleType['icon'] : vehicle_type_icon($vehicleType['slug']);
                ?>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="vehicle_type" id="edit_<?= esc($vehicleType['slug']) ?>" value="<?= esc($vehicleType['slug']) ?>">
                  <label class="form-check-label d-inline-flex align-items-center gap-1" for="edit_<?= esc($vehicleType['slug']) ?>">
                    <i class="fas <?= esc($vtIco) ?>" style="color: <?= esc($vtCol) ?>;"></i> 
                    <span><?= esc($vehicleType['name']) ?></span>
                  </label>
                </div>
              <?php endforeach; ?>
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
      <div class="modal-header">
        <h5 class="modal-title fw-bold" id="editDiscountModalLabel"><i class="fas fa-percent me-2 text-primary"></i>Edit Discount</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="editDiscountForm" method="post" data-no-change-guard>
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
      <div class="modal-header">
        <h5 class="modal-title fw-bold" id="addDiscountModalLabel"><i class="fas fa-plus me-2 text-primary"></i>Add New Discount</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form action="<?= base_url('admin/routes/discounts/store') ?>" method="post" id="addDiscountForm">
          <?= csrf_field() ?>
          <?php $onlyTerminalDisc = (is_array($terminals) && count($terminals) === 1); ?>
          <div class="mb-3">
            <label class="form-label fw-semibold">Terminal</label>
            <?php if ($onlyTerminalDisc): ?>
              <?php $singleTerminalDisc = reset($terminals); ?>
              <input type="text" class="form-control" value="<?= esc($singleTerminalDisc['name']) ?>" readonly style="background-color: var(--bs-secondary-bg, #e9ecef); cursor: not-allowed;">
              <input type="hidden" name="terminal_id" id="add_terminal_id" value="<?= $singleTerminalDisc['id'] ?>">
            <?php else: ?>
              <select name="terminal_id" id="add_terminal_id" class="form-select" required>
                <option value="">— Select Terminal —</option>
                <?php foreach ($terminals as $t): ?>
                  <option value="<?= $t['id'] ?>"><?= esc($t['name']) ?></option>
                <?php endforeach; ?>
              </select>
            <?php endif; ?>
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
// Real-time validation for existing routes, grouped by configured vehicle type.
<?php
$existingRoutes = [];
foreach (($routesByType ?? []) as $type => $routes) {
    $existingRoutes[$type] = array_map(fn($route) => [
        'id'          => (int) ($route['id'] ?? 0),
        'terminal_id' => (string) ($route['terminal_id'] ?? ''),
        'destination' => strtoupper(trim($route['destination'] ?? '')),
        'fare'        => (float) ($route['fare'] ?? 0),
        'vehicle_type'=> $type,
    ], $routes);
}
?>
const existingRoutes = <?= json_encode($existingRoutes) ?>;

function findExistingRoute(terminalId, dest, type) {
    if (!dest || !type) return null;
    const cleanDest = dest.trim().toUpperCase().replace(/\s+/g, ' ');
    const cleanTerminal = terminalId ? String(terminalId).trim() : '';
    const routes = existingRoutes[type] || [];
    return routes.find(r => {
        const destMatch = r.destination.trim().toUpperCase().replace(/\s+/g, ' ') === cleanDest;
        if (!destMatch) return false;
        if (cleanTerminal) return String(r.terminal_id).trim() === cleanTerminal;
        return true;
    }) || null;
}

function checkRouteExists(terminalId, dest, type) {
    return Boolean(findExistingRoute(terminalId, dest, type));
}

function validateAddFareForm() {
    const form = document.getElementById('addFareForm');
    if (!form) return true;

    const terminalId = document.getElementById('add_fare_terminal_id')?.value || '';
    const destInput = document.getElementById('add_destination');
    const dest = destInput ? destInput.value.trim() : '';
    const checkedRadio = form.querySelector('input[name="vehicle_type"]:checked');
    const type = checkedRadio ? checkedRadio.value : '';
    const destFeedback = document.getElementById('add_dest_feedback');
    const submitBtn = form.querySelector('button[type="submit"]');

    if (!dest) {
        if (destInput) {
            destInput.classList.remove('is-invalid');
            destInput.style.borderColor = '';
        }
        if (destFeedback) {
            destFeedback.style.display = 'none';
        }
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.classList.remove('opacity-50');
        }
        return true;
    }

    const matched = findExistingRoute(terminalId, dest, type);
    if (matched) {
        if (destInput) {
            destInput.classList.add('is-invalid');
            destInput.style.borderColor = '#dc2626';
        }
        if (destFeedback) {
            destFeedback.style.display = 'block';
            destFeedback.style.color = '#dc2626';
            destFeedback.textContent = 'This route already exists for ' + type.toUpperCase() + '.';
        }
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.classList.remove('opacity-50');
        }
        return false;
    } else {
        if (destInput) {
            destInput.classList.remove('is-invalid');
            destInput.style.borderColor = '';
        }
        if (destFeedback) {
            destFeedback.style.display = 'none';
        }
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.classList.remove('opacity-50');
        }
        return true;
    }
}

// Switch directly from Add modal to Edit modal with full data population
function switchToEditFare(routeId) {
    var addModalEl = document.getElementById('addFareModal');
    if (addModalEl) {
        var addModal = bootstrap.Modal.getInstance(addModalEl) || new bootstrap.Modal(addModalEl);
        addModal.hide();
    }

    var foundRoute = null;
    for (var vType in existingRoutes) {
        var match = (existingRoutes[vType] || []).find(function(r) { return r.id == routeId; });
        if (match) {
            foundRoute = match;
            break;
        }
    }

    setTimeout(function() {
        var editForm = document.getElementById('editFareForm');
        if (editForm && routeId) {
            editForm.action = '<?= base_url('admin/routes/update') ?>/' + routeId;
        }
        if (foundRoute) {
            if (document.getElementById('edit_fare_destination')) {
                document.getElementById('edit_fare_destination').value = foundRoute.destination || '';
            }
            if (document.getElementById('edit_fare_amount')) {
                document.getElementById('edit_fare_amount').value = foundRoute.fare || '';
            }
            if (document.getElementById('edit_fare_terminal')) {
                document.getElementById('edit_fare_terminal').value = foundRoute.terminal_id || '';
            }
            if (editForm) {
                var radios = editForm.querySelectorAll('input[name="vehicle_type"]');
                radios.forEach(function(r) { r.checked = (r.value === foundRoute.vehicle_type); });
            }
        } else {
            var editBtn = document.querySelector('[data-bs-target="#editFareModal"][data-id="' + routeId + '"]');
            if (editBtn) populateFareModal(editBtn);
        }

        var editModalEl = document.getElementById('editFareModal');
        if (editModalEl) {
            var editModal = bootstrap.Modal.getInstance(editModalEl) || new bootstrap.Modal(editModalEl);
            editModal.show();
        }
    }, 200);
}

const addForm = document.getElementById('addFareForm');
if (addForm) {
    const destInput = document.getElementById('add_destination');
    if (destInput) {
        destInput.addEventListener('input', validateAddFareForm);
        destInput.addEventListener('change', validateAddFareForm);
    }

    const typeRadios = addForm.querySelectorAll('input[name="vehicle_type"]');
    typeRadios.forEach(r => {
        r.addEventListener('change', validateAddFareForm);
    });

    const terminalSelect = addForm.querySelector('select[name="terminal_id"]');
    if (terminalSelect) {
        terminalSelect.addEventListener('change', validateAddFareForm);
    }

    addForm.addEventListener('submit', function(e) {
        if (!validateAddFareForm()) {
            e.preventDefault();
            e.stopPropagation();
            const destFeedback = document.getElementById('add_dest_feedback');
            if (destFeedback) {
                destFeedback.classList.remove('gl-shake');
                void destFeedback.offsetWidth;
                destFeedback.classList.add('gl-shake');
            }
            if (destInput) {
                destInput.classList.remove('gl-shake');
                void destInput.offsetWidth;
                destInput.classList.add('gl-shake');
            }
            return false;
        }
    });
}

// Modal lifecycle listeners
var addFareModalEl = document.getElementById('addFareModal');
if (addFareModalEl) {
    addFareModalEl.addEventListener('shown.bs.modal', function() {
        validateAddFareForm();
    });
    addFareModalEl.addEventListener('hidden.bs.modal', function() {
        if (addForm) {
            addForm.reset();
            var radios = addForm.querySelectorAll('input[name="vehicle_type"]');
            if (radios.length > 0) radios[0].checked = true;
        }
        var destInput = document.getElementById('add_destination');
        if (destInput) {
            destInput.classList.remove('is-invalid');
            destInput.classList.remove('gl-shake');
            destInput.style.borderColor = '';
        }
        var destFeedback = document.getElementById('add_dest_feedback');
        if (destFeedback) {
            destFeedback.style.display = 'none';
            destFeedback.classList.remove('gl-shake');
        }
        var submitBtn = addForm?.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.classList.remove('opacity-50');
        }
    });
}

<?php if ((session()->getFlashdata('error') || session()->getFlashdata('errors')) && old('destination')): ?>
document.addEventListener('DOMContentLoaded', function() {
    var modalEl = document.getElementById('addFareModal');
    if (modalEl && window.bootstrap) {
        var modal = new bootstrap.Modal(modalEl);
        modal.show();
        var destInput = document.getElementById('add_destination');
        if (destInput) {
            destInput.value = <?= json_encode(old('destination')) ?>;
        }
        var fareInput = document.querySelector('#addFareForm input[name="fare"]');
        if (fareInput && <?= json_encode(old('fare')) ?>) {
            fareInput.value = <?= json_encode(old('fare')) ?>;
        }
        var oldVehicleType = <?= json_encode(old('vehicle_type')) ?>;
        if (oldVehicleType && addForm) {
            var r = addForm.querySelector('input[name="vehicle_type"][value="' + oldVehicleType + '"]');
            if (r) r.checked = true;
        }
        var oldTerminalId = <?= json_encode(old('terminal_id')) ?>;
        if (oldTerminalId) {
            var t = document.getElementById('add_fare_terminal_id');
            if (t) t.value = oldTerminalId;
        }
        validateAddFareForm();
    }
});
<?php endif; ?>

// Populate edit fare modal synchronously on click/mousedown/show.bs.modal to avoid input flash
function populateFareModal(btn) {
    if (!btn) return;
    var target = (btn.closest && btn.closest('[data-id]')) ? btn.closest('[data-id]') : btn;
    var id          = target.getAttribute('data-id');
    var destination = target.getAttribute('data-destination');
    var fare        = target.getAttribute('data-fare');
    var vehicleType = target.getAttribute('data-vehicle-type');
    var terminalId  = target.getAttribute('data-terminal-id');
    var form        = document.getElementById('editFareForm');
    if (form && id) form.action = '<?= base_url('admin/routes/update') ?>/' + id;
    if (document.getElementById('edit_fare_destination')) document.getElementById('edit_fare_destination').value = destination || '';
    if (document.getElementById('edit_fare_amount')) document.getElementById('edit_fare_amount').value = fare || '';
    if (document.getElementById('edit_fare_terminal')) document.getElementById('edit_fare_terminal').value = terminalId || '';
    if (form) {
        var radios = form.querySelectorAll('input[name="vehicle_type"]');
        radios.forEach(function(r) { r.checked = (r.value === vehicleType); });
    }
}

function populateDiscountModal(btn) {
    if (!btn) return;
    var target = (btn.closest && btn.closest('[data-id]')) ? btn.closest('[data-id]') : btn;
    var id      = target.getAttribute('data-id');
    var label   = target.getAttribute('data-label');
    var percent = target.getAttribute('data-percent');
    var active  = target.getAttribute('data-active');
    var form    = document.getElementById('editDiscountForm');
    if (form && id) form.action = '<?= base_url('admin/routes/discounts/update') ?>/' + id;
    if (document.getElementById('edit_disc_label')) document.getElementById('edit_disc_label').value = label || '';
    if (document.getElementById('edit_disc_percent')) document.getElementById('edit_disc_percent').value = percent || '';
    if (document.getElementById('edit_disc_active')) document.getElementById('edit_disc_active').checked = (active == '1' || active === 'true');
}

// Pre-fill on click/mousedown
document.addEventListener('click', function(e) {
    var editFareBtn = e.target.closest('[data-bs-target="#editFareModal"]');
    if (editFareBtn) populateFareModal(editFareBtn);
    var editDiscBtn = e.target.closest('[data-bs-target="#editDiscountModal"]');
    if (editDiscBtn) populateDiscountModal(editDiscBtn);
});
document.addEventListener('mousedown', function(e) {
    var editFareBtn = e.target.closest('[data-bs-target="#editFareModal"]');
    if (editFareBtn) populateFareModal(editFareBtn);
    var editDiscBtn = e.target.closest('[data-bs-target="#editDiscountModal"]');
    if (editDiscBtn) populateDiscountModal(editDiscBtn);
});

var editFareModal = document.getElementById('editFareModal');
if (editFareModal) {
    editFareModal.addEventListener('show.bs.modal', function(event) {
        populateFareModal(event.relatedTarget);
    });
}

var editDiscountModal = document.getElementById('editDiscountModal');
if (editDiscountModal) {
    editDiscountModal.addEventListener('show.bs.modal', function(event) {
        populateDiscountModal(event.relatedTarget);
    });
}

// Fare search filter
function filterFares() {
    const query = document.getElementById('fareSearch')?.value.toLowerCase().trim() ?? '';
    const cards = document.querySelectorAll('#fareCardsGrid .fare-section-card');

    cards.forEach(card => {
        const titleEl = card.querySelector('.fare-section-title');
        const headerText = titleEl ? titleEl.textContent.toLowerCase() : '';
        const headerMatches = Boolean(query && headerText.includes(query));
        
        const items = card.querySelectorAll('.fare-item');
        const emptyState = card.querySelector('.fare-empty-item');
        let cardVisibleCount = 0;

        items.forEach(item => {
            const text = item.textContent.toLowerCase();
            if (!query || headerMatches || text.includes(query)) {
                item.style.display = '';
                cardVisibleCount++;
            } else {
                item.style.display = 'none';
            }
        });

        if (emptyState) {
            if (!query || headerMatches) {
                emptyState.style.display = '';
            } else {
                emptyState.style.display = 'none';
            }
        }

        const col = card.closest('.col-lg-4, .col-12');
        if (col) {
            if (!query) {
                col.style.display = '';
            } else {
                if (cardVisibleCount > 0 || (items.length === 0 && headerMatches)) {
                    col.style.display = '';
                } else {
                    col.style.display = 'none';
                }
            }
        }
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
