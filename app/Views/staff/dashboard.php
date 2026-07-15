<?= $this->include('templates/header') ?>

<!-- Modern Frontend Styles -->
<link rel="stylesheet" href="<?= base_url('assets/css/modern-frontend.css') ?>">

<style>
    /* Staff/Dispatcher-specific responsive enhancements */
    @media (max-width: 768px) {
        .page-header-modern {
            flex-direction: column;
            align-items: flex-start;
            gap: 16px;
            padding: 20px 0;
        }
        
        .page-title-modern {
            font-size: 22px;
        }
        
        .stat-card-modern {
            padding: 18px;
        }
        
        .stat-card-icon {
            width: 48px;
            height: 48px;
            font-size: 20px;
        }
        
        .stat-card-value {
            font-size: 26px;
        }
        
        .stat-card-label {
            font-size: 11px;
        }
        
        .table-modern thead {
            display: none;
        }
        
        .table-modern tbody tr {
            display: block;
            margin-bottom: 12px;
            border-radius: 10px;
            border: 1px solid var(--slate-200);
            padding: 14px;
        }
        
        .table-modern tbody td {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid var(--slate-100);
            font-size: 13px;
        }
        
        .table-modern tbody td:last-child {
            border-bottom: none;
        }
        
        .table-modern tbody td::before {
            content: attr(data-label);
            font-weight: 600;
            color: var(--slate-500);
            text-transform: uppercase;
            font-size: 11px;
        }
    }
    
    @media (max-width: 480px) {
        .stat-card-modern {
            padding: 16px;
        }
        
        .stat-card-value {
            font-size: 22px;
        }
    }
</style>

<div class="page-header-modern fade-in">
    <h1 class="page-title-modern" style="--primary-red: #1565c0; --primary-red-dark: #0d47a1;">
        <i class="bi bi-person-workspace" style="color: #1565c0;"></i>
        Dispatcher Dashboard
    </h1>
</div>

<div class="row">
    <div class="col-12 col-md-6 col-xl-4 mb-4">
        <div class="stat-card-modern blue-accent fade-in">
            <div class="stat-card-icon" style="background: #DBEAFE; color: #1565c0;">
                <i class="bi bi-clock-history"></i>
            </div>
            <div class="stat-card-value"><?= $active_queue_count ?></div>
            <div class="stat-card-label">Active in Queue</div>
            <a href="<?= base_url('staff/queue') ?>" class="stat-card-link" style="color: #1565c0;">
                Manage Queue <i class="bi bi-arrow-right"></i>
            </a>
        </div>
    </div>
    
    <?php foreach ($terminals as $terminal): ?>
        <div class="col-12 col-md-6 col-xl-4 mb-4">
            <div class="modern-card shadow-modern fade-in">
                <div class="modern-card-body">
                    <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; color: var(--text-muted, #475569); margin-bottom: 8px;">
                        <?= esc($terminal['name']) ?>
                    </div>
                    <div class="d-flex justify-content-between align-items-end">
                        <div>
                            <div style="font-size: 28px; font-weight: 800; color: var(--text-main, #1e293b); line-height: 1.1;">
                                <?= esc($terminal['capacity']) ?>
                                <span style="font-size: 12px; font-weight: 600; color: var(--text-muted, #475569);">pax capacity</span>
                            </div>
                        </div>
                        <i class="bi bi-building fs-2" style="color: #1565c0; opacity: 0.2;"></i>
                    </div>
                    <p style="margin: 8px 0 0; font-size: 13px; color: var(--text-muted, #475569);"><?= esc($terminal['location']) ?></p>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<h4 class="mt-5 mb-3" style="font-size: 20px; font-weight: 700; color: var(--text-main, #1e293b);">Recent Departures</h4>
<div class="modern-card shadow-modern fade-in">
    <div class="table-responsive">
        <table class="table-modern">
            <thead>
                <tr>
                    <th>Type</th>
                    <th>Plate Number</th>
                    <th>Departure Time</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($recent_departures)): ?>
                    <?php foreach ($recent_departures as $dept): ?>
                        <tr>
                            <td data-label="Type">
                                <?php 
                                    $vType = $dept['vehicle_type'] ?? '';
                                    $imgFile = vehicle_type_image($vType);
                                ?>
                                <span class="vehicle-type-icon <?= vehicle_type_class($vType) ?>">
                                    <img src="<?= base_url('images/' . $imgFile) ?>" alt="<?= vehicle_type_label($vType) ?>" style="height:36px; width:auto;" title="<?= vehicle_type_label($vType) ?>">
                                </span>
                            </td>
                            <td class="fw-bold" data-label="Plate Number"><?= esc($dept['plate_number']) ?></td>
                            <td data-label="Departure Time"><?= date('H:i', strtotime($dept['departure_time'])) ?></td>
                            <td data-label="Status">
                                <span class="badge-modern badge-modern-success">
                                    <i class="bi bi-check-circle-fill"></i> Departed
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" style="text-align: center; padding: 40px; color: var(--text-muted, #475569);">No recent departures today.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?= $this->include('templates/footer') ?>

<script src="<?= base_url('js/ws-client.js') ?>"></script>
<script src="<?= base_url('js/queue-sync.js') ?>"></script>
<script>
    QueueSync.init({
        pollInterval: 3000,
        refreshUrl:   '<?= base_url('staff/dashboard') ?>',
        tableSelector: '.table-hover tbody',
        extraRefresh: function(newDoc) {
            // Update Active in Queue count
            var newCount = newDoc.querySelector('.card.bg-primary .card-body h2');
            var curCount = document.querySelector('.card.bg-primary .card-body h2');
            if (newCount && curCount) curCount.textContent = newCount.textContent;

            // Update stat cards (terminal capacity, etc.)
            var newCards = newDoc.querySelectorAll('.card.border-info .card-body');
            var curCards = document.querySelectorAll('.card.border-info .card-body');
            newCards.forEach(function(card, i) { if (curCards[i]) curCards[i].innerHTML = card.innerHTML; });
        }
    });
</script>
