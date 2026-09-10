<!-- ===== Footer ===== -->
<footer>
    <div class="footer-container">
        <div class="footer-content">
            <div class="f-about">
                <h3>PTTM System</h3>
                <p>Palompon Transit Terminal Management System provides real-time tracking of vehicle queues and departure schedules to ensure efficient travel for every passenger.</p>
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
                    <li><a href="<?= base_url('manual') ?>"><i class="fas fa-book-open"></i> User Guide & Error Help</a></li>
                    <li><a href="javascript:void(0)" onclick="event.preventDefault(); openSupportModal('helpModal')">Help Center</a></li>
                    <li><a href="javascript:void(0)" onclick="event.preventDefault(); openSupportModal('reportModal')">Report Issue</a></li>
                    <li><a href="javascript:void(0)" onclick="event.preventDefault(); openSupportModal('contactModal')">Contact Us</a></li>
                    <li><a href="javascript:void(0)" onclick="event.preventDefault(); openSupportModal('faqModal')">FAQ</a></li>
                    <li><a href="javascript:void(0)" onclick="event.preventDefault(); openSupportModal('termsModal')">Terms of Service</a></li>
                </ul>
            </div>
            <div class="f-links">
                <h4>Contact</h4>
                <ul>
                    <li style="font-size: 14px; color: #cbd5e1;"><i class="fas fa-map-marker-alt" style="margin-right: 10px; color: #f97316;"></i> Palompon Transit Terminal, Rizal St., Palompon, Leyte 6538</li>
                    <li style="font-size: 14px; color: #cbd5e1;">
                        <a href="tel:0535558376" style="color: inherit; text-decoration: none; display: inline-flex; align-items: center; transition: color 0.2s ease;" onmouseover="this.style.color='#f97316'" onmouseout="this.style.color='inherit'">
                            <i class="fas fa-phone" style="margin-right: 10px; color: #f97316;"></i> (053) 555-8376 / 338-2022
                        </a>
                    </li>
                    <li style="font-size: 14px; color: #cbd5e1;">
                        <a href="mailto:<?= esc(config('Email')->recipients ?: 'arclast988@gmail.com') ?>" style="color: inherit; text-decoration: none; display: inline-flex; align-items: center; transition: color 0.2s ease;" onmouseover="this.style.color='#f97316'" onmouseout="this.style.color='inherit'">
                            <i class="fas fa-envelope" style="margin-right: 10px; color: #f97316;"></i> <?= esc(config('Email')->recipients ?: 'arclast988@gmail.com') ?>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
        <div class="f-copyright">
            &copy; <?= date('Y') ?> Palompon Transit Terminal Management System (PTTM). All rights reserved. | Municipality of Palompon, Leyte
        </div>
    </div>
</footer>

<!-- ===== Modals ===== -->

<!-- 1. Help Center Modal -->
<div id="helpModal" class="support-modal-backdrop">
    <div class="support-modal">
        <button class="close-modal" onclick="closeSupportModal('helpModal')">&times;</button>
        <h3><i class="fas fa-life-ring" style="color:#3b82f6;"></i> Help Center</h3>
        <p style="font-size:14px;color:#64748b;margin-bottom:16px;">Browse common topics or view the full commuter user manual.</p>
        
        <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 12px; padding: 12px 16px; margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between; gap: 10px;">
            <div style="font-size: 13px; color: #1e40af; font-weight: 600;">
                <i class="fas fa-book-open" style="margin-right: 6px;"></i> Complete Commuter & Passenger Guide
            </div>
            <a href="<?= base_url('manual') ?>" style="background: #2563eb; color: white; padding: 6px 12px; border-radius: 8px; font-size: 12.5px; font-weight: 700; text-decoration: none; white-space: nowrap;">
                Open Guide →
            </a>
        </div>

        <div class="faq-item">
            <button class="faq-question" onclick="toggleFaq(this)">How do I track a vehicle? <i class="fas fa-chevron-down"></i></button>
            <div class="faq-answer">Use the search bar on the main page or visit the Trip Search page to search by plate number, destination, or vehicle type. Results show the vehicle's current queue position, remaining seats, and estimated departure time.</div>
        </div>
        <div class="faq-item">
            <button class="faq-question" onclick="toggleFaq(this)">How often is queue data updated? <i class="fas fa-chevron-down"></i></button>
            <div class="faq-answer">The Terminal Queue on the home page refreshes instantly via real-time WebSocket synchronization. If connection drops, it automatically falls back to background HTTP updates every 20 seconds.</div>
        </div>
        <div class="faq-item">
            <button class="faq-question" onclick="toggleFaq(this)">How do I view route fares & discounts? <i class="fas fa-chevron-down"></i></button>
            <div class="faq-answer">Click "Fares" in the navigation bar. Official LTFRB fare tables and 20% statutory discounts for Students, Senior Citizens, and PWDs are calculated automatically.</div>
        </div>
        <div class="faq-item">
            <button class="faq-question" onclick="toggleFaq(this)">Is there a mobile app available? <i class="fas fa-chevron-down"></i></button>
            <div class="faq-answer">No app store download is necessary! This website is fully mobile-responsive. Simply visit the URL in your mobile browser or tap "Add to Home Screen" for quick access.</div>
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
                    <option>Other issue</option>
                </select>
            </div>
            <div class="form-group">
                <label>Describe the Issue *</label>
                <textarea name="message" placeholder="Please describe what went wrong and when it happened..." required></textarea>
            </div>
            <button type="submit" class="submit-btn"><i class="fas fa-paper-plane"></i> Submit Report</button>
        </form>
    </div>
</div>

<!-- 3. Contact Us Modal -->
<div id="contactModal" class="support-modal-backdrop">
    <div class="support-modal">
        <button class="close-modal" onclick="closeSupportModal('contactModal')">&times;</button>
        <h3><i class="fas fa-envelope-open-text" style="color:#22c55e;"></i> Contact Us</h3>
        <?php if (session()->getFlashdata('contact_success')): ?>
        <div class="alert-success-banner"><i class="fas fa-check-circle"></i> <?= session()->getFlashdata('contact_success') ?></div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('contact_error')): ?>
        <div class="alert-error-banner"><i class="fas fa-times-circle"></i> <?= session()->getFlashdata('contact_error') ?></div>
        <?php endif; ?>
        <p style="font-size:14px;color:#64748b;margin-bottom:20px;">Send a message and terminal administration will get back to you as soon as possible.</p>
        <form action="<?= base_url('contact/send') ?>" method="post">
            <?= csrf_field() ?>
            <input type="hidden" name="type" value="contact">
            <div class="form-group">
                <label>Your Name *</label>
                <input type="text" name="name" placeholder="e.g. Juan Dela Cruz" required>
            </div>
            <div class="form-group">
                <label>Your Email *</label>
                <input type="email" name="email" placeholder="your@email.com" required>
            </div>
            <div class="form-group">
                <label>Subject</label>
                <input type="text" name="subject" placeholder="e.g. Question about schedules">
            </div>
            <div class="form-group">
                <label>Message *</label>
                <textarea name="message" placeholder="Write your message here..." required></textarea>
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
            <div class="faq-answer">The Palompon Terminal operates daily from <strong>4:00 AM to 8:00 PM</strong>. Individual vehicle departure times depend on route demand and scheduled headway intervals. Check the Live Monitor or Schedules page for up-to-date departures.</div>
        </div>
        <div class="faq-item">
            <button class="faq-question" onclick="toggleFaq(this)">Can I buy tickets through this website? <i class="fas fa-chevron-down"></i></button>
            <div class="faq-answer">No. This system provides real-time public monitoring only. Cash fares and tickets are handled directly at the terminal bays or with the vehicle conductor.</div>
        </div>
        <div class="faq-item">
            <button class="faq-question" onclick="toggleFaq(this)">What vehicle types operate from this terminal? <i class="fas fa-chevron-down"></i></button>
            <div class="faq-answer">PUJs (Jeepneys), UV Express Vans, and Modern Minibuses operate across certified routes including Ormoc, Tacloban, Isabel, Naval, and Kananga. You can filter the queue and fares by vehicle type.</div>
        </div>
        <div class="faq-item">
            <button class="faq-question" onclick="toggleFaq(this)">How accurate are the estimated departure times? <i class="fas fa-chevron-down"></i></button>
            <div class="faq-answer">Estimated Departure Times (ETD) are calculated automatically based on official headway rules set by terminal administration. A vehicle may depart early once all passenger seats are filled.</div>
        </div>
        <div class="faq-item">
            <button class="faq-question" onclick="toggleFaq(this)">Who do I contact for complaints or feedback? <i class="fas fa-chevron-down"></i></button>
            <div class="faq-answer">Use the "Contact Us" or "Report Issue" forms on this page. Your message goes directly to our support team at <a href="mailto:<?= esc(config('Email')->recipients ?: 'arclast988@gmail.com') ?>" style="color:#2563eb; font-weight:600;"><?= esc(config('Email')->recipients ?: 'arclast988@gmail.com') ?></a>.</div>
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
            <p>By accessing and using the Palompon Transit Terminal Management System ("PTTM System"), you agree to be bound by these Terms of Service. If you do not agree, please do not use this service.</p>

            <h4 style="color: #1f2937; margin: 15px 0 5px;">2. Description of Service</h4>
            <p>The PTTM System provides real-time information regarding vehicle queues, departure schedules, and route fares within the Palompon Terminal. This is a public information service operated by the Municipality of Palompon, Leyte.</p>

            <h4 style="color: #1f2937; margin: 15px 0 5px;">3. Use of Information</h4>
            <ul>
                <li>All information provided is for general informational purposes only.</li>
                <li>Departure times and schedules are estimates and may vary due to actual operational conditions.</li>
                <li>The system does not support online booking, ticketing, or fare collection.</li>
            </ul>

            <h4 style="color: #1f2937; margin: 15px 0 5px;">4. Disclaimer of Warranties</h4>
            <p>The PTTM System is provided "as is" without warranties of any kind, express or implied. We do not guarantee the accuracy, completeness, or timeliness of information displayed.</p>

            <h4 style="color: #1f2937; margin: 15px 0 5px;">5. Limitation of Liability</h4>
            <p>Palompon Terminal shall not be liable for any loss or damages arising from reliance on the information provided through this system, including missed departures or scheduling inaccuracies.</p>

            <h4 style="color: #1f2937; margin: 15px 0 5px;">6. Privacy</h4>
            <p>This system does not collect personal data from guest users. Concerns submitted through the Support section are sent directly to our email for response purposes only and are not stored in any database.</p>
        </div>
    </div>
</div>

<!-- ===== Footer CSS ===== -->
<style>

/* Footer styling */
footer {
    background: rgba(26, 32, 44, 0.82) !important;
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    color: white;
    padding: 60px 5% 30px;
    margin-top: 60px;
}

.footer-container {
    max-width: 1200px;
    margin: 0 auto;
}

.footer-content {
    display: grid;
    grid-template-columns: 2fr repeat(3, 1fr);
    gap: 40px;
    margin-bottom: 40px;
}

.f-about h3 { color: #f97316; font-size: 18px; font-weight: 800; margin-bottom: 20px; }
.f-about p { font-size: 14px; color: #e2e8f0; line-height: 1.6; }

.f-links h4 { margin-bottom: 20px; font-size: 16px; font-weight: 700; color: #ffffff; position: relative; }
.f-links h4::after {
    content: '';
    width: 30px;
    height: 2px;
    background: #f97316;
    position: absolute;
    bottom: -8px;
    left: 0;
}

.f-links ul { list-style: none; padding: 0; margin: 0; }
.f-links li { margin-bottom: 12px; color: #cbd5e1; }
.f-links a {
    color: #e2e8f0; text-decoration: none; font-size: 14px; font-weight: 500;
    transition: var(--transition);
}
.f-links a:hover { color: #f97316; padding-left: 5px; font-weight: 600; }

/* Center copyright */
.f-copyright {
    font-size: 13px; color: #cbd5e1; text-align: center; font-weight: 500;
    margin-top: 30px; display: flex; justify-content: center; flex-wrap: wrap;
}

/* Responsive Footer */
@media (max-width: 768px) {
    .footer-content { grid-template-columns: 1fr 1fr 1fr; gap: 24px; }
    .f-about { grid-column: 1 / -1; }
}
@media (max-width: 480px) {
    .footer-content { grid-template-columns: 1fr 1fr; }
    .f-links:nth-child(4) { grid-column: 1 / -1; }
}

/* --- Modals --- */
.support-modal-backdrop {
    display: none;
    position: fixed; inset: 0;
    background: rgba(0,0,0,.6);
    z-index: 9999;
    align-items: center;
    justify-content: center;
    padding: 16px;
    overscroll-behavior: contain;
}
.support-modal-backdrop.active { display: flex; }

.support-modal {
    background: white;
    border-radius: 24px;
    width: 95%;
    max-width: 550px;
    max-height: 90vh;
    overflow-y: auto;
    padding: 35px 30px;
    position: relative;
    box-shadow: 0 20px 60px rgba(0,0,0,.3);
    overscroll-behavior: contain;
}

#termsModal .support-modal {
    max-width: 850px;
}

.support-modal h3 { font-size: 20px; font-weight: 800; color: #1e293b; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; padding-right: 32px; word-break: break-word; }
.support-modal .close-modal {
    position: absolute; top: 18px; right: 20px; font-size: 22px;
    cursor: pointer; color: #94a3b8; background: none; border: none; line-height: 1;
    transition: color .2s;
    width: 32px; height: 32px; display: grid; place-items: center;
}
.support-modal .close-modal:hover { color: #e53e3e; }

.support-modal .form-group  { margin-bottom: 16px; }
.support-modal label { display: block; font-size: 13px; font-weight: 700; color: #374151; margin-bottom: 6px; }
.support-modal input, .support-modal textarea, .support-modal select {
    width: 100%; padding: 12px 14px; border: 1px solid #e2e8f0;
    border-radius: 12px; font-size: 14px; font-family: inherit;
    outline: none; transition: border-color .2s;
    box-sizing: border-box;
}
.support-modal input:focus, .support-modal textarea:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(21,101,192,.1); }
.support-modal textarea { resize: vertical; min-height: 110px; }
.support-modal .submit-btn {
    width: 100%; padding: 14px; background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    color: white; border: none; border-radius: 12px; font-size: 15px; font-weight: 700; cursor: pointer;
    transition: opacity .2s;
    box-sizing: border-box;
}
.support-modal .submit-btn:hover { opacity: .88; }

/* FAQ accordion */
.faq-item { border: 1px solid #e2e8f0; border-radius: 12px; margin-bottom: 10px; overflow: hidden; }
.faq-question {
    width: 100%; text-align: left; background: #f8fafc;
    border: none; padding: 15px 18px;
    font-size: 14px; font-weight: 700; cursor: pointer;
    display: flex; justify-content: space-between; align-items: center;
    transition: background .2s; font-family: inherit;
}
.faq-question:hover { background: #e3f2fd; }
.faq-answer { display: none; padding: 14px 18px; font-size: 14px; color: #4b5563; line-height: 1.7; background: white; }
.faq-answer.open { display: block; }

.alert-success-banner { background: #d1fae5; color: #065f46; border-radius: 10px; padding: 12px 16px; margin-bottom: 18px; font-size: 14px; font-weight: 600; display: flex; align-items: center; gap: 8px; }
.alert-error-banner   { background: #fee2e2; color: #991b1b; border-radius: 10px; padding: 12px 16px; margin-bottom: 18px; font-size: 14px; font-weight: 600; display: flex; align-items: center; gap: 8px; }

@media (max-width: 530px) {
    .support-modal {
        padding: 22px 16px;
        border-radius: 18px;
        width: 100%;
        max-width: calc(100vw - 24px);
    }
    .support-modal h3 {
        font-size: 17px;
        margin-bottom: 16px;
    }
    .support-modal .close-modal {
        top: 14px;
        right: 14px;
    }
}
@media (max-width: 420px) {
    .support-modal {
        padding: 16px 10px;
        border-radius: 12px;
        max-width: calc(100vw - 12px);
    }
    .support-modal h3 {
        font-size: 15px;
    }
    .support-modal input, .support-modal textarea, .support-modal select {
        padding: 8px 10px;
        font-size: 13px;
    }
    .support-modal .submit-btn {
        padding: 11px;
        font-size: 13.5px;
    }
}

@media print {
    footer,
    .footer-container,
    .support-modal-backdrop {
        display: none !important;
    }
}
</style>

<!-- ===== Footer JS ===== -->
<script src="<?= base_url('assets/js/global-loader.js?v=20260910') ?>"></script>
<script src="<?= base_url('assets/js/autocomplete-search.js?v=20260906') ?>"></script>
<script src="<?= base_url('assets/js/auto-dismiss-alerts.js') ?>"></script>
<script src="<?= base_url('js/ws-client.js?v=20260905') ?>"></script>
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
</script>
