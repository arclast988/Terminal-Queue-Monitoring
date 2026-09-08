<?= view('templates/header', ['title' => $title]) ?>

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
        background: linear-gradient(135deg, #065f46 0%, #047857 100%);
        color: #ffffff !important;
        border-radius: 16px;
        padding: 32px 28px;
        margin-bottom: 28px;
        box-shadow: 0 10px 25px -5px rgba(4, 120, 87, 0.25);
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
        color: #d1fae5 !important;
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
        background: #059669;
        border-color: #059669;
        color: white;
        box-shadow: 0 4px 12px rgba(5, 150, 105, 0.25);
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
        background: #d1fae5;
        color: #065f46;
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
        border-left: 4px solid #059669;
        padding: 16px 20px;
        border-radius: 0 10px 10px 0;
        margin: 16px 0;
        font-size: 13.5px;
        color: #334155;
        line-height: 1.6;
        box-sizing: border-box;
        width: 100%;
    }
    .alert-box {
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
        .manual-header-actions a {
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
        .rule-callout, .alert-box {
            padding: 12px 14px;
            font-size: 13px;
        }
    }
</style>

<div class="manual-container">
    <div class="manual-header">
        <div>
            <h1 class="manual-title">
                <i class="fas fa-clipboard-list" style="color: #6ee7b7;"></i>
                Dispatcher & Staff User Manual
            </h1>
            <p class="manual-subtitle">
                Operational guide for managing vehicle queues, boarding, passenger counters, and 1-click Undo Cancel recovery.
            </p>
        </div>
        <div class="manual-header-actions">
            <a href="<?= base_url('staff/dashboard') ?>" class="btn-back">
                <i class="fas fa-arrow-left"></i> Staff Dashboard
            </a>
            <a href="<?= base_url('staff/queue') ?>" class="btn btn--primary btn--sm" style="background:#059669; border:none; padding:8px 16px; border-radius:8px; font-weight:700; text-decoration:none; display:inline-flex; align-items:center; gap:6px;">
                <i class="fas fa-list-ol"></i> Go to Queue
            </a>
        </div>
    </div>

    <!-- Tab Navigation -->
    <div class="manual-nav-pills">
        <button class="manual-pill-btn active" onclick="switchManualTab('tab-shift', this)">
            <i class="fas fa-user-clock"></i> Shift Setup
        </button>
        <button class="manual-pill-btn" onclick="switchManualTab('tab-queue-ops', this)">
            <i class="fas fa-tasks"></i> Queue Operations
        </button>
        <button class="manual-pill-btn" onclick="switchManualTab('tab-passengers', this)">
            <i class="fas fa-users"></i> Passenger & Driver Controls
        </button>
        <button class="manual-pill-btn" onclick="switchManualTab('tab-undo-cancel', this)">
            <i class="fas fa-rotate-left" style="color:#ef4444;"></i> 1-Click Undo Cancel & Error Recovery
        </button>
        <button class="manual-pill-btn" onclick="switchManualTab('tab-announcements', this)">
            <i class="fas fa-bullhorn"></i> Advisories
        </button>
    </div>

    <!-- Tab 1: Shift Setup -->
    <div id="tab-shift" class="tab-content active">
        <div class="manual-card">
            <h3><i class="fas fa-sign-in-alt" style="color:#059669;"></i> Shift Start & Assigned Routes</h3>
            <div class="step-item">
                <div class="step-number">1</div>
                <div class="step-text">
                    <h5>Sign In to Dispatcher Portal</h5>
                    <p>Log in with your dispatcher credentials at <code>/login</code>. Your active terminal shift summary is displayed on your dashboard.</p>
                </div>
            </div>
            <div class="step-item">
                <div class="step-number">2</div>
                <div class="step-text">
                    <h5>Check Assigned Routes</h5>
                    <p>You can only manage vehicles for routes assigned to your account by the terminal administrator. If you supervise multiple destinations (e.g. Ormoc and Tacloban), both will appear in your queue filter.</p>
                </div>
            </div>
            <div class="rule-callout">
                <i class="fas fa-info-circle"></i> <strong>Note:</strong> If a vehicle arrives for a route that is not appearing in your available list, verify that your account has permission for that destination under your profile.
            </div>
        </div>
    </div>

    <!-- Tab 2: Queue Operations -->
    <div id="tab-queue-ops" class="tab-content">
        <div class="manual-card">
            <h3><i class="fas fa-list-ol" style="color:#059669;"></i> Step-by-Step Queue Workflow</h3>
            <div class="step-item">
                <div class="step-number">1</div>
                <div class="step-text">
                    <h5>Check In Arriving Vehicles</h5>
                    <p>When an operator arrives at the terminal staging area, locate their plate number in the <strong>Available Vehicles</strong> list and click <strong>Add to Queue</strong>. The vehicle enters as <code>Waiting</code> in strict FIFO order.</p>
                </div>
            </div>
            <div class="step-item">
                <div class="step-number">2</div>
                <div class="step-text">
                    <h5>Start Boarding</h5>
                    <p>When the current boarding vehicle is dispatched, click <strong>Start Boarding</strong> on the next waiting vehicle. Its status becomes <code>Boarding</code>, and the departure interval clock begins counting down immediately.</p>
                </div>
            </div>
            <div class="step-item">
                <div class="step-number">3</div>
                <div class="step-text">
                    <h5>Dispatch Vehicle</h5>
                    <p>Once full or the departure time is reached, click <strong>Depart Vehicle</strong>. The trip is logged to history, and the next waiting vehicle is promoted to Position #1.</p>
                </div>
            </div>
            <div class="alert-box">
                <i class="fas fa-stopwatch"></i> <strong>30-Minute Departure Cooldown:</strong> Departed vehicles cannot re-enter the queue for 30 minutes. If you attempt to add a vehicle that recently departed, the system informs you of the exact minutes remaining before it can be added back.
            </div>
        </div>
    </div>

    <!-- Tab 3: Passengers & Drivers -->
    <div id="tab-passengers" class="tab-content">
        <div class="manual-card">
            <h3><i class="fas fa-user-friends" style="color:#0ea5e9;"></i> Real-Time Passenger Loading & Driver Swaps</h3>
            <div class="step-item">
                <div class="step-number">1</div>
                <div class="step-text">
                    <h5>Live Passenger Counter</h5>
                    <p>Use the <strong>+</strong> and <strong>-</strong> buttons to record passengers boarding the vehicle. The system automatically clamps counts between <code>0</code> and the vehicle's maximum certified capacity (e.g., maximum 14 for vans).</p>
                </div>
            </div>
            <div class="step-item">
                <div class="step-number">2</div>
                <div class="step-text">
                    <h5>Full Vehicle Alert</h5>
                    <p>When capacity is reached, the passenger count badge turns red with <strong>FULL</strong>, signaling to passengers and terminal marshals that boarding is complete.</p>
                </div>
            </div>
            <div class="step-item">
                <div class="step-number">3</div>
                <div class="step-text">
                    <h5>Driver Swap on Duty</h5>
                    <p>To update the active driver for a trip, click the <strong>Driver Name</strong> in the queue table, type the new driver's name, and press Enter. The change is logged to the audit trail and broadcast to the public monitor immediately.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab 4: 1-Click Undo Cancel & Error Recovery -->
    <div id="tab-undo-cancel" class="tab-content">
        <div class="manual-card">
            <h3><i class="fas fa-rotate-left" style="color:#ef4444;"></i> Accidental Cancellation? Use 1-Click Undo Cancel!</h3>
            <p style="color:#64748b; font-size:14px;">
                Dispatchers work in fast-paced terminal environments. If you accidentally click <strong>Cancel Trip</strong> on a vehicle, do not panic!
            </p>
            <div class="rule-callout" style="border-left-color:#ef4444; background:#fef2f2; color:#991b1b;">
                <h5 style="margin:0 0 6px; font-weight:800;"><i class="fas fa-magic"></i> Instant Recovery with Undo Cancel:</h5>
                <ol style="margin:0; padding-left:20px;">
                    <li>Immediately after clicking Cancel, a temporary recovery notice will appear on your screen.</li>
                    <li>Click the <strong>Undo Cancel</strong> button on the notice.</li>
                    <li>The system restores the vehicle to its exact prior position in the queue and recalculates departure times automatically.</li>
                    <li>All public monitors immediately restore the vehicle to the live queue display.</li>
                </ol>
            </div>
            <div class="step-item">
                <div class="step-number">!</div>
                <div class="step-text">
                    <h5>Duplicate Plate Protection</h5>
                    <p>If you accidentally attempt to add a vehicle that is already waiting in the queue, the system prevents the duplicate entry and displays a warning: <em>"Vehicle ABC-1234 is already in the queue!"</em></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab 5: Announcements -->
    <div id="tab-announcements" class="tab-content">
        <div class="manual-card">
            <h3><i class="fas fa-bullhorn" style="color:#f59e0b;"></i> Publishing Terminal Advisories</h3>
            <p style="color:#64748b; font-size:14px;">
                Keep passengers informed during weather delays, temporary road blockages, or bay changes:
            </p>
            <div class="step-item">
                <div class="step-number">1</div>
                <div class="step-text">
                    <h5>Click Announcements in Navigation</h5>
                    <p>Navigate to <strong>Announcements</strong> (`/admin/announcements`) and click <strong>+ New Announcement</strong>.</p>
                </div>
            </div>
            <div class="step-item">
                <div class="step-number">2</div>
                <div class="step-text">
                    <h5>Type Clear Message & Publish</h5>
                    <p>Keep the message clear and concise. Once published, it appears on the public marquee header and announcement modal within milliseconds.</p>
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
