<?php
$pending = session()->get('guest_contact_pending');
$pending = is_array($pending) && ($pending['expires_at'] ?? 0) > time() ? $pending : null;
?>
<div id="guestContactVerificationModal" class="support-modal-backdrop" role="dialog" aria-modal="true" aria-labelledby="contactVerificationTitle" aria-hidden="true" data-pending-type="<?= esc($pending['type'] ?? '', 'attr') ?>">
    <div class="support-modal guest-contact-verification">
        <button type="button" class="close-modal" onclick="closeSupportModal('guestContactVerificationModal')" aria-label="Close email verification">&times;</button>
        <div id="guestContactVerificationContent">
            <?php if ($pending): ?>
            <?= view('partials/guest-contact-verification', ['pending'=>$pending, 'resendWait'=>max(0, $pending['sent_at'] + 60 - time()),
                'notice'=>session()->getFlashdata('contact_verify_notice'), 'error'=>session()->getFlashdata('contact_verify_error')]) ?>
            <?php else: ?>
            <h3 id="contactVerificationTitle">Email verification</h3>
            <p>Enter your message in Contact Us or Report Issue to request a verification code.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
