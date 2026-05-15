<!-- ===== Footer ===== -->
<footer>
    <div class="footer-container">
        <div class="footer-content">
            <div class="f-about">
                <h3>PTM System</h3>
                <p>Palompon Transit Terminal Management System provides real-time tracking of vehicle queues and departure schedules to ensure efficient travel for every passenger.</p>
            </div>
            <div class="f-links">
                <h4>Quick Links</h4>
                <ul>
                    <li><a href="<?= base_url('schedules') ?>">Schedules</a></li>
                    <li><a href="<?= base_url('fares') ?>">Route Fares</a></li>
                    <li><a href="<?= base_url('login') ?>">Staff Login</a></li>
                    <li><a href="javascript:void(0)" onclick="openSupportModal('termsModal')">Terms of Service</a></li>
                </ul>
            </div>
            <div class="f-links">
                <h4>Support</h4>
                <ul>
                    <li><a href="javascript:void(0)" onclick="openSupportModal('helpModal')">Help Center</a></li>
                    <li><a href="javascript:void(0)" onclick="openSupportModal('reportModal')">Report Issue</a></li>
                    <li><a href="javascript:void(0)" onclick="openSupportModal('contactModal')">Contact Us</a></li>
                    <li><a href="javascript:void(0)" onclick="openSupportModal('faqModal')">FAQ</a></li>
                </ul>
            </div>
            <div class="f-links">
                <h4>Contact</h4>
                <ul>
                    <li style="font-size: 14px; opacity: 0.7;"><i class="fas fa-map-marker-alt" style="margin-right: 10px;"></i> Palompon Terminal Center</li>
                    <li style="font-size: 14px; opacity: 0.7;"><i class="fas fa-phone" style="margin-right: 10px;"></i> (053) 555-0123</li>
                    <li style="font-size: 14px; opacity: 0.7;"><i class="fas fa-envelope" style="margin-right: 10px;"></i> marquezgio38@gmail.com</li>
                </ul>
            </div>
        </div>
        <div class="f-copyright">
            &copy; 2026 Palompon Transit Terminal. All rights reserved. | Government of Palompon, Leyte
        </div>
    </div>
</footer>

<!-- ===== Modals ===== -->

<!-- 1. Help Center Modal -->
<div id="helpModal" class="support-modal-backdrop">
    <div class="support-modal">
        <button class="close-modal" onclick="closeSupportModal('helpModal')">&times;</button>
        <h3><i class="fas fa-life-ring" style="color:#3b82f6;"></i> Help Center</h3>
        <p style="font-size:14px;color:#64748b;margin-bottom:20px;">Browse common topics to get started quickly.</p>
        <div class="faq-item">
            <button class="faq-question" onclick="toggleFaq(this)">How do I track a vehicle? <i class="fas fa-chevron-down"></i></button>
            <div class="faq-answer">Use the search bar on the main page to search by plate number, destination, or operator name. Results will show the vehicle's current queue position and estimated departure time.</div>
        </div>
        <div class="faq-item">
            <button class="faq-question" onclick="toggleFaq(this)">How often is queue data updated? <i class="fas fa-chevron-down"></i></button>
            <div class="faq-answer">The Terminal Queue on the home page refreshes instantly via real-time synchronization. Schedules are updated as soon as staff record changes at the terminal.</div>
        </div>
        <div class="faq-item">
            <button class="faq-question" onclick="toggleFaq(this)">How do I view route fares? <i class="fas fa-chevron-down"></i></button>
            <div class="faq-answer">Click "Fares" in the navigation bar or the "Route Fares" link under Quick Links. Fares are organized by vehicle type: Van, Jeepney, and Minibus.</div>
        </div>
        <div class="faq-item">
            <button class="faq-question" onclick="toggleFaq(this)">Is there a mobile app available? <i class="fas fa-chevron-down"></i></button>
            <div class="faq-answer">Currently, this is a web-based system only. The site is fully responsive and works on any mobile or tablet browser — simply visit the URL on your phone!</div>
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
                    <option>Incorrect fare information</option>
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
        <p style="font-size:14px;color:#64748b;margin-bottom:20px;">Send a message and we'll get back to you as soon as possible.</p>
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
            <div class="faq-answer">The Palompon Terminal typically operates from early morning until late evening. Individual vehicle schedules depend on operators and may vary. Check the Schedules page for up-to-date times.</div>
        </div>
        <div class="faq-item">
            <button class="faq-question" onclick="toggleFaq(this)">Can I buy tickets through this website? <i class="fas fa-chevron-down"></i></button>
            <div class="faq-answer">No. This system is for real-time monitoring only. Tickets and fares are handled directly at the terminal or with the vehicle operator.</div>
        </div>
        <div class="faq-item">
            <button class="faq-question" onclick="toggleFaq(this)">What vehicle types operate from this terminal? <i class="fas fa-chevron-down"></i></button>
            <div class="faq-answer">The terminal serves three vehicle types: <strong>Vans</strong> (express routes), <strong>Jeepneys</strong> (regular routes), and <strong>Minibuses</strong>. You can filter schedules and fares by vehicle type.</div>
        </div>
        <div class="faq-item">
            <button class="faq-question" onclick="toggleFaq(this)">How accurate are the estimated departure times? <i class="fas fa-chevron-down"></i></button>
            <div class="faq-answer">Departure times are set by terminal staff when a vehicle checks in. They are estimates and may shift slightly depending on passenger loading. A vehicle may depart early if it fills up.</div>
        </div>
        <div class="faq-item">
            <button class="faq-question" onclick="toggleFaq(this)">Who do I contact for complaints or feedback? <i class="fas fa-chevron-down"></i></button>
            <div class="faq-answer">Use the "Contact Us" or "Report Issue" widgets here on this page. Your message goes directly to our support team at marquezgio38@gmail.com.</div>
        </div>
    </div>
</div>

<!-- 5. Terms of Service Modal -->
<div id="termsModal" class="support-modal-backdrop">
    <div class="support-modal">
        <button class="close-modal" onclick="closeSupportModal('termsModal')">&times;</button>
        <h3><i class="fas fa-file-contract" style="color:#6366f1;"></i> Terms of Service</h3>
        <div style="font-size: 14px; color: #4b5563; line-height: 1.6; max-height: 60vh; overflow-y: auto; padding-right: 10px;">
            <p><strong>Last updated: February 2026</strong></p>
            <h4 style="color: #1f2937; margin: 15px 0 5px;">1. Acceptance of Terms</h4>
            <p>By accessing and using the Palompon Transit Terminal Monitoring System ("PTM System"), you agree to be bound by these Terms of Service. If you do not agree, please do not use this service.</p>

            <h4 style="color: #1f2937; margin: 15px 0 5px;">2. Description of Service</h4>
            <p>The PTM System provides real-time information regarding vehicle queues, departure schedules, and route fares within the Palompon Terminal. This is a public information service operated by the Government of Palompon, Leyte.</p>

            <h4 style="color: #1f2937; margin: 15px 0 5px;">3. Use of Information</h4>
            <ul>
                <li>All information provided is for general informational purposes only.</li>
                <li>Departure times and schedules are estimates and may vary due to actual operational conditions.</li>
                <li>The system does not support online booking, ticketing, or fare collection.</li>
            </ul>

            <h4 style="color: #1f2937; margin: 15px 0 5px;">4. Disclaimer of Warranties</h4>
            <p>The PTM System is provided "as is" without warranties of any kind, express or implied. We do not guarantee the accuracy, completeness, or timeliness of information displayed.</p>

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
    background: #1a202c;
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

.f-about h3 { color: var(--accent); margin-bottom: 20px; }
.f-about p { font-size: 14px; opacity: 0.7; }

.f-links h4 { margin-bottom: 20px; font-size: 16px; position: relative; }
.f-links h4::after {
    content: '';
    width: 30px;
    height: 2px;
    background: var(--accent);
    position: absolute;
    bottom: -8px;
    left: 0;
}

.f-links ul { list-style: none; padding: 0; margin: 0; }
.f-links li { margin-bottom: 12px; }
.f-links a {
    color: white; text-decoration: none; font-size: 14px; opacity: 0.7;
    transition: var(--transition);
}
.f-links a:hover { opacity: 1; color: var(--accent); padding-left: 5px; }

/* Center copyright */
.f-copyright {
    font-size: 13px; opacity: 0.5; text-align: center;
    margin-top: 30px; display: flex; justify-content: center; flex-wrap: wrap;
}

/* Responsive Footer */
@media (max-width: 768px) {
    .footer-content { grid-template-columns: 1fr; gap: 30px; }
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
}

#termsModal .support-modal {
    max-width: 850px;
}

.support-modal h3 { font-size: 20px; font-weight: 800; color: #1e293b; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
.support-modal .close-modal {
    position: absolute; top: 18px; right: 20px; font-size: 22px;
    cursor: pointer; color: #94a3b8; background: none; border: none; line-height: 1;
    transition: color .2s;
}
.support-modal .close-modal:hover { color: #e53e3e; }

.support-modal .form-group  { margin-bottom: 16px; }
.support-modal label { display: block; font-size: 13px; font-weight: 700; color: #374151; margin-bottom: 6px; }
.support-modal input, .support-modal textarea, .support-modal select {
    width: 100%; padding: 12px 14px; border: 1px solid #e2e8f0;
    border-radius: 12px; font-size: 14px; font-family: inherit;
    outline: none; transition: border-color .2s;
}
.support-modal input:focus, .support-modal textarea:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(21,101,192,.1); }
.support-modal textarea { resize: vertical; min-height: 110px; }
.support-modal .submit-btn {
    width: 100%; padding: 14px; background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    color: white; border: none; border-radius: 12px; font-size: 15px; font-weight: 700; cursor: pointer;
    transition: opacity .2s;
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

@media (max-width: 530px) { .support-modal { padding: 25px 18px; border-radius: 18px; } }
</style>

<!-- ===== Footer JS ===== -->
<script>
function openSupportModal(modalId){
    const modal = document.getElementById(modalId);
    if(modal){ modal.classList.add('active'); document.body.style.overflow='hidden'; }
}
function closeSupportModal(modalId){
    const modal = document.getElementById(modalId);
    if(modal){ modal.classList.remove('active');
        if(!document.querySelector('.support-modal-backdrop.active')){ document.body.style.overflow=''; }
    }
}
window.onclick = function(event){
    if(event.target.classList.contains('support-modal-backdrop')){
        closeSupportModal(event.target.id);
    }
}

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