<?= view('templates/header', ['title' => $title, 'pageStyles' => ['assets/css/contact-verification.css']]) ?>
<section class="guest-contact-verification modern-card shadow-modern" aria-labelledby="contactVerificationTitle">
    <div class="modern-card-body">
        <div class="contact-verification-icon" aria-hidden="true"><i class="bi bi-envelope-check"></i></div>
        <h1 id="contactVerificationTitle" class="page-title-modern">Verify your email</h1>
        <?php if ($pending['verified']): ?>
        <p class="text-muted">Your email is verified. Retry sending your saved <?= $pending['type'] === 'report' ? 'report' : 'message' ?> below.</p>
        <?php else: ?>
        <p class="text-muted">Enter the six-digit code sent to <strong class="contact-verification-email"><?= esc($pending['email']) ?></strong>. Your <?= $pending['type'] === 'report' ? 'report' : 'message' ?> will reach terminal management after verification.</p>
        <?php endif; ?>
        <?php if ($notice = session()->getFlashdata('contact_verify_notice')): ?>
        <div class="contact-verification-notice" role="status"><?= esc($notice) ?></div>
        <?php endif; ?>
        <?php if ($error = session()->getFlashdata('contact_verify_error')): ?>
        <div class="contact-verification-notice is-error" role="alert"><?= esc($error) ?></div>
        <?php endif; ?>
        <form method="post" action="<?= base_url('contact/verify') ?>" id="guestContactVerifyForm">
            <?= csrf_field() ?>
            <input type="hidden" name="draft_id" value="<?= esc($pending['id'], 'attr') ?>">
            <?php if (!$pending['verified']): ?>
            <label for="guestContactCode" class="form-label-modern">Email verification code</label>
            <input id="guestContactCode" class="form-control contact-verification-code" name="code" type="text" inputmode="numeric" autocomplete="one-time-code" pattern="[0-9]{6}" minlength="6" maxlength="6" placeholder="000000" aria-describedby="guestContactCodeHelp" required autofocus>
            <p id="guestContactCodeHelp" class="small text-muted mt-2">The code expires in 10 minutes. Check your spam folder if it hasn’t arrived.</p>
            <?php endif; ?>
            <button type="submit" class="btn-modern btn-modern-primary contact-verification-submit"><i class="bi bi-send"></i> <?= $pending['verified'] ? 'Send message' : 'Verify and send' ?></button>
        </form>
        <div class="contact-verification-actions">
            <?php if (!$pending['verified']): ?>
            <form method="post" action="<?= base_url('contact/resend') ?>">
                <?= csrf_field() ?><input type="hidden" name="draft_id" value="<?= esc($pending['id'], 'attr') ?>">
                <button type="submit" class="btn-modern btn-modern-outline" id="guestContactResend" data-resend-wait="<?= (int) $resendWait ?>">Resend code</button>
            </form>
            <?php endif; ?>
            <form method="post" action="<?= base_url('contact/edit') ?>">
                <?= csrf_field() ?><input type="hidden" name="draft_id" value="<?= esc($pending['id'], 'attr') ?>">
                <button type="submit" class="btn-modern btn-modern-outline">Edit email or message</button>
            </form>
        </div>
        <details class="contact-verification-preview">
            <summary>Review your <?= $pending['type'] === 'report' ? 'report' : 'message' ?></summary>
            <p class="mt-3 mb-1"><strong>Name:</strong> <?= esc($pending['name']) ?></p>
            <p><strong><?= $pending['type'] === 'report' ? 'Issue' : 'Subject' ?>:</strong> <?= esc($pending['subject'] ?: 'General inquiry') ?></p>
            <div class="contact-verification-message"><?= esc($pending['message']) ?></div>
        </details>
    </div>
</section>
<script src="<?= app_asset_url('assets/js/contact-verification.js') ?>" defer></script>
<?= view('templates/footer') ?>
