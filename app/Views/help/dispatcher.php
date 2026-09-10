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
                        2. Adding Vehicles to Queue
                    </h3>
                    <i class="fas fa-chevron-down accordion-header-icon"></i>
                </div>
                <div class="accordion-body">
                    <p>When a vehicle arrives at the terminal:</p>
                    <ol style="padding-left: 20px; margin: 0 0 10px 0;">
                        <li>On the <strong>Queue Management</strong> page, click <strong>Add Vehicle to Queue</strong>.</li>
                        <li>Select one or more available vehicles from the list (you can search by plate number or driver).</li>
                        <li>Click <strong>Add Selected to Queue</strong>.</li>
                    </ol>
                    <div class="step-box">
                        <strong>Initial Status:</strong> The vehicle appears in line with the <span class="status-badge status-waiting">Waiting</span> status.
                    </div>
                </div>
            </div>

            <!-- Step 3 -->
            <div class="accordion-item">
                <div class="accordion-header" onclick="toggleHelpAccordion(this)">
                    <h3 class="accordion-header-text">
                        3. Passenger Boarding & Countdown Timer
                    </h3>
                    <i class="fas fa-chevron-down accordion-header-icon"></i>
                </div>
                <div class="accordion-body">
                    <p>When a vehicle is ready to accept passengers:</p>
                    <ol style="padding-left: 20px; margin: 0 0 10px 0;">
                        <li>Click the status button on the vehicle to change it to <span class="status-badge status-boarding">Boarding</span>.</li>
                        <li>The <strong>countdown timer</strong> starts automatically based on the waiting time rule for that route.</li>
                        <li>Update the passenger count using the <strong>+</strong> and <strong>-</strong> buttons as passengers board.</li>
                    </ol>
                    <div class="step-box">
                        <strong>Public Screen Updates:</strong> The passenger count updates immediately on the public monitor screens so passengers know how many seats are left.
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
                    <p>A vehicle can be dispatched under two conditions:</p>
                    <div class="step-box">
                        <strong>Condition 1 (Full Capacity):</strong> All seats are filled (100%). You can depart the vehicle right away even if time remains on the timer.
                    </div>
                    <div class="step-box">
                        <strong>Condition 2 (Time Up):</strong> The countdown timer reaches 00:00. The timer flashes red, signaling that the vehicle must depart.
                    </div>
                    <p>Click <strong>Depart</strong> to mark the vehicle as <span class="status-badge status-departed">Departed</span>. This finishes the trip and records the departure time in the system logs. After departing, the vehicle has a 30-minute cooldown and will not appear in the Add to Queue list for 30 minutes.</p>
                </div>
            </div>

            <!-- Step 5 -->
            <div class="accordion-item">
                <div class="accordion-header" onclick="toggleHelpAccordion(this)">
                    <h3 class="accordion-header-text">
                        5. Driver Changes, Cancellations & Undo
                    </h3>
                    <i class="fas fa-chevron-down accordion-header-icon"></i>
                </div>
                <div class="accordion-body">
                    <p>If changes happen while managing the queue:</p>
                    <ol style="padding-left: 20px; margin: 0 0 10px 0;">
                        <li><strong>Change Driver:</strong> If another driver takes over the trip, click the driver's name on the card, type the new driver's name, and click <strong>Save Driver</strong>.</li>
                        <li><strong>Cancel Trip:</strong> If a vehicle breaks down or cannot travel, click <strong>Cancel</strong> to mark the trip as <span class="status-badge status-cancelled">Cancelled</span>.</li>
                        <li><strong>Undo Cancel:</strong> If you clicked Cancel by mistake, click <strong>Undo Cancel</strong> on that vehicle to immediately put it back into the queue.</li>
                    </ol>
                </div>
            </div>

            <!-- Step 6 -->
            <div class="accordion-item">
                <div class="accordion-header" onclick="toggleHelpAccordion(this)">
                    <h3 class="accordion-header-text">
                        6. Fares and Discounts
                    </h3>
                    <i class="fas fa-chevron-down accordion-header-icon"></i>
                </div>
                <div class="accordion-body">
                    <p>Check fares and discount rates anytime:</p>
                    <ol style="padding-left: 20px; margin: 0 0 10px 0;">
                        <li>Click <strong>Fares</strong> from the options menu.</li>
                        <li>Find any destination from Palompon to see the standard fares for Jeepneys and Vans.</li>
                        <li><strong>Discounts:</strong> Check discounted rates for eligible passenger groups (Senior Citizens, PWDs, and Students). Discount percentages vary by category.</li>
                    </ol>
                    <div class="step-box">
                        The fare table shows the regular fare and each applicable discount rate for easy reference.
                    </div>
                </div>
            </div>

            <!-- Step 7 -->
            <div class="accordion-item">
                <div class="accordion-header" onclick="toggleHelpAccordion(this)">
                    <h3 class="accordion-header-text">
                        7. Schedules & Announcements
                    </h3>
                    <i class="fas fa-chevron-down accordion-header-icon"></i>
                </div>
                <div class="accordion-body">
                    <p>Viewing schedules and passenger advisories:</p>
                    <ol style="padding-left: 20px; margin: 0 0 10px 0;">
                        <li>Click <strong>Schedules</strong> in the options menu to view departure timetables and active trips.</li>
                        <li>Click <strong>Announcements</strong> to read terminal advisories, weather updates, or travel notices.</li>
                    </ol>
                </div>
            </div>

            <!-- FAQ Section -->
            <div class="accordion-item">
                <div class="accordion-header" onclick="toggleHelpAccordion(this)">
                    <h3 class="accordion-header-text">
                        8. Frequently Asked Questions
                    </h3>
                    <i class="fas fa-chevron-down accordion-header-icon"></i>
                </div>
                <div class="accordion-body">
                    <div class="step-box">
                        <strong>Q: Why does a vehicle not appear in the Add to Queue list?</strong><br>
                        A: The vehicle might already be queued, an Admin set its status to Maintenance, or it recently departed (vehicles have a 30-minute cooldown after departure before they can be added to the queue again).
                    </div>
                    <div class="step-box">
                        <strong>Q: How do I change the driver of a queued vehicle?</strong><br>
                        A: Click the driver's name on the vehicle card in the queue, enter the new driver's name, and click Save Driver.
                    </div>
                    <div class="step-box">
                        <strong>Q: Can I undo an accidental departure?</strong><br>
                        A: Departed trips are saved in the departure logs. If a vehicle was marked departed by mistake, simply add it back to the queue.
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
