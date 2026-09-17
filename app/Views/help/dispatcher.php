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
        background: #dcfce7;
        color: #047857;
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
        color: #047857;
        font-size: 14px;
        transition: transform 0.25s cubic-bezier(.4, 0, .2, 1);
        flex-shrink: 0;
        margin-left: 16px;
    }

    .accordion-item.active .accordion-header-icon {
        transform: rotate(180deg);
    }

    .accordion-item.active .accordion-header-text {
        color: #047857;
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
        border-left: 3px solid #047857;
        padding: 12px 16px;
        border-radius: 0 8px 8px 0;
        margin: 12px 0;
        font-size: 13.5px;
    }

    .step-box strong {
        color: #0f172a;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        padding: 2px 8px;
        border-radius: 6px;
        font-size: 11.5px;
        font-weight: 700;
    }
    .status-waiting { background: #f1f5f9; color: #475569; }
    .status-boarding { background: #dbeafe; color: #1e40af; }
    .status-departed { background: #dcfce7; color: #166534; }
    .status-delayed { background: #fef3c7; color: #92400e; }
    .status-cancelled { background: #fee2e2; color: #991b1b; }

    .help-tip {
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
        border-radius: 8px;
        padding: 10px 14px;
        margin-top: 10px;
        font-size: 13px;
        color: #166534;
        display: flex;
        align-items: flex-start;
        gap: 8px;
    }
    .help-tip i {
        margin-top: 3px;
        color: #15803d;
    }

    @media (max-width: 768px) {
        .help-guide-wrapper {
            width: 96%;
            margin: 18px auto 40px;
        }
        .help-card-container {
            padding: 22px 18px;
            border-radius: 16px;
        }
    }

    @media (max-width: 530px) {
        .help-guide-wrapper {
            width: 100%;
            padding: 0 10px;
            margin: 14px auto 30px;
        }
        .help-card-container {
            padding: 16px 12px;
            border-radius: 14px;
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
            <h2 class="help-header-title">Dispatcher Help Guide & Operational Procedures</h2>
        </div>

        <div class="accordion-list">
            <!-- Step 1 -->
            <div class="accordion-item active">
                <div class="accordion-header" onclick="toggleHelpAccordion(this)">
                    <h3 class="accordion-header-text">
                        1. Shift Start & Terminal Assignment
                    </h3>
                    <i class="fas fa-chevron-down accordion-header-icon"></i>
                </div>
                <div class="accordion-body">
                    <p>Follow these steps at the beginning of each dispatch shift:</p>
                    <ol style="padding-left: 20px; margin: 0 0 10px 0;">
                        <li>Log in to your Dispatcher account with your username and password.</li>
                        <li>Open the options menu and click <strong>Queue Management</strong>.</li>
                        <li>Check your assigned terminal (e.g. <em>Palompon Central Terminal</em>) and active routes.</li>
                        <li>Check the queue list to see waiting and boarding vehicles.</li>
                    </ol>
                    <div class="help-tip">
                        <i class="fas fa-info-circle"></i>
                        <span>If your assigned route or terminal is not showing, ask an Administrator to check your account route assignments.</span>
                    </div>
                </div>
            </div>

            <!-- Step 2 -->
            <div class="accordion-item">
                <div class="accordion-header" onclick="toggleHelpAccordion(this)">
                    <h3 class="accordion-header-text">
                        2. Adding Vehicles to Queue & Departure Cooldown
                    </h3>
                    <i class="fas fa-chevron-down accordion-header-icon"></i>
                </div>
                <div class="accordion-body">
                    <p>When an authorized vehicle arrives at the terminal staging area:</p>
                    <ol style="padding-left: 20px; margin: 0 0 10px 0;">
                        <li>On the <strong>Queue Management</strong> page, inspect the <em>Available Vehicles</em> pool.</li>
                        <li>Each vehicle card shows its official registration photo (or vehicle type badge), plate number, driver, and destination. You can search by plate or driver name.</li>
                        <li>Select one or more vehicles and click <strong>+ Add Selected to Queue</strong>.</li>
                    </ol>
                    <div class="step-box">
                        <strong>The Departure Cooldown:</strong> To guarantee fair rotation among operators, recently departed vehicles cannot be re-queued immediately until their cooldown interval expires (default is 30 minutes, or as customized by the Superadmin in System Settings). If an operator arrives early, the system displays: <em>"Vehicle departed recently. Please wait about X more minute(s) before adding it back."</em> The vehicle unlocks automatically once the cooldown period has elapsed.
                    </div>
                    <div class="step-box">
                        <strong>Initial Status:</strong> Upon entry, the vehicle is placed in chronological FIFO order with the <span class="status-badge status-waiting">Waiting</span> status.
                    </div>
                </div>
            </div>

            <!-- Step 3 -->
            <div class="accordion-item">
                <div class="accordion-header" onclick="toggleHelpAccordion(this)">
                    <h3 class="accordion-header-text">
                        3. Passenger Boarding, Counter Clamping & Countdown
                    </h3>
                    <i class="fas fa-chevron-down accordion-header-icon"></i>
                </div>
                <div class="accordion-body">
                    <p>When the vehicle advances into the active terminal boarding bay:</p>
                    <ol style="padding-left: 20px; margin: 0 0 10px 0;">
                        <li>Click the action button to transition the vehicle to <span class="status-badge status-boarding">Boarding</span>.</li>
                        <li>The <strong>headway countdown timer</strong> starts automatically based on the active departure rule for that route.</li>
                        <li>Update passenger counts using the <strong>+</strong> and <strong>-</strong> buttons (or enter the number directly).</li>
                    </ol>
                    <div class="step-box">
                        <strong>Automatic Seating Capacity Clamping:</strong> The passenger counter is debounced for network performance and strictly clamped between <code>0</code> and the vehicle's maximum registered capacity. You can never accidentally enter 16 passengers into a 14-passenger UV Express van.
                    </div>
                    <div class="help-tip">
                        <i class="fas fa-info-circle"></i>
                        <span>Passenger seat progress updates on public monitor screens within sub-seconds via real-time WebSocket sync.</span>
                    </div>
                </div>
            </div>

            <!-- Step 4 -->
            <div class="accordion-item">
                <div class="accordion-header" onclick="toggleHelpAccordion(this)">
                    <h3 class="accordion-header-text">
                        4. Dispatching Vehicles (Departure)
                    </h3>
                    <i class="fas fa-chevron-down accordion-header-icon"></i>
                </div>
                <div class="accordion-body">
                    <p>A vehicle is dispatched under two operational conditions:</p>
                    <div class="step-box">
                        <strong>Condition 1 (Full Capacity):</strong> When all seats are filled (100%), the vehicle displays <code>FULL</code>. The dispatcher can click <strong>Depart</strong> immediately without waiting for the timer to expire.
                    </div>
                    <div class="step-box">
                        <strong>Condition 2 (Timer Reaches 00:00):</strong> When the headway countdown expires, the timer flashes red, indicating mandatory departure to maintain headway frequency.
                    </div>
                    <p>Clicking <strong>Depart</strong> marks the trip as <span class="status-badge status-departed">Departed</span>, logs the departure timestamp in official ledgers, and initiates the post-departure cooldown period.</p>
                </div>
            </div>

            <!-- Step 5 -->
            <div class="accordion-item">
                <div class="accordion-header" onclick="toggleHelpAccordion(this)">
                    <h3 class="accordion-header-text">
                        5. Inline Driver Changes, Cancellations & 1-Click Undo
                    </h3>
                    <i class="fas fa-chevron-down accordion-header-icon"></i>
                </div>
                <div class="accordion-body">
                    <p>Manage unexpected changes on the terminal floor without losing queue priority:</p>
                    <ol style="padding-left: 20px; margin: 0 0 10px 0;">
                        <li><strong>Driver Change:</strong> If a relief driver takes the wheel, click the driver's name on the queue card, type the new driver's name, and click <strong>Save Driver</strong>. The change is saved and broadcasted to public monitors.</li>
                        <li><strong>Trip Cancellation:</strong> If a vehicle suffers a mechanical issue, click <strong>Cancel</strong> to set the trip to <span class="status-badge status-cancelled">Cancelled</span>.</li>
                        <li><strong>1-Click Undo Cancel:</strong> If Cancel was clicked accidentally, an immediate <strong>Undo Cancel</strong> button appears on the notification banner and queue row. Clicking it restores the vehicle to the queue without loss of timestamp or line position.</li>
                    </ol>
                </div>
            </div>

            <!-- Step 6 -->
            <div class="accordion-item">
                <div class="accordion-header" onclick="toggleHelpAccordion(this)">
                    <h3 class="accordion-header-text">
                        6. Fares & Statutory Concessions Reference
                    </h3>
                    <i class="fas fa-chevron-down accordion-header-icon"></i>
                </div>
                <div class="accordion-body">
                    <p>Dispatchers can consult official LTFRB distance-based tariffs at any time:</p>
                    <ol style="padding-left: 20px; margin: 0 0 10px 0;">
                        <li>Click <strong>Fares</strong> from the navigation menu to review base fares for Jeepneys, Vans, and Minibuses.</li>
                        <li>Check authorized 20% statutory discount rates for eligible groups (Students, OSCA Senior Citizens, and PWDs).</li>
                    </ol>
                    <div class="step-box">
                        Use this reference if commuters or drivers have questions regarding exact legal fares.
                    </div>
                </div>
            </div>

            <!-- Step 7 -->
            <div class="accordion-item">
                <div class="accordion-header" onclick="toggleHelpAccordion(this)">
                    <h3 class="accordion-header-text">
                        7. Timetables & Emergency Announcements
                    </h3>
                    <i class="fas fa-chevron-down accordion-header-icon"></i>
                </div>
                <div class="accordion-body">
                    <p>Stay informed about operational changes and municipal advisories:</p>
                    <ol style="padding-left: 20px; margin: 0 0 10px 0;">
                        <li>Click <strong>Schedules</strong> to inspect daily trip timetables and departure intervals.</li>
                        <li>Click <strong>Announcements</strong> to create and broadcast weather warnings, road delay advisories, or port closure notices. High priority advisories scroll immediately across all terminal displays.</li>
                    </ol>
                </div>
            </div>

            <!-- Step 8 -->
            <div class="accordion-item">
                <div class="accordion-header" onclick="toggleHelpAccordion(this)">
                    <h3 class="accordion-header-text">
                        8. Account Security & Password Change with Email Verification
                    </h3>
                    <i class="fas fa-chevron-down accordion-header-icon"></i>
                </div>
                <div class="accordion-body">
                    <p>Dispatchers can update their account credentials securely without administrator intervention:</p>
                    <ol style="padding-left: 20px; margin: 0 0 10px 0;">
                        <li>Navigate to <strong>Change Password</strong> from your user menu (<code>/change-password</code>).</li>
                        <li>Click <strong>Send Verification Code</strong>. The system emails a secure 6-digit code valid for 15 minutes.</li>
                        <li>Enter your current password, new password, and the 6-digit code.</li>
                        <li>Click <strong>Update Password</strong>. Your password updates immediately with zero downtime.</li>
                    </ol>
                    <div class="help-tip">
                        <i class="fas fa-shield-alt"></i>
                        <span>Failed login attempts are monitored. Accounts lock for 15 minutes after 5 consecutive failed attempts to prevent unauthorized access.</span>
                    </div>
                </div>
            </div>

            <!-- FAQ Section -->
            <div class="accordion-item">
                <div class="accordion-header" onclick="toggleHelpAccordion(this)">
                    <h3 class="accordion-header-text">
                        9. Frequently Asked Questions
                    </h3>
                    <i class="fas fa-chevron-down accordion-header-icon"></i>
                </div>
                <div class="accordion-body">
                    <div class="step-box">
                        <strong>Q: Why does a vehicle not appear in the Add to Queue list?</strong><br>
                        A: Ensure the vehicle is set to <em>Active</em> rather than <em>Maintenance</em>, is assigned to one of your authorized routes, is not already queued, and has completed its post-departure cooldown period (default 30 minutes, or as set by the Superadmin).
                    </div>
                    <div class="step-box">
                        <strong>Q: What should I do if the live WebSocket connection indicator turns red or yellow?</strong><br>
                        A: The system automatically falls back to background HTTP polling every 20 seconds. You can continue managing the queue normally; the system will reconnect to WebSockets as soon as network stability returns.
                    </div>
                    <div class="step-box">
                        <strong>Q: Can I depart a vehicle before the timer runs out?</strong><br>
                        A: Yes! If a vehicle reaches 100% capacity (all seats occupied), you can depart it immediately to keep passenger flow moving smoothly.
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
