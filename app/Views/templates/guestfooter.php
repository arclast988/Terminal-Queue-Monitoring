<!-- ===== Footer ===== -->
<footer>
    <div class="footer-container">
        <div class="footer-content">
            <div class="f-about">
                <h3><?= esc(app_footer_about_title()) ?></h3>
                <p><?= esc(app_footer_about_text()) ?></p>
            </div>
            <div class="f-links">
                <h4>Quick Links</h4>
                <ul>
                    <li><a href="<?= base_url('guest') ?>">Terminal Monitor</a></li>
                    <li><a href="<?= base_url('schedules') ?>">Schedules</a></li>
                    <li><a href="<?= base_url('fares') ?>">Route Fares</a></li>
                    <li><a href="<?= base_url('search') ?>">Trip Search</a></li>
                    <?php if (session()->get('isLoggedIn')): ?>
                        <?php
                        $dashUrl = '/';
                        if (in_array(session()->get('role'), ['super_admin', 'admin'], true)) $dashUrl = '/admin/dashboard';
                        elseif (session()->get('role') === 'staff') $dashUrl = '/staff/dashboard';
                        ?>
                        <li><a href="<?= base_url($dashUrl) ?>"><i class="fas fa-th-large"></i> Dashboard</a></li>
                    <?php else: ?>
                        <li><a href="<?= base_url('login') ?>">Staff & Admin Login</a></li>
                    <?php endif; ?>
                </ul>
            </div>
            <div class="f-links">
                <h4>Support</h4>
                <ul>
                    <li><a href="javascript:void(0)" onclick="event.preventDefault(); openSupportModal('helpModal')"><i class="fas fa-life-ring" style="margin-right: 6px; color: #38bdf8;"></i> Help Guide</a></li>
                    <li><a href="javascript:void(0)" onclick="event.preventDefault(); openSupportModal('reportModal')"><i class="fas fa-exclamation-triangle" style="margin-right: 6px; color: #f87171;"></i> Report Issue</a></li>
                    <li><a href="javascript:void(0)" onclick="event.preventDefault(); openSupportModal('contactModal')"><i class="fas fa-envelope-open-text" style="margin-right: 6px; color: #4ade80;"></i> Contact Us</a></li>
                    <li><a href="javascript:void(0)" onclick="event.preventDefault(); openSupportModal('faqModal')"><i class="fas fa-question-circle" style="margin-right: 6px; color: #facc15;"></i> FAQ</a></li>
                    <li><a href="javascript:void(0)" onclick="event.preventDefault(); openSupportModal('termsModal')"><i class="fas fa-file-contract" style="margin-right: 6px; color: #818cf8;"></i> Terms of Service</a></li>
                </ul>
            </div>
            <div class="f-links">
                <h4>Contact</h4>
                <ul>
                    <li style="font-size: 14px; color: #cbd5e1;"><i class="fas fa-map-marker-alt" style="margin-right: 10px; color: var(--primary, #C62828);"></i> <?= esc(app_contact_address()) ?></li>
                    <li style="font-size: 14px; color: #cbd5e1;">
                        <a href="tel:<?= esc(preg_replace('/[^0-9+]/', '', app_contact_phone())) ?>" style="color: inherit; text-decoration: none; display: inline-flex; align-items: center; transition: color 0.2s ease;">
                            <i class="fas fa-phone" style="margin-right: 10px; color: var(--primary, #C62828);"></i> <?= esc(app_contact_phone()) ?>
                        </a>
                    </li>
                    <li style="font-size: 14px; color: #cbd5e1;">
                        <a href="mailto:<?= esc(app_contact_email() ?: (config('Email')->recipients ?: 'arclast988@gmail.com')) ?>" style="color: inherit; text-decoration: none; display: inline-flex; align-items: center; transition: color 0.2s ease;">
                            <i class="fas fa-envelope" style="margin-right: 10px; color: var(--primary, #C62828);"></i> <?= esc(app_contact_email() ?: (config('Email')->recipients ?: 'arclast988@gmail.com')) ?>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
        <div class="f-copyright">
            <?= app_footer_copyright() ?>
        </div>
    </div>
</footer>

<!-- ===== Modals ===== -->

<!-- 1. Help Guide Modal (Consolidated Commuter User Manual & Operational Guide) -->
<div id="helpModal" class="support-modal-backdrop">
    <div class="support-modal">
        <button class="close-modal" onclick="closeSupportModal('helpModal')">&times;</button>
        <h3><i class="fas fa-life-ring" style="color:#0284c7;"></i> Commuter Help Guide & Travel Assistance</h3>
        <p style="font-size:14px;color:#64748b;margin-bottom:20px;">Official commuter guidance for tracking live queues, boarding status, LTFRB route fares, and daily schedules at <?= esc(app_name()) ?> Terminal.</p>

        <div class="accordion-list">
            <!-- Item 1: Live Terminal Queue & Boarding -->
            <div class="accordion-item">
                <div class="accordion-header" onclick="toggleHelpAccordion(this)">
                    <h4 class="accordion-header-text"><i class="fas fa-tv" style="color:#0284c7;"></i> 1. Live Terminal Queue & Boarding Badges</h4>
                    <i class="fas fa-chevron-down accordion-header-icon"></i>
                </div>
                <div class="accordion-body">
                    <p>The Terminal Monitor displays arriving, queued, and departing public utility vehicles connecting Palompon to regional destinations. Each vehicle displays a real-time status badge:</p>
                    <div class="step-box">
                        <span class="status-badge status-boarding"><i class="fas fa-door-open"></i> Boarding</span><br>
                        <strong>Actively Loading:</strong> The vehicle is physically stationed at the terminal bay. Passengers are paying fares and taking seats. Head to the bay promptly to secure your ride.
                    </div>
                    <div class="step-box">
                        <span class="status-badge status-waiting"><i class="fas fa-hourglass-half"></i> Waiting</span><br>
                        <strong>In Queue:</strong> The vehicle has checked into the terminal and is in line. When the current boarding vehicle leaves, Position #1 moves into the boarding bay.
                    </div>
                    <div class="step-box">
                        <span class="status-badge status-full"><i class="fas fa-user-check"></i> FULL</span><br>
                        <strong>Capacity Reached:</strong> All passenger seats are taken. The vehicle will dispatch immediately. Passengers should queue for the next vehicle in line.
                    </div>
                    <div class="step-box">
                        <span class="status-badge status-departed"><i class="fas fa-check-circle"></i> Departed</span><br>
                        <strong>Trip Dispatched:</strong> The vehicle has departed the terminal. Departure time is archived in official records.
                    </div>
                </div>
            </div>

            <!-- Item 2: Trip Search & Vehicle Filtering -->
            <div class="accordion-item">
                <div class="accordion-header" onclick="toggleHelpAccordion(this)">
                    <h4 class="accordion-header-text"><i class="fas fa-search" style="color:#0284c7;"></i> 2. Searching Trips & Filtering Vehicle Types</h4>
                    <i class="fas fa-chevron-down accordion-header-icon"></i>
                </div>
                <div class="accordion-body">
                    <p>You can quickly find your vehicle or route using the interactive filters and search bar:</p>
                    <ol style="padding-left: 20px; margin: 0 0 10px 0;">
                        <li><strong>Vehicle Type Filter:</strong> Switch between <em>All</em>, <em>PUJ (Jeepney)</em>, <em>UV Express Van</em>, and <em>Modern Minibus</em> on the monitor to see only your preferred transit option.</li>
                        <li><strong>Trip Search:</strong> Visit the <a href="<?= base_url('search') ?>" style="color:#0284c7; font-weight:700; text-decoration:none;">Trip Search page</a> to search by license plate number, destination municipality, or driver name.</li>
                    </ol>
                    <div class="help-tip">
                        <i class="fas fa-info-circle"></i>
                        <span>Search results display the vehicle's exact current bay position, remaining seats, and estimated departure countdown.</span>
                    </div>
                </div>
            </div>

            <!-- Item 3: Daily Schedules & Departure Headways -->
            <div class="accordion-item">
                <div class="accordion-header" onclick="toggleHelpAccordion(this)">
                    <h4 class="accordion-header-text"><i class="fas fa-clock" style="color:#0284c7;"></i> 3. Departure Schedules & Headway Rules</h4>
                    <i class="fas fa-chevron-down accordion-header-icon"></i>
                </div>
                <div class="accordion-body">
                    <p>Estimated Departure Times (ETD) are governed by official municipal headway rules:</p>
                    <div class="step-box">
                        <strong>Headway Wait Timers:</strong> During peak commute hours (morning and late afternoon), departures are spaced closely (typically every 15–20 minutes). During off-peak periods, wait intervals are typically 30–45 minutes.
                    </div>
                    <div class="step-box">
                        <strong>Early Departure on Full Capacity:</strong> Vehicles do not need to wait for the timer to reach zero. Once 100% of seats are filled, the dispatcher sends the vehicle immediately for passenger convenience.
                    </div>
                    <p>Check complete daily timetables by visiting the <a href="<?= base_url('schedules') ?>" style="color:#0284c7; font-weight:700; text-decoration:none;">Schedules page</a>.</p>
                </div>
            </div>

            <!-- Item 4: Fares & Statutory Discounts -->
            <div class="accordion-item">
                <div class="accordion-header" onclick="toggleHelpAccordion(this)">
                    <h4 class="accordion-header-text"><i class="fas fa-tags" style="color:#0284c7;"></i> 4. Route Fares & 20% Statutory Discounts</h4>
                    <i class="fas fa-chevron-down accordion-header-icon"></i>
                </div>
                <div class="accordion-body">
                    <p>All passenger fares strictly comply with official LTFRB fare matrices determined by highway kilometers:</p>
                    <div class="step-box">
                        <strong>Statutory 20% Discount:</strong> Under Philippine Law (RA 9994, RA 10754, RA 11314), a <strong>20% discount</strong> is granted to:
                        <ul style="margin: 6px 0 0 16px; padding: 0;">
                            <li><strong>Students:</strong> Enrolled elementary, high school, vocational, or undergraduate students (present valid school ID).</li>
                            <li><strong>Senior Citizens:</strong> Filipino citizens aged 60 and above (present OSCA Senior Citizen ID).</li>
                            <li><strong>Persons with Disability (PWD):</strong> Registered PWDs (present valid National PWD ID).</li>
                        </ul>
                    </div>
                    <p>To view official base fares and calculate discounted fares for all routes, visit the <a href="<?= base_url('fares') ?>" style="color:#0284c7; font-weight:700; text-decoration:none;">Route Fares page</a>.</p>
                </div>
            </div>

            <!-- Item 5: Real-Time Sync & Failover -->
            <div class="accordion-item">
                <div class="accordion-header" onclick="toggleHelpAccordion(this)">
                    <h4 class="accordion-header-text"><i class="fas fa-wifi" style="color:#0284c7;"></i> 5. Real-Time Updates & Network Failover</h4>
                    <i class="fas fa-chevron-down accordion-header-icon"></i>
                </div>
                <div class="accordion-body">
                    <p>The system utilizes sub-second WebSocket synchronization to push queue changes directly to your device:</p>
                    <div class="step-box">
                        <strong>Zero Manual Refreshing Needed:</strong> When a dispatcher updates passenger counts or boards a new vehicle, your screen updates instantly without reloading the page.
                    </div>
                    <div class="step-box">
                        <strong>Automatic Network Recovery:</strong> If your mobile signal fluctuates or drops, the system seamlessly transitions to background HTTP polling every 20 seconds and automatically reconnects once your connection stabilizes.
                    </div>
                </div>
            </div>

            <!-- Item 6: Commuter Troubleshooting & Issue Reporting -->
            <div class="accordion-item">
                <div class="accordion-header" onclick="toggleHelpAccordion(this)">
                    <h4 class="accordion-header-text"><i class="fas fa-wrench" style="color:#0284c7;"></i> 6. Commuter Troubleshooting & Reporting Issues</h4>
                    <i class="fas fa-chevron-down accordion-header-icon"></i>
                </div>
                <div class="accordion-body">
                    <p>If you encounter unexpected situations while planning your commute:</p>
                    <div class="step-box">
                        <strong>Queue Appears Inactive:</strong> If departure times seem unchanged, verify your internet connection. You can perform a quick browser pull-to-refresh to fetch fresh queue states.
                    </div>
                    <div class="step-box">
                        <strong>Reporting Overcharging or Lost Belongings:</strong> If an operator charges above the official LTFRB fare or if you misplaced items at the terminal, click <a href="javascript:void(0)" onclick="closeSupportModal('helpModal'); openSupportModal('reportModal');" style="color:#ef4444; font-weight:700;">Report Issue</a> or <a href="javascript:void(0)" onclick="closeSupportModal('helpModal'); openSupportModal('contactModal');" style="color:#16a34a; font-weight:700;">Contact Us</a>. Submissions are delivered directly to municipal terminal administrators.
                    </div>
                </div>
            </div>

            <!-- Item 7: Mobile Web & Home Screen Access -->
            <div class="accordion-item">
                <div class="accordion-header" onclick="toggleHelpAccordion(this)">
                    <h4 class="accordion-header-text"><i class="fas fa-mobile-screen-button" style="color:#0284c7;"></i> 7. Mobile Browser & Smartphone Tips</h4>
                    <i class="fas fa-chevron-down accordion-header-icon"></i>
                </div>
                <div class="accordion-body">
                    <p>No App Store or Play Store download is required. For fast, one-tap access on your phone:</p>
                    <ol style="padding-left: 20px; margin: 0 0 10px 0;">
                        <li><strong>Android (Chrome):</strong> Tap the three-dot browser menu &rarr; tap <em>"Add to Home screen"</em> or <em>"Install app"</em>.</li>
                        <li><strong>iPhone (Safari):</strong> Tap the Share button &rarr; scroll down and tap <em>"Add to Home Screen"</em>.</li>
                    </ol>
                    <div class="help-tip">
                        <i class="fas fa-check-circle"></i>
                        <span>This creates a dedicated full-screen icon on your phone that launches the terminal monitor instantly like a native app.</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 2. Report Issue Modal -->
<div id="reportModal" class="support-modal-backdrop">
    <div class="support-modal">
        <button class="close-modal" onclick="closeSupportModal('reportModal')">&times;</button>
        <h3><i class="fas fa-exclamation-triangle" style="color:#ef4444;"></i> Report an Issue</h3>
        <?php if (session()->getFlashdata('contact_success')): ?>
        <div class="alert-success-banner"><i class="fas fa-check-circle"></i> <?= session()->getFlashdata('contact_success') ?></div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('contact_error')): ?>
        <div class="alert-error-banner"><i class="fas fa-times-circle"></i> <?= session()->getFlashdata('contact_error') ?></div>
        <?php endif; ?>
        <form action="<?= base_url('contact/send') ?>" method="post">
            <?= csrf_field() ?>
            <input type="hidden" name="type" value="report">
            <div class="form-group">
                <label>Your Name *</label>
                <input type="text" name="name" placeholder="e.g. Juan Dela Cruz" required>
            </div>
            <div class="form-group">
                <label>Your Email *</label>
                <input type="email" name="email" placeholder="your@email.com" required>
            </div>
            <div class="form-group">
                <label>Issue Type</label>
                <select name="subject">
                    <option>Incorrect schedule or departure time</option>
                    <option>Missing vehicle from queue</option>
                    <option>Website display / technical problem</option>
                    <option>Incorrect fare information / overcharging</option>
                    <option>Lost and found inquiry</option>
                    <option>Other operational issue</option>
                </select>
            </div>
            <div class="form-group">
                <label>Describe the Issue *</label>
                <textarea name="message" placeholder="Please provide plate number, destination, date/time, and a brief description..." required></textarea>
            </div>
            <button type="submit" class="submit-btn"><i class="fas fa-paper-plane"></i> Submit Report</button>
        </form>
    </div>
</div>

<!-- 3. Contact Us Modal -->
<div id="contactModal" class="support-modal-backdrop">
    <div class="support-modal">
        <button class="close-modal" onclick="closeSupportModal('contactModal')">&times;</button>
        <h3><i class="fas fa-envelope-open-text" style="color:#16a34a;"></i> Contact Us</h3>
        <?php if (session()->getFlashdata('contact_success')): ?>
        <div class="alert-success-banner"><i class="fas fa-check-circle"></i> <?= session()->getFlashdata('contact_success') ?></div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('contact_error')): ?>
        <div class="alert-error-banner"><i class="fas fa-times-circle"></i> <?= session()->getFlashdata('contact_error') ?></div>
        <?php endif; ?>
        <p style="font-size:14px;color:#64748b;margin-bottom:20px;">Send a message to <?= esc(app_name()) ?> Terminal administration. We are committed to safe, reliable public transport.</p>
        <form action="<?= base_url('contact/send') ?>" method="post">
            <?= csrf_field() ?>
            <input type="hidden" name="type" value="contact">
            <div class="form-group">
                <label>Your Name *</label>
                <input type="text" name="name" placeholder="e.g. Maria Santos" required>
            </div>
            <div class="form-group">
                <label>Your Email *</label>
                <input type="email" name="email" placeholder="your@email.com" required>
            </div>
            <div class="form-group">
                <label>Subject</label>
                <input type="text" name="subject" placeholder="e.g. Inquiring about special holiday trip schedules">
            </div>
            <div class="form-group">
                <label>Message *</label>
                <textarea name="message" placeholder="Write your inquiry or feedback here..." required></textarea>
            </div>
            <button type="submit" class="submit-btn"><i class="fas fa-paper-plane"></i> Send Message</button>
        </form>
    </div>
</div>

<!-- 4. FAQ Modal -->
<div id="faqModal" class="support-modal-backdrop">
    <div class="support-modal">
        <button class="close-modal" onclick="closeSupportModal('faqModal')">&times;</button>
        <h3><i class="fas fa-question-circle" style="color:#eab308;"></i> Frequently Asked Questions</h3>
        <div class="faq-item">
            <button class="faq-question" onclick="toggleFaq(this)">What are the terminal operating hours? <i class="fas fa-chevron-down"></i></button>
            <div class="faq-answer">The <?= esc(app_name()) ?> Terminal operates daily from <strong>4:00 AM to 8:00 PM</strong>. Earliest trips dispatch starting at 4:30 AM, with frequent departures throughout peak commute hours. Check the Live Monitor or Schedules page for real-time departure status.</div>
        </div>
        <div class="faq-item">
            <button class="faq-question" onclick="toggleFaq(this)">Can I purchase tickets or book seats online? <i class="fas fa-chevron-down"></i></button>
            <div class="faq-answer">No. This platform is a real-time public information monitor. In accordance with municipal and LTFRB regulations, passenger fares and ticketing are handled in cash directly at the terminal boarding bays or with the vehicle conductor.</div>
        </div>
        <div class="faq-item">
            <button class="faq-question" onclick="toggleFaq(this)">What vehicle types operate from this terminal? <i class="fas fa-chevron-down"></i></button>
            <div class="faq-answer">The terminal services <strong>PUJs (Jeepneys)</strong>, <strong>UV Express Vans</strong>, and <strong>Modern Minibuses</strong> across certified regional routes including Ormoc City, Tacloban City, Isabel, Naval, and Kananga. You can filter the queue and fare matrices by vehicle type.</div>
        </div>
        <div class="faq-item">
            <button class="faq-question" onclick="toggleFaq(this)">How accurate are the estimated departure times (ETD)? <i class="fas fa-chevron-down"></i></button>
            <div class="faq-answer">Estimated Departure Times are automatically calculated based on official departure headway rules set by terminal administration. <em>Note:</em> If a vehicle reaches 100% seating capacity earlier than scheduled, it will dispatch immediately for passenger convenience.</div>
        </div>
        <div class="faq-item">
            <button class="faq-question" onclick="toggleFaq(this)">Who is eligible for the 20% statutory fare discount? <i class="fas fa-chevron-down"></i></button>
            <div class="faq-answer">Under Philippine law (RA 9994, RA 10754, RA 11314), a <strong>20% discount</strong> applies to currently enrolled <strong>Students</strong> (with valid school ID), <strong>Senior Citizens</strong> aged 60+ (with OSCA ID), and <strong>Persons with Disability</strong> (with National PWD ID). Visit the Fares page to view discounted rates.</div>
        </div>
        <div class="faq-item">
            <button class="faq-question" onclick="toggleFaq(this)">How often does the live queue update? <i class="fas fa-chevron-down"></i></button>
            <div class="faq-answer">Queue updates occur in sub-seconds via real-time WebSocket communication. When a vehicle boards or departs, your screen updates instantly without requiring a page refresh. If your internet connection drops, the system falls back to automatic background HTTP polling.</div>
        </div>
        <div class="faq-item">
            <button class="faq-question" onclick="toggleFaq(this)">How do I report overcharging, lost items, or file complaints? <i class="fas fa-chevron-down"></i></button>
            <div class="faq-answer">Use the "Report Issue" or "Contact Us" forms in this support section. Please include the route, vehicle plate number, date, and time. Messages are routed directly to terminal management at <a href="mailto:<?= esc(config('Email')->recipients ?: 'arclast988@gmail.com') ?>" style="color:#0284c7; font-weight:600;"><?= esc(config('Email')->recipients ?: 'arclast988@gmail.com') ?></a>.</div>
        </div>
    </div>
</div>

<!-- 5. Terms of Service Modal -->
<div id="termsModal" class="support-modal-backdrop">
    <div class="support-modal">
        <button class="close-modal" onclick="closeSupportModal('termsModal')">&times;</button>
        <h3><i class="fas fa-file-contract" style="color:#6366f1;"></i> Terms of Service</h3>
        <div style="font-size: 14px; color: #4b5563; line-height: 1.6; max-height: 60vh; overflow-y: auto; padding-right: 10px;">
            <p><strong>Last updated: September 2026</strong></p>
            <h4 style="color: #1f2937; margin: 15px 0 5px;">1. Acceptance of Terms</h4>
            <p>By accessing and using the <?= esc(app_system_title()) ?> ("<?= esc(app_acronym()) ?> System"), you agree to be bound by these Terms of Service and all applicable municipal guidelines. If you do not agree with any portion of these terms, please discontinue use of this service.</p>

            <h4 style="color: #1f2937; margin: 15px 0 5px;">2. Description of Service</h4>
            <p>The <?= esc(app_acronym()) ?> System is a municipal transit monitoring platform providing real-time information regarding vehicle queue positions, departure headway estimates, and official route fares for the Municipality of Palompon, Leyte. This public service is maintained and operated by <?= esc(app_footer_credit()) ?>.</p>

            <h4 style="color: #1f2937; margin: 15px 0 5px;">3. Use of Transit Information & Estimates</h4>
            <ul>
                <li>All queue statuses, seat counts, and timetables are provided for public convenience and travel planning.</li>
                <li>Estimated Departure Times (ETD) are operational targets. Vehicles may depart ahead of schedule upon reaching 100% capacity, or experience minor delays due to prevailing road, weather, or traffic conditions.</li>
                <li>The system serves as an information monitor and does not support advance seat reservations, online ticketing, or digital fare transactions.</li>
            </ul>

            <h4 style="color: #1f2937; margin: 15px 0 5px;">4. Fare Schedules & Statutory Concessions</h4>
            <p>All route fares displayed conform to official LTFRB tariff guidelines. Statutory 20% discounts for Students, Senior Citizens (OSCA), and Persons with Disability (PWD) are mandated by national law and require presentation of valid government or institutional identification upon boarding.</p>

            <h4 style="color: #1f2937; margin: 15px 0 5px;">5. Disclaimer of Warranties</h4>
            <p>The <?= esc(app_acronym()) ?> System is provided on an "as is" and "as available" basis without warranties of any kind, whether express or implied. While terminal dispatchers endeavor to maintain continuous accuracy, we do not warrant that service will be uninterrupted or entirely error-free under adverse network conditions.</p>

            <h4 style="color: #1f2937; margin: 15px 0 5px;">6. Limitation of Liability</h4>
            <p><?= esc(app_name()) ?> Terminal and its administrators shall not be liable for any direct, indirect, incidental, or consequential loss resulting from reliance on displayed departure schedules, missed connections, or unforeseen operational changes.</p>

            <h4 style="color: #1f2937; margin: 15px 0 5px;">7. Privacy & Support Communications</h4>
            <p>This system does not harvest personal user tracking data from guest commuters. Inquiries and operational issue reports submitted through the Support section are transmitted securely via email to terminal management solely for verification and resolution purposes, and are never shared with unauthorized third parties.</p>

            <h4 style="color: #1f2937; margin: 15px 0 5px;">8. Governing Law & Jurisdiction</h4>
            <p>These Terms shall be governed and interpreted under the laws of the Republic of the Philippines and local ordinances of the Municipality of Palompon, Province of Leyte.</p>
        </div>
    </div>
</div>

<!-- ===== Footer CSS ===== -->

<!-- ===== Footer JS ===== -->
<script src="<?= base_url('assets/js/global-loader.js?v=20260920_2') ?>"></script>
<script src="<?= base_url('assets/js/autocomplete-search.js?v=20260920_2') ?>"></script>
<script src="<?= base_url('assets/js/auto-dismiss-alerts.js') ?>"></script>
<script src="<?= base_url('js/ws-client.js?v=20260920_2') ?>"></script>
<script src="<?= base_url('js/vehicle-type-live.js?v=20260905') ?>"></script>
<script>
let savedSupportScrollY = 0;

function openSupportModal(modalId){
    const modal = document.getElementById(modalId);
    if(modal){
        // Save current scroll position before opening
        savedSupportScrollY = window.scrollY || window.pageYOffset || document.documentElement.scrollTop || 0;
        modal.classList.add('active');
        document.documentElement.style.overflow = 'hidden';
    }
}

function closeSupportModal(modalId){
    const modal = document.getElementById(modalId);
    if(modal){
        modal.classList.remove('active');
        if(!document.querySelector('.support-modal-backdrop.active')){
            document.documentElement.style.overflow = '';
            document.body.style.overflow = '';
            // Ensure scroll position remains exactly where user was
            if (typeof savedSupportScrollY === 'number') {
                window.scrollTo({
                    top: savedSupportScrollY,
                    behavior: 'instant'
                });
            }
        }
    }
}

window.addEventListener('click', function(event){
    if(event.target && event.target.classList && event.target.classList.contains('support-modal-backdrop')){
        closeSupportModal(event.target.id);
    }
});

document.addEventListener('keydown', function(event){
    if(event.key === 'Escape' || event.key === 'Esc'){
        const activeModal = document.querySelector('.support-modal-backdrop.active');
        if(activeModal){
            closeSupportModal(activeModal.id);
        }
    }
});

function toggleFaq(btn){
    const answer = btn.nextElementSibling;
    const icon = btn.querySelector('i');

    document.querySelectorAll('.faq-answer').forEach(el=>{ if(el!==answer) el.classList.remove('open'); });
    document.querySelectorAll('.faq-question i').forEach(el=>{ if(el!==icon) el.classList.replace('fa-chevron-up','fa-chevron-down'); });

    answer.classList.toggle('open');
    if(answer.classList.contains('open')) icon.classList.replace('fa-chevron-down','fa-chevron-up');
    else icon.classList.replace('fa-chevron-up','fa-chevron-down');
}

function toggleHelpAccordion(headerEl) {
    const item = headerEl.closest('.accordion-item');
    if (!item) return;

    const accordionList = item.closest('.accordion-list');
    const isActive = item.classList.contains('active');

    if (accordionList) {
        accordionList.querySelectorAll('.accordion-item.active').forEach(openItem => {
            if (openItem !== item) openItem.classList.remove('active');
        });
    }

    item.classList.toggle('active', !isActive);
}

// Auto-open support modal if query parameter exists (e.g. redirected from /manual)
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('help')) {
        openSupportModal('helpModal');
    } else if (urlParams.has('faq')) {
        openSupportModal('faqModal');
    } else if (urlParams.has('terms')) {
        openSupportModal('termsModal');
    } else if (urlParams.has('report')) {
        openSupportModal('reportModal');
    } else if (urlParams.has('contact')) {
        openSupportModal('contactModal');
    }
});
</script>
