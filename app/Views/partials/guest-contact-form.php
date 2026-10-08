<?php
$isReport = $type === 'report';
$prefix = $isReport ? 'guestReport' : 'guestContact';
$oldValue = static fn(string $field): string => old('type') === $type && is_string(old($field, null, false)) ? old($field, null, false) : '';
$issueTypes = ['Incorrect schedule or departure time', 'Missing vehicle from queue', 'Website display / technical problem', 'Incorrect fare information / overcharging', 'Lost and found inquiry', 'Other operational issue'];
$recipient = app_contact_recipient();
$gmailUrl = old('type') === $type ? session()->getFlashdata('contact_gmail_url') : null;
?>
<form action="<?= base_url('contact/send') ?>" method="post" data-contact-gmail data-no-loader data-recipient="<?= esc($recipient ?? '', 'attr') ?>" data-acronym="<?= esc(app_acronym(), 'attr') ?>">
    <?= csrf_field() ?>
    <input type="hidden" name="type" value="<?= $type ?>">
    <div class="form-group">
        <label for="<?= $prefix ?>Name">Your Name *</label>
        <input id="<?= $prefix ?>Name" type="text" name="name" value="<?= esc($oldValue('name'), 'attr') ?>" maxlength="120" autocomplete="name" placeholder="e.g. Juan Dela Cruz" required>
    </div>
    <div class="form-group">
        <label for="<?= $prefix ?>Subject"><?= $isReport ? 'Issue Type' : 'Subject' ?></label>
        <?php if ($isReport): ?>
        <select id="<?= $prefix ?>Subject" name="subject">
            <?php foreach ($issueTypes as $issueType): ?><option <?= $oldValue('subject') === $issueType ? 'selected' : '' ?>><?= esc($issueType) ?></option><?php endforeach; ?>
        </select>
        <?php else: ?>
        <input id="<?= $prefix ?>Subject" type="text" name="subject" value="<?= esc($oldValue('subject'), 'attr') ?>" maxlength="180" placeholder="e.g. Inquiring about holiday trip schedules">
        <?php endif; ?>
    </div>
    <div class="form-group">
        <label for="<?= $prefix ?>Message"><?= $isReport ? 'Describe the Issue *' : 'Message *' ?></label>
        <textarea id="<?= $prefix ?>Message" name="message" maxlength="5000" placeholder="<?= $isReport ? 'Please include the plate number, destination, date/time and issue…' : 'Write your inquiry or feedback here…' ?>" required><?= esc($oldValue('message')) ?></textarea>
    </div>
    <p style="font-size:13px;color:#64748b;margin-bottom:16px;">Open a Gmail draft to send this <?= $isReport ? 'report' : 'message' ?> from your own account<?= $recipient ? ' to ' . esc($recipient) : '' ?>. Check the sender account in Gmail, then click <strong>Send</strong> to deliver it.</p>
    <button type="submit" class="submit-btn"><i class="fas fa-envelope-open-text"></i> Open Gmail draft</button>
    <div data-contact-draft-notice class="contact-verification-notice" role="status" tabindex="-1" <?= $gmailUrl ? '' : 'hidden' ?>>
        <p>Your message has not been sent yet. In Gmail, check the sender account and click <strong>Send</strong>.</p>
        <a data-contact-draft-link <?= $gmailUrl ? 'href="' . esc($gmailUrl, 'attr') . '"' : '' ?> target="_blank" rel="noopener noreferrer" data-no-loader>Open your Gmail draft</a>
        <p style="margin-top:8px;font-size:13px;">If Gmail did not open, use the link above. Your message stays in this form.</p>
    </div>
</form>
