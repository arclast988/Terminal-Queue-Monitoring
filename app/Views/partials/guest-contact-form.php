<?php
$isReport = $type === 'report';
$prefix = $isReport ? 'guestReport' : 'guestContact';
$oldValue = static fn(string $field): string => old('type') === $type && is_string(old($field, null, false)) ? old($field, null, false) : '';
$issueTypes = ['Incorrect schedule or departure time', 'Missing vehicle from queue', 'Website display / technical problem', 'Incorrect fare information / overcharging', 'Lost and found inquiry', 'Other operational issue'];
?>
<form action="<?= base_url('contact/send') ?>" method="post">
    <?= csrf_field() ?>
    <input type="hidden" name="type" value="<?= $type ?>">
    <div class="form-group">
        <label for="<?= $prefix ?>Name">Your Name *</label>
        <input id="<?= $prefix ?>Name" type="text" name="name" value="<?= esc($oldValue('name'), 'attr') ?>" maxlength="120" autocomplete="name" placeholder="e.g. Juan Dela Cruz" required>
    </div>
    <div class="form-group">
        <label for="<?= $prefix ?>Email">Your Email *</label>
        <input id="<?= $prefix ?>Email" type="email" name="email" value="<?= esc($oldValue('email'), 'attr') ?>" maxlength="254" autocomplete="email" placeholder="your@email.com" required>
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
    <p style="font-size:13px;color:#64748b;margin-bottom:16px;">We’ll email you a verification code before sending your <?= $isReport ? 'report' : 'message' ?> to terminal management.</p>
    <button type="submit" class="submit-btn"><i class="fas fa-envelope-circle-check"></i> Continue to email verification</button>
</form>
