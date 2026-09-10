<?= view('templates/header', ['title' => $title]) ?>

<link rel="stylesheet" href="<?= base_url('assets/css/modern-frontend.css') ?>">
<style>
    .help-guide-wrapper {
        width: 95%;
        max-width: 1100px;
        margin: 28px auto 60px;
        box-sizing: border-box;
    }

    .help-card-container {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
        padding: 28px 32px;
        margin-bottom: 24px;
    }

    .help-card-header {
        display: flex;
        align-items: center;
        gap: 14px;
        padding-bottom: 20px;
        border-bottom: 1px solid #f1f5f9;
        margin-bottom: 10px;
    }

    .help-header-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        background: #fee2e2;
        color: #b71c1c;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        flex-shrink: 0;
    }

    .help-header-title {
        margin: 0;
        font-size: 20px;
        font-weight: 800;
        color: #0f172a;
        letter-spacing: -0.3px;
        font-family: var(--font-display, 'Outfit', sans-serif);
    }

    /* Accordion List */
    .accordion-list {
        display: flex;
        flex-direction: column;
    }

    .accordion-item {
        border-bottom: 1px solid #f1f5f9;
    }
    .accordion-item:last-child {
        border-bottom: none;
    }

    .accordion-header {
        padding: 18px 6px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        cursor: pointer;
        user-select: none;
        transition: background-color 0.15s ease;
        border-radius: 8px;
    }
    .accordion-header:hover {
        background-color: #f8fafc;
        padding-left: 10px;
        padding-right: 10px;
    }

    .accordion-header-text {
        font-size: 15px;
        font-weight: 700;
        color: #0f172a;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 10px;
        line-height: 1.4;
    }

    .accordion-header-icon {
        color: #b71c1c;
        font-size: 14px;
        transition: transform 0.25s cubic-bezier(.4, 0, .2, 1);
        flex-shrink: 0;
        margin-left: 16px;
    }

    .accordion-item.active .accordion-header-icon {
        transform: rotate(180deg);
    }

    .accordion-item.active .accordion-header-text {
        color: #b71c1c;
    }

    .accordion-body {
        display: none;
        padding: 0 10px 22px 10px;
        color: #334155;
        font-size: 14px;
        line-height: 1.65;
        animation: accordionFadeIn 0.2s ease;
    }

    .accordion-item.active .accordion-body {
        display: block;
    }

    @keyframes accordionFadeIn {
        from { opacity: 0; transform: translateY(-4px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Step content formatting */
    .step-box {
        background: #f8fafc;
        border-left: 3px solid #b71c1c;
        padding: 12px 16px;
        border-radius: 0 8px 8px 0;
        margin: 12px 0;
        font-size: 13.5px;
    }

    .step-box strong {
        color: #0f172a;
    }

    .role-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 3.5px 9px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #ffffff !important;
    }
    .role-super { background: #B71C1C !important; color: #ffffff !important; }
    .role-admin { background: #dc2626 !important; color: #ffffff !important; }
    .role-staff { background: #15803d !important; color: #ffffff !important; }

    .help-tip {
        background: #fff5f5;
        border: 1px solid #fed7d7;
        border-radius: 8px;
        padding: 10px 14px;
        margin-top: 10px;
        font-size: 13px;
        color: #9b2c2c;
        display: flex;
        align-items: flex-start;
        gap: 8px;
    }
    .help-tip i {
        margin-top: 3px;
    }

    @media (max-width: 640px) {
        .help-card-container {
            padding: 20px 16px;
            border-radius: 14px;
        }
        .accordion-header-text {
            font-size: 14px;
        }
    }
</style>

<div class="help-guide-wrapper">
    <!-- Main Guide Card -->
    <div class="help-card-container">
        <div class="help-card-header">
            <div class="help-header-icon">
                <i class="fas fa-circle-question"></i>
            </div>
            <h2 class="help-header-title">Administrator Help Guide</h2>
        </div>

        <div class="accordion-list">
            <!-- Step 1 -->
            <div class="accordion-item active">
                <div class="accordion-header" onclick="toggleHelpAccordion(this)">
                    <h3 class="accordion-header-text">
                        1. System Access & User Role Permissions
                    </h3>
                    <i class="fas fa-chevron-down accordion-header-icon"></i>
                </div>
                <div class="accordion-body">
                    <p>The Palompon Transit Terminal Management System utilizes role-based access control with distinct authority levels:</p>
                    <div class="step-box">
                        <span class="profile-role-pill pill-super_admin"><i class="fas fa-shield-alt"></i> Super Admin</span><br>
                        Has unrestricted master access to all system modules, including creating Administrator accounts, editing core terminal configurations, inspecting complete security audit logs, and running database maintenance.
                    </div>
                    <div class="step-box">
                        <span class="profile-role-pill pill-admin"><i class="fas fa-shield-alt"></i> Admin</span><br>
                        Manages day-to-day operations: registering vehicles, managing terminal settings, configuring routes and fares, setting departure waiting time rules, and publishing public announcements.
                    </div>
                    <div class="step-box">
                        <span class="profile-role-pill pill-staff"><i class="fas fa-user-gear"></i> Dispatcher</span><br>
                        Handles floor operations: adding arriving vehicles to the queue, managing passenger load, and dispatching trips on schedule.
                    </div>
                    <div class="help-tip">
                        <i class="fas fa-shield-alt"></i>
                        <span><strong>Security Rule:</strong> Only Super Administrators are permitted to create or modify Administrator user accounts.</span>
                    </div>
                </div>
            </div>

            <!-- Step 2 -->
            <div class="accordion-item">
                <div class="accordion-header" onclick="toggleHelpAccordion(this)">
                    <h3 class="accordion-header-text">
                        2. Terminal Facility Management
                    </h3>
                    <i class="fas fa-chevron-down accordion-header-icon"></i>
                </div>
                <div class="accordion-body">
                    <p>To view and manage terminal facility information:</p>
                    <ol style="padding-left: 20px; margin: 0 0 10px 0;">
                        <li>Navigate to <strong>Management &gt; Terminals</strong> from the options navigation menu.</li>
                        <li>Manage terminal information such as terminal name (e.g., <em>Palompon Central Terminal</em>), physical location, and vehicle holding capacity.</li>
                    </ol>
                    <div class="step-box">
                        <strong>Why Terminals Matter:</strong> Terminals serve as the origin point for transit routes and connect dispatchers to their local terminal view.
                    </div>
                </div>
            </div>

            <!-- Step 3 -->
            <div class="accordion-item">
                <div class="accordion-header" onclick="toggleHelpAccordion(this)">
                    <h3 class="accordion-header-text">
                        3. Route Setup
                    </h3>
                    <i class="fas fa-chevron-down accordion-header-icon"></i>
                </div>
                <div class="accordion-body">
                    <p>Routes connect Palompon Terminal to regional destinations:</p>
                    <ol style="padding-left: 20px; margin: 0 0 10px 0;">
                        <li>Go to <strong>Management &gt; Routes</strong>.</li>
                        <li>Click <strong>Add Route</strong> to register a new destination (e.g., <em>Palompon to Ormoc</em>, <em>Palompon to Tacloban</em>).</li>
                        <li>Enter the route name (origin and destination) and select the applicable vehicle type.</li>
                        <li>Specify the estimated trip duration in minutes for commuter scheduling.</li>
                    </ol>
                    <div class="help-tip">
                        <i class="fas fa-info-circle"></i>
                        <span>Routes determine which vehicles and fares are available for dispatcher queue assignments.</span>
                    </div>
                </div>
            </div>

            <!-- Step 4 -->
            <div class="accordion-item">
                <div class="accordion-header" onclick="toggleHelpAccordion(this)">
                    <h3 class="accordion-header-text">
                        4. Fares and Discounts
                    </h3>
                    <i class="fas fa-chevron-down accordion-header-icon"></i>
                </div>
                <div class="accordion-body">
                    <p>Fares and passenger discounts can be managed in the system:</p>
                    <ol style="padding-left: 20px; margin: 0 0 10px 0;">
                        <li>Go to <strong>Fares</strong> from the navigation menu to view and manage the fare table.</li>
                        <li>Fares and discount rates are configured per route and passenger category (such as Regular, Senior Citizen, PWD, and Student).</li>
                        <li>Each discount category has its own designated discount percentage configured in the system.</li>
                    </ol>
                    <div class="step-box">
                        <strong>Live Updates:</strong> Updating a fare or discount rate applies immediately across dispatcher views and public terminal displays.
                    </div>
                </div>
            </div>

            <!-- Step 5 -->
            <div class="accordion-item">
                <div class="accordion-header" onclick="toggleHelpAccordion(this)">
                    <h3 class="accordion-header-text">
                        5. Vehicle Registration
                    </h3>
                    <i class="fas fa-chevron-down accordion-header-icon"></i>
                </div>
                <div class="accordion-body">
                    <p>Only registered vehicles can be queued by dispatchers:</p>
                    <ol style="padding-left: 20px; margin: 0 0 10px 0;">
                        <li>Go to <strong>Management &gt; Vehicle Register</strong>.</li>
                        <li>Fill in the vehicle details: Operator Name, Driver Name, Plate Number, Vehicle Type, Destination, and Passenger Capacity.</li>
                        <li>Set the operational status to <strong>Active</strong> (ready for trips) or <strong>Maintenance</strong> (under repair).</li>
                        <li>Click <strong>Add</strong> to save the vehicle.</li>
                    </ol>
                    <div class="help-tip">
                        <i class="fas fa-exclamation-triangle"></i>
                        <span>Vehicles set to <em>Maintenance</em> are automatically excluded from the dispatcher's queue list.</span>
                    </div>
                </div>
            </div>

            <!-- Step 6 -->
            <div class="accordion-item">
                <div class="accordion-header" onclick="toggleHelpAccordion(this)">
                    <h3 class="accordion-header-text">
                        6. Departure Rules & Waiting Time
                    </h3>
                    <i class="fas fa-chevron-down accordion-header-icon"></i>
                </div>
                <div class="accordion-body">
                    <p>Departure rules control how long a vehicle waits for passengers before it has to depart:</p>
                    <ol style="padding-left: 20px; margin: 0 0 10px 0;">
                        <li>Go to <strong>Management &gt; Departure Rules</strong>.</li>
                        <li>Click <strong>Add Rule</strong>.</li>
                        <li><strong>Destination:</strong> Select a specific route destination, or leave it blank to apply as a terminal-wide rule.</li>
                        <li><strong>Active Hours (Time From / Time To):</strong> Set the hours when this rule is active (e.g., 05:00 to 18:00).</li>
                        <li><strong>Wait Time:</strong> Set how long the vehicle waits for passengers while boarding (e.g., 00:30 for 30 minutes).</li>
                        <li><strong>Rule Name (Label):</strong> Optional name to identify the rule (e.g., <em>Morning Rush</em> or <em>Regular</em>).</li>
                    </ol>
                    <div class="step-box">
                        <strong>Early Departure for Full Vehicles:</strong> When a vehicle reaches 100% capacity (all seats filled), the dispatcher can send it off immediately without waiting for the timer to run out.
                    </div>
                </div>
            </div>

            <!-- Step 7 -->
            <div class="accordion-item">
                <div class="accordion-header" onclick="toggleHelpAccordion(this)">
                    <h3 class="accordion-header-text">
                        7. Staff & Dispatcher Account Management
                    </h3>
                    <i class="fas fa-chevron-down accordion-header-icon"></i>
                </div>
                <div class="accordion-body">
                    <p>Administrators manage terminal floor staff accounts:</p>
                    <ol style="padding-left: 20px; margin: 0 0 10px 0;">
                        <li>Go to <strong>Management &gt; Users</strong>.</li>
                        <li>Click <strong>Create User</strong>, fill in the full name, username, email, and set the role to <em>Staff (Dispatcher)</em>.</li>
                        <li>Under <em>Assigned Routes</em>, select the specific routes this dispatcher is authorized to operate.</li>
                        <li>If a staff member is locked out after repeated incorrect password attempts, edit their account and click <strong>Unlock Account</strong> or reset their password.</li>
                    </ol>
                </div>
            </div>

            <!-- Step 8 -->
            <div class="accordion-item">
                <div class="accordion-header" onclick="toggleHelpAccordion(this)">
                    <h3 class="accordion-header-text">
                        8. Public Announcements & Travel Advisories
                    </h3>
                    <i class="fas fa-chevron-down accordion-header-icon"></i>
                </div>
                <div class="accordion-body">
                    <p>Broadcast advisories to the public display monitors and passenger portal:</p>
                    <ol style="padding-left: 20px; margin: 0 0 10px 0;">
                        <li>Open <strong>Announcements</strong> from the navigation menu.</li>
                        <li>Click <strong>Create Announcement</strong>.</li>
                        <li>Enter title, advisory message, and select priority level: <em>Normal</em>, <em>High</em>, or <em>Urgent</em>.</li>
                        <li>Urgent and High priority advisories immediately trigger the top scrolling marquee ticker across all terminal monitors and passenger views.</li>
                    </ol>
                </div>
            </div>

            <!-- Step 9 -->
            <div class="accordion-item">
                <div class="accordion-header" onclick="toggleHelpAccordion(this)">
                    <h3 class="accordion-header-text">
                        9. Audit Logs, Departure History & Official Reports
                    </h3>
                    <i class="fas fa-chevron-down accordion-header-icon"></i>
                </div>
                <div class="accordion-body">
                    <p>Review system activities and generate official terminal operations documentation:</p>
                    <div class="step-box">
                        <strong>Security Audit Logs:</strong> Click <strong>Logs</strong> in the navigation menu to review all user logins, failed attempts, fare rate changes, vehicle modifications, and record deletions with exact timestamps and IP addresses.
                    </div>
                    <div class="step-box">
                        <strong>Departure History & Reports:</strong> Click <strong>History</strong> in the navigation menu. Filter trips by date range, route, or vehicle type. Click <strong>Generate Report</strong> to preview or <strong>Print</strong> to export official records for municipal transport reporting.
                    </div>
                </div>
            </div>

            <!-- FAQ Section -->
            <div class="accordion-item">
                <div class="accordion-header" onclick="toggleHelpAccordion(this)">
                    <h3 class="accordion-header-text">
                        10. Frequently Asked Questions & Common Solutions
                    </h3>
                    <i class="fas fa-chevron-down accordion-header-icon"></i>
                </div>
                <div class="accordion-body">
                    <div class="step-box">
                        <strong>Q: How do I assign a dispatcher to a specific terminal or route?</strong><br>
                        A: Go to <em>Management &gt; Users</em>, edit the dispatcher's profile, and check the routes/terminals they are assigned to manage.
                    </div>
                    <div class="step-box">
                        <strong>Q: What happens if a route's fare changes due to a new LTFRB order?</strong><br>
                        A: Go to the <em>Fares</em> page and update the fare amount for the applicable route and passenger category. The fare tables update immediately system-wide.
                    </div>
                    <div class="step-box">
                        <strong>Q: A dispatcher account is locked due to repeated failed logins. How do I unlock it?</strong><br>
                        A: Open <em>Management &gt; Users</em>, edit the locked account, and clear the lock or provide a new password. You can also inspect the failed attempt logs under <em>Logs</em>.
                    </div>
                    <div class="step-box">
                        <strong>Q: A vehicle is not appearing in the dispatcher's queue dropdown. Why?</strong><br>
                        A: Check <em>Management &gt; Vehicle Register</em>. Ensure the vehicle is registered, its status is set to <em>Active</em> rather than <em>Maintenance</em>, and that it has not departed within the last 30 minutes (30-minute cooldown period).
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function toggleHelpAccordion(headerEl) {
        var item = headerEl.closest('.accordion-item');
        if (!item) return;

        var isActive = item.classList.contains('active');
        
        // Optional: close other open accordions for clean single view
        // document.querySelectorAll('.accordion-item').forEach(function(i) { i.classList.remove('active'); });

        if (isActive) {
            item.classList.remove('active');
        } else {
            item.classList.add('active');
        }
    }
</script>

<?= view('templates/footer') ?>
