<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Commuter User Guide - Palompon Transit') ?></title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= base_url('images/9HFScgVg_400x400.png') ?>">
    <link rel="shortcut icon" href="<?= base_url('favicon.ico') ?>">
    <link rel="apple-touch-icon" href="<?= base_url('apple-touch-icon.png') ?>">

    <!-- Modern Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= base_url('assets/css/design-system.css') ?>?v=3.2">
    <link rel="stylesheet" href="<?= base_url('assets/css/modern-frontend.css') ?>?v=3.2">
    <link rel="stylesheet" href="<?= base_url('assets/css/responsive.css') ?>?v=3.2">

    <style>
        :root {
            --primary: #B71C1C;
            --primary-dark: #7F0000;
            --accent: #FFA726;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --bg-body: #f8fafc;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background: var(--bg-body);
            color: var(--text-main);
            line-height: 1.6;
            margin: 0;
            padding: 0;
        }

        .guide-container {
            width: 90%;
            max-width: 1400px;
            margin: 25px auto 40px !important;
            padding: 0 10px;
            box-sizing: border-box;
        }

        .guide-hero {
            background: linear-gradient(135deg, #B71C1C 0%, #7F0000 100%);
            color: white;
            border-radius: 20px;
            padding: 36px 32px;
            margin-bottom: 28px;
            box-shadow: 0 10px 25px -5px rgba(183, 28, 28, 0.3);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
            width: 100%;
            box-sizing: border-box;
        }

        .guide-hero h1 {
            color: #ffffff !important;
            font-size: clamp(20px, 3.5vw, 28px);
            font-weight: 800;
            margin: 0 0 10px;
            display: flex;
            align-items: center;
            gap: 12px;
            line-height: 1.3;
        }

        .guide-hero p {
            font-size: 15px;
            color: #fee2e2 !important;
            margin: 0;
            max-width: 680px;
            line-height: 1.6;
        }

        .guide-hero-actions {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .guide-nav-pills {
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

        .guide-nav-pills::-webkit-scrollbar {
            height: 4px;
        }
        .guide-nav-pills::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }

        .guide-pill-btn {
            background: white;
            border: 1px solid #cbd5e1;
            color: #475569;
            padding: 10px 20px;
            border-radius: 12px;
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

        .guide-pill-btn:hover {
            background: #f1f5f9;
            color: #0f172a;
        }

        .guide-pill-btn.active {
            background: #B71C1C;
            border-color: #B71C1C;
            color: white;
            box-shadow: 0 4px 12px rgba(183, 28, 28, 0.25);
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
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(6px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .guide-card {
            background: white;
            border-radius: 16px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            padding: 30px;
            margin-bottom: 24px;
            width: 100%;
            box-sizing: border-box;
        }

        .guide-card h3 {
            font-size: clamp(17px, 2.8vw, 20px);
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 18px;
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
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: #fee2e2;
            color: #B71C1C;
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
            font-size: 16px;
            font-weight: 700;
            margin: 0 0 4px;
            color: #1e293b;
        }

        .step-text p {
            font-size: 14px;
            color: #64748b;
            margin: 0;
            line-height: 1.6;
        }

        .badge-demo {
            display: inline-flex;
            align-items: center;
            padding: 4px 12px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 12.5px;
            gap: 6px;
            white-space: nowrap;
        }
        .badge-demo.boarding { background: #dcfce7; color: #166534; }
        .badge-demo.waiting { background: #fef3c7; color: #92400e; }
        .badge-demo.next { background: #ffedd5; color: #9a3412; }
        .badge-demo.full { background: #fee2e2; color: #991b1b; }
        .badge-demo.departed { background: #f1f5f9; color: #475569; }

        .callout-box {
            background: #f8fafc;
            border-left: 4px solid #B71C1C;
            padding: 16px 20px;
            border-radius: 0 12px 12px 0;
            margin: 18px 0;
            font-size: 14px;
            color: #334155;
            line-height: 1.65;
            box-sizing: border-box;
            width: 100%;
        }

        /* Responsive Table Container */
        .table-responsive {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            margin: 16px 0;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            box-sizing: border-box;
        }
        .table-guide {
            width: 100%;
            min-width: 580px;
            border-collapse: collapse;
            margin: 0;
            font-size: 14px;
        }
        .table-guide th {
            background: #f8fafc;
            padding: 12px 14px;
            text-align: left;
            font-weight: 700;
            color: #475569;
            border-bottom: 2px solid #e2e8f0;
        }
        .table-guide td {
            padding: 12px 14px;
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
        }

        /* Mobile Media Queries */
        @media (max-width: 768px) {
            .guide-container {
                width: 94% !important;
                margin: 16px auto 30px !important;
                padding: 0 !important;
            }
            .guide-hero {
                padding: 24px 20px;
                border-radius: 16px;
                margin-bottom: 20px;
            }
            .guide-hero h1 {
                font-size: 22px;
            }
            .guide-hero p {
                font-size: 13.5px;
            }
            .guide-hero-actions {
                width: 100%;
            }
            .guide-hero-actions .btn {
                width: 100%;
                justify-content: center;
            }
            .guide-card {
                padding: 20px 16px;
                border-radius: 14px;
                margin-bottom: 18px;
            }
            .step-item {
                gap: 12px;
            }
        }

        @media (max-width: 480px) {
            .guide-hero h1 {
                font-size: 19px;
            }
            .guide-pill-btn {
                padding: 8px 14px;
                font-size: 13px;
                border-radius: 10px;
            }
            .callout-box {
                padding: 12px 14px;
                font-size: 13px;
            }
        }
    </style>
</head>
<body>

    <?= view('templates/guest_header', [
        'announcements'      => $announcements ?? [],
        'breadcrumb_current' => 'User Guide',
    ]) ?>

    <div class="guide-container">
        <!-- Hero Section -->
        <div class="guide-hero">
            <div>
                <h1><i class="fas fa-compass"></i> Commuter & Passenger User Guide</h1>
                <p>Learn how to track terminal queues in real time, plan trip departure schedules, look up official fares and discounts, and troubleshoot common travel questions.</p>
            </div>
            <div class="guide-hero-actions">
                <a href="<?= base_url('guest') ?>" class="btn btn--secondary btn--sm" style="background:white; color:#B71C1C; font-weight:700; border:none; padding:10px 18px; border-radius:10px; text-decoration:none; display:inline-flex; align-items:center; gap:8px;">
                    <i class="fas fa-tv"></i> Live Terminal Monitor
                </a>
            </div>
        </div>

        <!-- Tab Navigation -->
        <div class="guide-nav-pills">
            <button class="guide-pill-btn active" onclick="switchGuideTab('tab-live-queue', this)">
                <i class="fas fa-bus"></i> Live Queue & Status
            </button>
            <button class="guide-pill-btn" onclick="switchGuideTab('tab-fares', this)">
                <i class="fas fa-tags"></i> Fares & Discounts (20%)
            </button>
            <button class="guide-pill-btn" onclick="switchGuideTab('tab-schedules', this)">
                <i class="fas fa-calendar-alt"></i> Schedules & Search
            </button>
            <button class="guide-pill-btn" onclick="switchGuideTab('tab-recovery', this)">
                <i class="fas fa-life-ring" style="color:#ef4444;"></i> Error Recovery & Help
            </button>
            <button class="guide-pill-btn" onclick="switchGuideTab('tab-faq', this)">
                <i class="fas fa-question-circle"></i> FAQ & Contacts
            </button>
        </div>

        <!-- Tab 1: Live Queue & Status -->
        <div id="tab-live-queue" class="tab-content active">
            <div class="guide-card">
                <h3><i class="fas fa-satellite-dish" style="color:#B71C1C;"></i> Reading the Live Terminal Queue</h3>
                <p style="color:#64748b; font-size:14px;">The Terminal Queue on the home page refreshes in real-time. Here is how to understand what you see:</p>
                
                <div class="table-responsive">
                    <table class="table-guide">
                        <thead>
                            <tr>
                                <th>Status Badge</th>
                                <th>Meaning</th>
                                <th>What You Should Do</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><span class="badge-demo boarding"><i class="fas fa-door-open"></i> BOARDING</span></td>
                                <td>Vehicle is parked at the active boarding bay.</td>
                                <td>Proceed to the designated bay immediately to pay fare and board.</td>
                            </tr>
                            <tr>
                                <td><span class="badge-demo next"><i class="fas fa-arrow-right"></i> NEXT IN LINE</span></td>
                                <td>Next vehicle scheduled to move into the boarding bay.</td>
                                <td>Prepare your luggage and be ready to board once the current vehicle departs.</td>
                            </tr>
                            <tr>
                                <td><span class="badge-demo waiting"><i class="fas fa-clock"></i> WAITING</span></td>
                                <td>Vehicle has checked into the terminal staging area.</td>
                                <td>Relax in the passenger waiting lounge; vehicle will queue up shortly.</td>
                            </tr>
                            <tr>
                                <td><span class="badge-demo full"><i class="fas fa-users"></i> FULL</span></td>
                                <td>All passenger seats are occupied.</td>
                                <td>Wait for the next vehicle in line to begin boarding.</td>
                            </tr>
                            <tr>
                                <td><span class="badge-demo departed"><i class="fas fa-check"></i> DEPARTED</span></td>
                                <td>Vehicle has left the terminal grounds.</td>
                                <td>The next scheduled trip is now in position.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="callout-box">
                    <i class="fas fa-lightbulb" style="color:#eab308; margin-right:8px;"></i>
                    <strong>Estimated Departure Time (ETD):</strong> The ETD shows the target departure clock. If a vehicle fills to capacity before the scheduled time, it may depart early to provide faster travel!
                </div>
            </div>
        </div>

        <!-- Tab 2: Fares & Discounts -->
        <div id="tab-fares" class="tab-content">
            <div class="guide-card">
                <h3><i class="fas fa-tags" style="color:#10b981;"></i> Official Fares & Statutory 20% Discounts</h3>
                <p style="color:#64748b; font-size:14px;">All route fares are regulated by LTFRB and the Municipality of Palompon. Check rates at <code>/fares</code>.</p>
                
                <div class="step-item">
                    <div class="step-number">1</div>
                    <div class="step-text">
                        <h5>Select Your Route</h5>
                        <p>Choose from standard certified routes: Palompon to Ormoc, Tacloban, Isabel, Naval, or Kananga.</p>
                    </div>
                </div>
                <div class="step-item">
                    <div class="step-number">2</div>
                    <div class="step-text">
                        <h5>Standard 20% Discount Concession</h5>
                        <p>Under Philippine law, the following passengers are entitled to a 20% discount on regular fares upon presenting valid identification:</p>
                        <ul style="margin:8px 0 0; color:#475569; font-size:13.5px;">
                            <li><strong>Students</strong>: Elementary, High School, Vocational, and College students (present valid school ID).</li>
                            <li><strong>Senior Citizens</strong>: Filipino citizens aged 60 and above (present OSCA Senior Citizen ID).</li>
                            <li><strong>Persons with Disability (PWD)</strong>: Persons with certified physical or mental disabilities (present official National PWD ID).</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab 3: Schedules & Search -->
        <div id="tab-schedules" class="tab-content">
            <div class="guide-card">
                <h3><i class="fas fa-calendar-alt" style="color:#3b82f6;"></i> Timetables & Trip Search</h3>
                <div class="step-item">
                    <div class="step-number">1</div>
                    <div class="step-text">
                        <h5>Daily Departure Schedules (/schedules)</h5>
                        <p>Browse full operating timetables, first-trip and last-trip hours, and typical departure frequencies across all certified operators.</p>
                    </div>
                </div>
                <div class="step-item">
                    <div class="step-number">2</div>
                    <div class="step-text">
                        <h5>Instant Autocomplete Search (/search)</h5>
                        <p>Type any destination (e.g. <em>"Ormoc"</em>), plate number, or vehicle type in the search bar. The system suggests matching active trips in real time.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab 4: Error Recovery & Help -->
        <div id="tab-recovery" class="tab-content">
            <div class="guide-card">
                <h3><i class="fas fa-life-ring" style="color:#ef4444;"></i> Help Recognizing & Recovering from Common Issues</h3>
                <p style="color:#64748b; font-size:14px; margin-bottom:20px;">What to do if you encounter unexpected travel situations:</p>

                <div class="callout-box" style="border-left-color:#ef4444; background:#fef2f2;">
                    <h5 style="margin:0 0 6px; font-weight:800; color:#991b1b;"><i class="fas fa-search"></i> 1. Search Query Returns "No Trips Found"</h5>
                    <p style="margin:0; color:#991b1b;"><strong>What happened:</strong> The vehicle plate or keyword searched has no active trips today.</p>
                    <p style="margin:4px 0 0; color:#991b1b;"><strong>Recovery:</strong> Check the spelling of your destination or click one of the quick destination chips (Ormoc, Tacloban, Isabel) to view all current trips for that city.</p>
                </div>

                <div class="callout-box" style="border-left-color:#f59e0b; background:#fffbeb;">
                    <h5 style="margin:0 0 6px; font-weight:800; color:#92400e;"><i class="fas fa-wifi"></i> 2. Connection Dropped or "Reconnecting..." Badge</h5>
                    <p style="margin:0; color:#92400e;"><strong>What happened:</strong> Mobile signal momentarily dropped while traveling through cellular dead zones.</p>
                    <p style="margin:4px 0 0; color:#92400e;"><strong>Recovery:</strong> You do not need to refresh the page. The system automatically reconnects with exponential backoff and pulls the latest queue as soon as service returns.</p>
                </div>

                <div class="callout-box" style="border-left-color:#3b82f6; background:#eff6ff;">
                    <h5 style="margin:0 0 6px; font-weight:800; color:#1e40af;"><i class="fas fa-ticket-alt"></i> 3. Overcharging or Fare Discrepancy</h5>
                    <p style="margin:0; color:#1e40af;"><strong>What happened:</strong> A conductor requests an amount higher than the approved LTFRB fare table.</p>
                    <p style="margin:4px 0 0; color:#1e40af;"><strong>Recovery:</strong> Check the official rate on the <strong>Fares</strong> page (`/fares`) and present the rate. You can also click <strong>Report Issue</strong> in the footer to submit an official complaint directly to terminal supervisors.</p>
                </div>
            </div>
        </div>

        <!-- Tab 5: FAQ & Contacts -->
        <div id="tab-faq" class="tab-content">
            <div class="guide-card">
                <h3><i class="fas fa-question-circle" style="color:#eab308;"></i> Frequently Asked Questions</h3>
                <div class="step-item">
                    <div class="step-number">?</div>
                    <div class="step-text">
                        <h5>Can I reserve seats through this website?</h5>
                        <p>No. This website is a real-time monitor. Seats are occupied on a first-come, first-served basis at the terminal boarding bays.</p>
                    </div>
                </div>
                <div class="step-item">
                    <div class="step-number">?</div>
                    <div class="step-text">
                        <h5>What are terminal operating hours?</h5>
                        <p>The Palompon Terminal operates daily from <strong>4:00 AM to 8:00 PM</strong>.</p>
                    </div>
                </div>
                <div class="step-item">
                    <div class="step-number">?</div>
                    <div class="step-text">
                        <h5>Terminal Office Contact</h5>
                        <p>Palompon Transit Terminal, Rizal Street, Palompon, Leyte 6538.<br>Telephone: (053) 555-8376 / (053) 338-2022 | Email: arclast988@gmail.com</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?= $this->include('templates/guestfooter') ?>

    <script>
    function switchGuideTab(tabId, btn) {
        document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
        document.querySelectorAll('.guide-pill-btn').forEach(el => el.classList.remove('active'));
        const target = document.getElementById(tabId);
        if (target) {
            target.classList.add('active');
            btn.classList.add('active');
        }
    }
    </script>
</body>
</html>
