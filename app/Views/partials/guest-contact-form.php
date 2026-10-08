<?php
$isReport = $type === 'report';
$prefix = $isReport ? 'guestReport' : 'guestContact';
$oldValue = static fn(string $field): string => old('type') === $type && is_string(old($field, null, false)) ? old($field, null, false) : '';
$issueTypes = [
    'Incorrect schedule or departure time', 'Missing vehicle from queue',
    'Delayed or cancelled trip', 'Vehicle or route information is incorrect',
    'Queue order or boarding problem', 'Unsafe driving or vehicle condition',
    'Driver or staff conduct', 'Incorrect fare information / overcharging',
    'Website display / technical problem', 'Accessibility or passenger assistance problem',
    'Lost item or belongings', 'Other operational issue',
];
$contactSubjects = [
    'Schedule or departure inquiry', 'Route or destination inquiry',
    'Fare or discount inquiry', 'Terminal hours and facilities',
    'Passenger assistance or accessibility', 'Lost and found inquiry',
    'Travel feedback or suggestion', 'Driver or operator coordination',
    'Partnership or community inquiry', 'Other inquiry',
];
$subjects = $isReport ? $issueTypes : $contactSubjects;
$previousSubject = $oldValue('subject');
$recipient = app_contact_recipient();
$gmailUrl = old('type') === $type ? session()->getFlashdata('contact_gmail_url') : null;
?>
<form action="<?= base_url('contact/send') ?>" method="post" data-contact-gmail data-no-loader data-recipient="<?= esc($recipient ?? '', 'attr') ?>" data-acronym="<?= esc(app_acronym(), 'attr') ?>">
    <?= csrf_field() ?>
    <input type="hidden" name="type" value="<?= $type ?>">
    <div class="form-group">
        <label for="<?= $prefix ?>Name">Your Name (optional)</label>
        <input id="<?= $prefix ?>Name" type="text" name="name" value="<?= esc($oldValue('name'), 'attr') ?>" maxlength="120" autocomplete="name" placeholder="e.g. Juan Dela Cruz">
    </div>
    <div class="form-group">
        <label for="<?= $prefix ?>Subject"><?= $isReport ? 'Issue Type' : 'Subject' ?></label>
        <select id="<?= $prefix ?>Subject" name="subject" aria-label="<?= $isReport ? 'Issue Type' : 'Subject' ?>" data-autocomplete-placeholder="<?= $isReport ? 'Search issue type…' : 'Search inquiry or feedback topic…' ?>">
            <?php if ($previousSubject !== '' && !in_array($previousSubject, $subjects, true)): ?>
            <option selected><?= esc($previousSubject) ?></option>
            <?php endif; ?>
            <?php foreach ($subjects as $subject): ?><option <?= $previousSubject === $subject ? 'selected' : '' ?>><?= esc($subject) ?></option><?php endforeach; ?>
        </select>
    </div>
    <div class="form-group">
        <label for="<?= $prefix ?>Message"><?= $isReport ? 'Describe the Issue (optional)' : 'Message (optional)' ?></label>
        <textarea id="<?= $prefix ?>Message" name="message" maxlength="5000" placeholder="<?= $isReport ? 'Add details if available: plate number, destination, date/time and issue…' : 'Add any questions or feedback you would like to share…' ?>"><?= esc($oldValue('message')) ?></textarea>
    </div>
    <p style="font-size:13px;color:#64748b;margin-bottom:16px;">Open a Gmail draft to send this <?= $isReport ? 'report' : 'message' ?> from your own account<?= $recipient ? ' to ' . esc($recipient) : '' ?>. Check the sender account in Gmail, then click <strong>Send</strong> to deliver it.</p>
    <button type="submit" class="submit-btn"><i class="fas fa-envelope-open-text"></i> Open Gmail draft</button>
    <div data-contact-draft-notice class="contact-verification-notice" role="status" tabindex="-1" <?= $gmailUrl ? '' : 'hidden' ?>>
        <p>Your message has not been sent yet. In Gmail, check the sender account and click <strong>Send</strong>.</p>
        <a data-contact-draft-link <?= $gmailUrl ? 'href="' . esc($gmailUrl, 'attr') . '"' : '' ?> target="_blank" rel="noopener noreferrer" data-no-loader>Open your Gmail draft</a>
        <p style="margin-top:8px;font-size:13px;">If Gmail did not open, use the link above. Your message stays in this form.</p>
    </div>
</form>
