<?= $this->include('templates/header') ?>

<link rel="stylesheet" href="<?= base_url('assets/css/modern-frontend.css') ?>">
<style>
    .manual-container {
        width: 92%;
        max-width: 1200px;
        margin: 25px auto 40px;
        padding: 0 10px;
        box-sizing: border-box;
    }
    .manual-header {
        background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
        color: #ffffff !important;
        border-radius: 16px;
        padding: 32px 28px;
        margin-bottom: 28px;
        box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.25);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 20px;
        width: 100%;
        box-sizing: border-box;
    }
    .manual-header h1,
    .manual-header .manual-title {
        color: #ffffff !important;
        font-size: clamp(20px, 3.5vw, 26px);
        font-weight: 800;
        margin: 0 0 8px;
        display: flex;
        align-items: center;
        gap: 12px;
        line-height: 1.3;
    }
    .manual-header p,
    .manual-header .manual-subtitle {
        color: #e2e8f0 !important;
        font-size: 14.5px;
        margin: 0;
        max-width: 700px;
        line-height: 1.5;
    }
    .manual-header-actions {
        display: flex;
        gap: 10px;
        align-items: center;
        flex-wrap: wrap;
    }
    .manual-header .btn-back {
        background: rgba(255, 255, 255, 0.15);
        color: #ffffff !important;
        border: 1px solid rgba(255, 255, 255, 0.3);
        padding: 8px 16px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 700;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s ease;
    }
    .manual-header .btn-back:hover {
        background: rgba(255, 255, 255, 0.25);
        color: #ffffff !important;
    }
    .manual-nav-pills {
        display: flex;
        gap: 10px;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: thin;
        padding-bottom: 10px;
        margin-bottom: 24px;
        border-bottom: 2px solid #e2e8f0;
        width: 100%;
        box-sizing: border-box;
    }
    .manual-nav-pills::-webkit-scrollbar {
        height: 4px;
    }
    .manual-nav-pills::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 4px;
    }
    .manual-pill-btn {
        background: white;
        border: 1px solid #cbd5e1;
        color: #475569;
        padding: 10px 20px;
        border-radius: 10px;
        font-size: 14px;
        font-weight: 700;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s ease;
        white-space: nowrap;
        flex-shrink: 0;
    }
    .manual-pill-btn:hover {
        background: #f1f5f9;
        color: #0f172a;
    }
    .manual-pill-btn.active {
        background: #D62828;
        border-color: #D62828;
        color: white;
        box-shadow: 0 4px 12px rgba(214, 40, 40, 0.25);
    }
    .tab-content {
        display: none;
        width: 100%;
        box-sizing: border-box;
    }
    .tab-content.active {
        display: block;
        width: 100%;
        animation: fadeIn 0.3s ease;
    }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: translateY(0); } }

    .manual-card {
        background: white;
        border-radius: 14px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        padding: 28px;
        margin-bottom: 24px;
        width: 100%;
        box-sizing: border-box;
    }
    .manual-card h3 {
        font-size: clamp(17px, 2.8vw, 19px);
        font-weight: 800;
        color: #0f172a;
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        gap: 10px;
        border-bottom: 2px solid #f8fafc;
        padding-bottom: 12px;
    }
    .step-item {
        display: flex;
        gap: 16px;
        margin-bottom: 20px;
        align-items: flex-start;
    }
    .step-number {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: #fee2e2;
        color: #b91c1c;
        font-weight: 800;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 14px;
    }
    .step-text {
        flex: 1;
        min-width: 0;
    }
    .step-text h5 {
        font-size: 15px;
        font-weight: 700;
        margin: 0 0 4px;
        color: #1e293b;
    }
    .step-text p {
        font-size: 13.5px;
        color: #64748b;
        margin: 0;
        line-height: 1.55;
    }
    .rule-callout {
        background: #f8fafc;
        border-left: 4px solid #3b82f6;
        padding: 16px 20px;
        border-radius: 0 10px 10px 0;
        margin: 16px 0;
        font-size: 13.5px;
        color: #334155;
        line-height: 1.6;
        box-sizing: border-box;
        width: 100%;
    }
    .error-callout {
        background: #fef2f2;
        border-left: 4px solid #ef4444;
        padding: 16px 20px;
        border-radius: 0 10px 10px 0;
        margin: 16px 0;
        font-size: 13.5px;
        color: #991b1b;
        line-height: 1.6;
        box-sizing: border-box;
        width: 100%;
    }
    .success-callout {
        background: #f0fdf4;
        border-left: 4px solid #22c55e;
        padding: 16px 20px;
        border-radius: 0 10px 10px 0;
        margin: 16px 0;
        font-size: 13.5px;
        color: #166534;
        line-height: 1.6;
        box-sizing: border-box;
        width: 100%;
    }

    /* Responsive Table Container */
    .table-responsive {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        margin: 16px 0;
        border-radius: 10px;
        border: 1px solid #e2e8f0;
        box-sizing: border-box;
    }
    .table-spec {
        width: 100%;
        min-width: 580px;
        border-collapse: collapse;
        margin: 0;
        font-size: 13.5px;
    }
    .table-spec th {
        background: #f8fafc;
        padding: 12px 14px;
        text-align: left;
        font-weight: 700;
        color: #475569;
        border-bottom: 2px solid #e2e8f0;
    }
    .table-spec td {
        padding: 12px 14px;
        border-bottom: 1px solid #f1f5f9;
        color: #334155;
    }

    /* Mobile Media Queries */
    @media (max-width: 768px) {
        .manual-container {
            width: 95% !important;
            margin: 16px auto 30px !important;
            padding: 0 !important;
        }
        .manual-header {
            padding: 22px 18px;
            border-radius: 14px;
            margin-bottom: 20px;
        }
        .manual-header h1,
        .manual-header .manual-title {
            font-size: 20px;
        }
        .manual-header p,
        .manual-header .manual-subtitle {
            font-size: 13px;
        }
        .manual-header-actions {
            width: 100%;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .manual-header-actions .btn-back,
        .manual-header-actions button {
            width: 100%;
            justify-content: center;
        }
        .manual-card {
            padding: 20px 16px;
            border-radius: 12px;
            margin-bottom: 18px;
        }
        .step-item {
            gap: 12px;
        }
    }

    @media (max-width: 480px) {
        .manual-header h1,
        .manual-header .manual-title {
            font-size: 18px;
        }
        .manual-pill-btn {
            padding: 8px 14px;
            font-size: 13px;
            border-radius: 8px;
        }
        .rule-callout, .error-callout, .success-callout {
            padding: 12px 14px;
            font-size: 13px;
        }
    }
</style>

<div class="manual-container">
    <div class="manual-header">
        <div>
            <h1 class="manual-title">
                <i class="fas fa-book-open" style="color: #ef4444;"></i>
                Admin & Superadmin User Manual
            </h1>
            <p class="manual-subtitle">
                Official guide for fleet administration, route & fare matrix configuration, departure headway rules, and error recovery.
            </p>
        </div>
        <div class="manual-header-actions">
            <a href="<?= base_url('admin/dashboard') ?>" class="btn-back">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
            <button onclick="window.print()" class="btn btn--primary btn--sm" style="background:#D62828; border:none; padding:8px 16px; border-radius:8px; font-weight:700; cursor:pointer; display:inline-flex; align-items:center; gap:6px;">
                <i class="fas fa-print"></i> Print Manual
            </button>
        </div>
    </div>

    <!-- Tab Navigation -->
    <div class="manual-nav-pills">
        <button class="manual-pill-btn active" onclick="switchManualTab('tab-overview', this)">
            <i class="fas fa-shield-alt"></i> Roles & Overview
        </button>
        <button class="manual-pill-btn" onclick="switchManualTab('tab-fleet', this)">
            <i class="fas fa-bus"></i> Fleet & Terminals
        </button>
        <button class="manual-pill-btn" onclick="switchManualTab('tab-routes', this)">
            <i class="fas fa-route"></i> Routes & Fares
        </button>
        <button class="manual-pill-btn" onclick="switchManualTab('tab-rules', this)">
            <i class="fas fa-clock"></i> Departure Rules
        </button>
        <button class="manual-pill-btn" onclick="switchManualTab('tab-heuristic9', this)">
            <i class="fas fa-triangle-exclamation" style="color:#ef4444;"></i> Error Recovery (Heuristic #9)
        </button>
        <button class="manual-pill-btn" onclick="switchManualTab('tab-system', this)">
            <i class="fas fa-server"></i> System Health
        </button>
    </div>

    <!-- Tab 1: Roles & Overview -->
    <div id="tab-overview" class="tab-content active">
        <div class="manual-card">
            <h3><i class="fas fa-users-cog" style="color:#3b82f6;"></i> User Roles & Access Hierarchy</h3>
            <p style="color:#64748b; font-size:14px;">The PTTM System divides authority into clear, non-overlapping operational roles:</p>
            <div class="table-responsive">
                <table class="table-spec">
                    <thead>
                        <tr>
                            <th>Role</th>
                            <th>Access Scope</th>
                            <th>Key Capabilities</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Super Admin</strong></td>
                            <td>Full System</td>
                            <td>Create Admins and Staff, configure all terminal settings, purge historical logs, configure vehicle types.</td>
                        </tr>
                        <tr>
                            <td><strong>Admin</strong></td>
                            <td>Fleet & Routes</td>
                            <td>Register vehicles, create routes and fare matrices, configure departure rules, print official departure history.</td>
                        </tr>
                        <tr>
                            <td><strong>Dispatcher (Staff)</strong></td>
                            <td>Assigned Routes</td>
                            <td>Check vehicles into queues, transition Waiting &rarr; Boarding &rarr; Departed, live passenger counter, driver updates, 1-click Undo Cancel.</td>
                        </tr>
                        <tr>
                            <td><strong>Commuter (Public)</strong></td>
                            <td>Read-Only Public</td>
                            <td>Live queue monitor, estimated departure countdowns, seat progress, fare lookup with 20% discount calculator, departure search.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="rule-callout">
                <i class="fas fa-info-circle" style="color:#3b82f6; margin-right:6px;"></i>
                <strong>Route Assignment Rule:</strong> When creating a staff account under <em>Management &rarr; Users</em>, ensure you check the specific route checkboxes they supervise. Unassigned dispatchers cannot check in vehicles on unauthorized routes.
            </div>
        </div>
    </div>

    <!-- Tab 2: Fleet & Terminals -->
    <div id="tab-fleet" class="tab-content">
        <div class="manual-card">
            <h3><i class="fas fa-bus" style="color:#10b981;"></i> Vehicle Registration & Fleet Management</h3>
            <div class="step-item">
                <div class="step-number">1</div>
                <div class="step-text">
                    <h5>Navigate to Vehicle Register</h5>
                    <p>Go to <strong>Management &rarr; Vehicle Register</strong> (`/admin/vehicles`) and click <strong>+ Add Vehicle</strong>.</p>
                </div>
            </div>
            <div class="step-item">
                <div class="step-number">2</div>
                <div class="step-text">
                    <h5>Plate Verification</h5>
                    <p>Enter the LTO Plate Number (e.g. <code>ABC-1234</code>). The system performs real-time validation to prevent duplicate plate entries.</p>
                </div>
            </div>
            <div class="step-item">
                <div class="step-number">3</div>
                <div class="step-text">
                    <h5>Capacity & Default Route</h5>
                    <p>Select the vehicle classification (PUJ Jeepney, UV Express Van, Modern Minibus). The seat capacity will automatically pre-populate (e.g. 14 for vans, 18 for PUJs). Assign its primary franchised destination route.</p>
                </div>
            </div>
            <div class="step-item">
                <div class="step-number">4</div>
                <div class="step-text">
                    <h5>Status Configuration</h5>
                    <p>Vehicles in <code>Active</code> status appear in the dispatcher's check-in pool. Setting a vehicle to <code>Maintenance</code> immediately hides it from the dispatcher queue to prevent dispatching unroadworthy units.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab 3: Routes & Fares -->
    <div id="tab-routes" class="tab-content">
        <div class="manual-card">
            <h3><i class="fas fa-route" style="color:#8b5cf6;"></i> Route & Fare Matrix Configuration</h3>
            <p style="color:#64748b; font-size:14px;">The PTTM System automates fare computations according to LTFRB fare structures:</p>
            <div class="rule-callout">
                <strong>Standard Formula:</strong> <code>Total Fare = Base Fare + (Distance in km - 4 km) × Rate per km</code><br>
                <strong>20% Statutory Discount:</strong> Automatically applied for verified Students, Senior Citizens, and PWDs across all routes.
            </div>
            <div class="step-item">
                <div class="step-number">1</div>
                <div class="step-text">
                    <h5>Add or Edit Route</h5>
                    <p>Go to <strong>Management &rarr; Routes</strong> (`/admin/routes`). Enter destination (e.g., Ormoc City), highway distance in kilometers, base fare, and incremental per-km rate.</p>
                </div>
            </div>
            <div class="step-item">
                <div class="step-number">2</div>
                <div class="step-text">
                    <h5>Save & Automatic Public Update</h5>
                    <p>Once saved, the public Fare Matrix at <code>/fares</code> is updated dynamically. Commuters and conductors immediately see the authoritative rates.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab 4: Departure Rules -->
    <div id="tab-rules" class="tab-content">
        <div class="manual-card">
            <h3><i class="fas fa-clock" style="color:#f59e0b;"></i> Headway & Departure Interval Rules</h3>
            <p style="color:#64748b; font-size:14px;">Departure rules govern when a vehicle is expected to depart, balancing peak passenger demand against regular off-peak travel:</p>
            <div class="step-item">
                <div class="step-number">1</div>
                <div class="step-text">
                    <h5>Configure Time Windows</h5>
                    <p>Set a rule for specific hours (e.g., Morning Peak from 06:00:00 to 09:00:00). Enter a waiting interval (e.g. 20 minutes).</p>
                </div>
            </div>
            <div class="step-item">
                <div class="step-number">2</div>
                <div class="step-text">
                    <h5>Dynamic ETD Calculation</h5>
                    <p>When a dispatcher moves a trip to <strong>Boarding</strong>, the system evaluates active rules for that hour and assigns the target Estimated Departure Time (ETD) automatically.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab 5: Error Recovery (Heuristic #9) -->
    <div id="tab-heuristic9" class="tab-content">
        <div class="manual-card">
            <h3><i class="fas fa-life-ring" style="color:#ef4444;"></i> Nielsen's Usability Heuristic #9: Error Recognition, Diagnosis & Recovery</h3>
            <p style="color:#64748b; font-size:14px; margin-bottom: 20px;">
                How PTTM helps administrators and dispatchers recognize, diagnose, and instantly recover from errors:
            </p>

            <div class="error-callout">
                <h5 style="margin:0 0 6px; font-weight:800;"><i class="fas fa-exclamation-triangle"></i> Scenario 1: Accidental Trip Cancellation</h5>
                <p style="margin:0;"><strong>Problem:</strong> Dispatcher clicks "Cancel" on a vehicle by mistake.</p>
                <p style="margin:4px 0 0;"><strong>Diagnosis:</strong> The system marks the trip as canceled, but does NOT purge the record.</p>
                <p style="margin:4px 0 0;"><strong>Recovery:</strong> A 1-click <code>Undo Cancel</code> button appears immediately. Clicking it restores the vehicle to the queue at its exact prior position with atomic database safety.</p>
            </div>

            <div class="rule-callout">
                <h5 style="margin:0 0 6px; font-weight:800;"><i class="fas fa-stopwatch"></i> Scenario 2: 30-Minute Departure Cooldown</h5>
                <p style="margin:0;"><strong>Problem:</strong> Driver departs, turns around immediately, and demands to re-enter the queue before 30 minutes pass.</p>
                <p style="margin:4px 0 0;"><strong>Diagnosis:</strong> Plain-language alert informs the dispatcher: <em>"Vehicle ABC-1234 departed recently. Please wait about 14 more minute(s) before adding it back."</em></p>
                <p style="margin:4px 0 0;"><strong>Recovery:</strong> The system automatically tracks the remaining minutes and re-enables the vehicle in the Available pool the instant 30 minutes expire.</p>
            </div>

            <div class="success-callout">
                <h5 style="margin:0 0 6px; font-weight:800;"><i class="fas fa-shield-alt"></i> Scenario 3: The "No-Change Guard" (no-change-guard.js)</h5>
                <p style="margin:0;"><strong>Problem:</strong> Administrator opens an edit form or modal, makes no changes, and hits "Save".</p>
                <p style="margin:4px 0 0;"><strong>Diagnosis:</strong> Submitting unchanged forms generates duplicate audit logs and redundant WebSocket broadcasts.</p>
                <p style="margin:4px 0 0;"><strong>Recovery:</strong> The No-Change Guard detects identical form state and displays a friendly notice: <em>"No changes detected — nothing was updated."</em> It cancels the request cleanly without reloading.</p>
            </div>
        </div>
    </div>

    <!-- Tab 6: System Health -->
    <div id="tab-system" class="tab-content">
        <div class="manual-card">
            <h3><i class="fas fa-server" style="color:#0ea5e9;"></i> Real-Time Daemon & Server Diagnostics</h3>
            <div class="step-item">
                <div class="step-number">1</div>
                <div class="step-text">
                    <h5>WebSocket Server Daemon</h5>
                    <p>PTTM uses a high-performance WebSocket daemon for sub-second terminal queue broadcasts. Start or monitor via terminal:</p>
                    <pre style="background:#0f172a; color:#38bdf8; padding:12px; border-radius:8px; font-size:13px; margin-top:6px;">php spark ws:serve</pre>
                </div>
            </div>
            <div class="step-item">
                <div class="step-number">2</div>
                <div class="step-text">
                    <h5>Automatic Polling Failover</h5>
                    <p>If the WebSocket service is interrupted, client browsers automatically drop back to 20-second HTTP polling without throwing an error or logging the user out.</p>
                </div>
            </div>
            <div class="step-item">
                <div class="step-number">3</div>
                <div class="step-text">
                    <h5>Log Inspection</h5>
                    <p>Application errors are written to <code>writable/logs/log-YYYY-MM-DD.log</code> and WebSocket events to <code>writable/logs/ws.log</code>.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function switchManualTab(tabId, btn) {
    document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
    document.querySelectorAll('.manual-pill-btn').forEach(el => el.classList.remove('active'));
    const target = document.getElementById(tabId);
    if (target) {
        target.classList.add('active');
        btn.classList.add('active');
    }
}
</script>

<?= $this->include('templates/footer') ?>
