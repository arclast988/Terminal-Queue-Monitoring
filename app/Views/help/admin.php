<?php
$adminContentPrefix = ($role ?? '') === 'super_admin' ? 'content_superadmin' : 'content_admin';
$adminManagedKeys = array_values(array_filter(
    array_keys(\Config\ContentManagement::fields()),
    static fn (string $key): bool => str_starts_with($key, $adminContentPrefix . '_')
));
$adminManagedContent = managed_content_overrides($adminManagedKeys);
?>
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

    @media (max-width: 768px) {
        .help-guide-wrapper {
            width: 100% !important;
            margin: 10px auto 24px !important;
            padding: 0 !important;
        }
        .help-card-container {
            padding: 20px 16px !important;
            border-radius: 16px !important;
        }
    }

    @media (max-width: 530px) {
        .help-guide-wrapper {
            width: 100% !important;
            padding: 0 !important;
            margin: 6px auto 20px !important;
        }
        .help-card-container {
            padding: 16px 12px !important;
            border-radius: 14px !important;
        }
        .help-card-header {
            gap: 10px;
            padding-bottom: 14px;
            margin-bottom: 6px;
        }
        .help-header-icon {
            width: 36px;
            height: 36px;
            font-size: 17px;
            border-radius: 10px;
        }
        .help-header-title {
            font-size: 16px;
        }
        .accordion-header {
            padding: 12px 6px;
        }
        .accordion-header-text {
            font-size: 13.5px;
            gap: 8px;
        }
        .accordion-body {
            padding: 0 2px 14px;
            font-size: 13px;
            line-height: 1.55;
        }
        .step-box {
            padding: 9px 12px;
            font-size: 12.5px;
            margin: 8px 0;
        }
        .help-tip {
            padding: 8px 10px;
            font-size: 12px;
        }
    }

    @media (max-width: 380px) {
        .help-guide-wrapper {
            padding: 0 6px;
        }
        .help-card-container {
            padding: 12px 8px;
        }
        .help-header-title {
            font-size: 15px;
        }
        .accordion-header-text {
            font-size: 12.8px;
        }
        .step-box {
            padding: 7px 9px;
            font-size: 12px;
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
            <h2 class="help-header-title"><?= ($role ?? '') === 'super_admin' ? 'Super Administrator Help Guide' : 'Administrator Help Guide' ?></h2>
        </div>

        <div class="accordion-list">
            <!-- Step 1 -->
            <div class="accordion-item">
                <div class="accordion-header" onclick="toggleHelpAccordion(this)">
                    <h3 class="accordion-header-text">
                        1. System Access & User Role Permissions
                    </h3>
                    <i class="fas fa-chevron-down accordion-header-icon"></i>
                </div>
                <div class="accordion-body">
                    <p>The <?= esc(app_system_title()) ?> utilizes role-based access control with distinct authority levels:</p>
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
                        5. Vehicle Registration, Photo Upload & Vehicle Types
                    </h3>
                    <i class="fas fa-chevron-down accordion-header-icon"></i>
                </div>
                <div class="accordion-body">
                    <p>Only registered vehicles in active status can enter the terminal queue:</p>
                    <ol style="padding-left: 20px; margin: 0 0 10px 0;">
                        <li>Navigate to <strong>Management &gt; Vehicle Register</strong>.</li>
                        <li>Click <strong>+ Add Vehicle</strong> (or edit an existing vehicle).</li>
                        <li><strong>Vehicle Technical Details:</strong> Enter Plate Number (e.g. <code>ABC-1234</code>), Operator Name, Default Driver, Destination, and Seating Capacity.</li>
                        <li><strong>Vehicle Registration Photo:</strong> Drag-and-drop or select an official vehicle image (JPG, PNG, WEBP up to 2MB). The upload card displays a real-time preview and includes a one-click <em>Remove Photo</em> button.</li>
                        <li><strong>Operational Status:</strong> Set to <strong>Active</strong> (ready for queuing) or <strong>Maintenance</strong> (temporarily out of service).</li>
                        <li>Click <strong>Save Vehicle</strong>. The vehicle photo and details immediately appear in the queue pool.</li>
                    </ol>
                    <div class="step-box">
                        <strong>Dynamic Vehicle Types (Super Admin & Admin):</strong> Under the <em>Vehicle Types</em> management tab, administrators can define and configure new vehicle classifications (e.g., <em>PUJ / Jeepney</em>, <em>UV Express Van</em>, <em>Modern Minibus</em>, <em>Bus</em>) with customized icon badges, theme colors, and default capacities.
                    </div>
                    <div class="help-tip">
                        <i class="fas fa-exclamation-triangle"></i>
                        <span>Vehicles marked as <em>Maintenance</em> or currently serving the post-departure cooldown (configurable in <em>System Settings &gt; Retention & Queue Rules</em>, default 30 minutes) are automatically hidden from the dispatcher's check-in list.</span>
                    </div>
                </div>
            </div>

            <!-- Step 6 -->
            <div class="accordion-item">
                <div class="accordion-header" onclick="toggleHelpAccordion(this)">
                    <h3 class="accordion-header-text">
                        6. Departure Rules & Headway Scheduling
                    </h3>
                    <i class="fas fa-chevron-down accordion-header-icon"></i>
                </div>
                <div class="accordion-body">
                    <p>Departure rules establish target headway intervals based on route demand and time of day:</p>
                    <ol style="padding-left: 20px; margin: 0 0 10px 0;">
                        <li>Go to <strong>Management &gt; Departure Rules</strong>.</li>
                        <li>Click <strong>+ Add Rule</strong>.</li>
                        <li><strong>Destination:</strong> Select a specific destination route (e.g., <em>Ormoc</em>) or leave blank to establish a terminal-wide default.</li>
                        <li><strong>24-Hour Coverage (Time From / Time To):</strong> Define the rule's active hours. Rules now cover all intervals seamlessly, including the midnight-to-morning period (<code>00:00:00</code> to <code>04:00:00</code>).</li>
                        <li><strong>Headway Wait Time:</strong> Set the waiting interval (e.g., <code>15</code> minutes for peak rush hours, <code>30</code> minutes for regular daytime).</li>
                        <li><strong>Rule Label:</strong> Enter an identifiable name (e.g., <em>Morning Peak Rush</em> or <em>Regular Daytime Headway</em>).</li>
                    </ol>
                    <div class="step-box">
                        <strong>Automatic ETD Calculation & Full Capacity:</strong> When a dispatcher places a vehicle into <em>Boarding</em> status, the system calculates the Estimated Departure Time automatically. If 100% seating capacity is reached ahead of time, the dispatcher can dispatch the vehicle immediately.
                    </div>
                </div>
            </div>

            <!-- Step 7 -->
            <div class="accordion-item">
                <div class="accordion-header" onclick="toggleHelpAccordion(this)">
                    <h3 class="accordion-header-text">
                        7. Staff Accounts, Security & Route Jurisdictions
                    </h3>
                    <i class="fas fa-chevron-down accordion-header-icon"></i>
                </div>
                <div class="accordion-body">
                    <p>Administrators manage terminal floor staff accounts and maintain strict access boundaries:</p>
                    <ol style="padding-left: 20px; margin: 0 0 10px 0;">
                        <li>Go to <strong>Management &gt; Users</strong>.</li>
                        <li>Click <strong>Create User</strong>, fill in the full name, username, official email, and select <em>Staff (Dispatcher)</em> or <em>Admin</em>.</li>
                        <li><strong>Route Jurisdictions:</strong> Select the authorized routes for each dispatcher under <em>Assigned Routes</em>. Dispatchers are strictly restricted to queueing and boarding vehicles on their assigned routes.</li>
                        <li><strong>Profile Avatars:</strong> Staff and administrators can upload a personalized profile avatar directly from their profile menu.</li>
                        <li><strong>Account Lockout Protection:</strong> Accounts are automatically locked for 15 minutes after 5 consecutive failed login attempts. An administrator can unlock the account or trigger an OTP password reset at any time.</li>
                        <li><strong>Dispatcher Change Password Authentication:</strong> Dispatchers can securely update their passwords via <code>/change-password</code> by entering a 6-digit verification code sent to their registered email.</li>
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
                        <strong>Security Audit Logs:</strong> Click <strong>Logs</strong> in the navigation menu to review all user logins, failed attempts, fare rate changes, vehicle modifications, and record deletions with exact timestamps and IP addresses. Records are kept according to the Superadmin retention policy (default: 60 days, customizable in System Settings).
                    </div>
                    <div class="step-box">
                        <strong>Departure History & Reports:</strong> Click <strong>History</strong> in the navigation menu. Filter trips by date range, route, or vehicle type. Click <strong>Generate Report</strong> to preview or <strong>Print</strong> to export official records for municipal transport reporting. Completed records are maintained per the Superadmin departure history retention policy (default: 60 days).
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
                        <strong>Q: How do I upload or change a vehicle registration photo?</strong><br>
                        A: In <em>Management &gt; Vehicle Register</em>, click Edit on any vehicle. Use the drag-and-drop photo uploader to select a JPG, PNG, or WEBP image up to 2MB. Click Save Vehicle. The image renders across queue management and search displays.
                    </div>
                    <div class="step-box">
                        <strong>Q: How do I configure data retention or the vehicle queue cooldown interval?</strong><br>
                        A: Superadmins can navigate to <em>System Settings</em> (top right profile &gt; System Settings or <code>/admin/settings</code>) and click the <strong>Retention & Queue Rules</strong> tab. You can adjust the Vehicle Departure Cooldown (in minutes or hours, default 30 mins) and set custom retention days for Audit Logs and Departure History.
                    </div>
                    <div class="step-box">
                        <strong>Q: How do I assign a dispatcher to a specific terminal or route?</strong><br>
                        A: Go to <em>Management &gt; Users</em>, edit the dispatcher's profile, and check the routes they are authorized to manage under Assigned Routes.
                    </div>
                    <div class="step-box">
                        <strong>Q: What happens if a route's fare changes due to a new LTFRB order?</strong><br>
                        A: Go to the <em>Fares</em> page and update the base fare or per-kilometer rate. The updated pricing and 20% statutory discounts recalculate immediately system-wide.
                    </div>
                    <div class="step-box">
                        <strong>Q: A dispatcher account is locked due to repeated failed logins. How do I unlock it?</strong><br>
                        A: Open <em>Management &gt; Users</em>, edit the locked account, and click <strong>Unlock Account</strong> or reset their password. You can review failed attempts under <em>Logs</em>.
                    </div>
                    <div class="step-box">
                        <strong>Q: A vehicle is not appearing in the dispatcher's queue dropdown. Why?</strong><br>
                        A: Check <em>Management &gt; Vehicle Register</em>. Ensure the vehicle is active rather than in Maintenance, and that it has completed its post-departure cooldown period (configurable by Superadmin in Settings, default 30 minutes).
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?= base_url('assets/js/managed-content.js?v=20260920') ?>"></script>
<script type="application/json" id="adminManagedContent"><?= json_encode($adminManagedContent, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?></script>
<script>
    if (window.ManagedContent) {
        window.ManagedContent.applyGuide(window.ManagedContent.readPayload('adminManagedContent'), {
            root: '.help-card-container .accordion-list',
            title: '.help-header-title',
            prefix: <?= json_encode($adminContentPrefix) ?>
        });
    }

    function toggleHelpAccordion(headerEl) {
        var item = headerEl.closest('.accordion-item');
        if (!item) return;

        var accordionList = item.closest('.accordion-list');
        var isActive = item.classList.contains('active');

        if (accordionList) {
            accordionList.querySelectorAll('.accordion-item.active').forEach(function(openItem) {
                if (openItem !== item) openItem.classList.remove('active');
            });
        }

        item.classList.toggle('active', !isActive);
    }
</script>

<?= view('templates/footer') ?>
