<h3 id="contactVerificationTitle"><i class="fas fa-envelope-circle-check" aria-hidden="true"></i> Verify your email</h3>
<?php if ($pending['verified']): ?>
<p class="contact-verification-intro">Your email is verified. Retry sending your saved <?= $pending['type'] === 'report' ? 'report' : 'message' ?> below.</p>
<?php else: ?>
<p class="contact-verification-intro">Enter the six-digit code sent to <strong class="contact-verification-email"><?= esc($pending['email']) ?></strong>. Your <?= $pending['type'] === 'report' ? 'report' : 'message' ?> will be sent after verification.</p>
<?php endif; ?>
<?php if (!empty($notice)): ?>
<div class="contact-verification-notice" role="status"><?= esc($notice) ?></div>
<?php endif; ?>
<?php if (!empty($error)): ?>
<div class="contact-verification-notice is-error" role="alert"><?= esc($error) ?></div>
<?php endif; ?>
<form method="post" action="<?= base_url('contact/verify') ?>" id="guestContactVerifyForm" data-contact-async data-no-loader>
    <?= csrf_field() ?>
    <input type="hidden" name="draft_id" value="<?= esc($pending['id'], 'attr') ?>">
    <?php if (!$pending['verified']): ?>
    <div class="form-group">
        <label for="guestContactCode">Email verification code</label>
        <input id="guestContactCode" class="contact-verification-code" name="code" type="text" inputmode="numeric" autocomplete="one-time-code" pattern="[0-9]{6}" minlength="6" maxlength="6" placeholder="000000" aria-describedby="guestContactCodeHelp" required>
        <p id="guestContactCodeHelp" class="contact-verification-help">Valid for 10 minutes. Check your spam folder if it hasn’t arrived.</p>
    </div>
    <?php endif; ?>
    <button type="submit" class="submit-btn"><i class="fas fa-paper-plane" aria-hidden="true"></i> <?= $pending['verified'] ? 'Send message' : 'Verify and send' ?></button>
</form>
<div class="contact-verification-actions">
    <?php if (!$pending['verified']): ?>
    <form method="post" action="<?= base_url('contact/resend') ?>" data-contact-async data-no-loader>
        <?= csrf_field() ?><input type="hidden" name="draft_id" value="<?= esc($pending['id'], 'attr') ?>">
        <button type="submit" class="contact-verification-secondary" id="guestContactResend" data-resend-wait="<?= (int) $resendWait ?>">Resend code</button>
    </form>
    <?php endif; ?>
    <form method="post" action="<?= base_url('contact/edit') ?>" data-contact-async data-no-loader>
        <?= csrf_field() ?><input type="hidden" name="draft_id" value="<?= esc($pending['id'], 'attr') ?>">
        <button type="submit" class="contact-verification-secondary">Edit email or message</button>
    </form>
</div>
<details class="contact-verification-preview">
    <summary>Review your <?= $pending['type'] === 'report' ? 'report' : 'message' ?></summary>
    <p><strong>Name:</strong> <?= esc($pending['name']) ?></p>
    <p><strong><?= $pending['type'] === 'report' ? 'Issue' : 'Subject' ?>:</strong> <?= esc($pending['subject'] ?: 'General inquiry') ?></p>
    <div class="contact-verification-message"><?= esc($pending['message']) ?></div>
</details>
