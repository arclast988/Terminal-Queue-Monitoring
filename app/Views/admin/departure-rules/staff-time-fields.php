<?php
$savedRule = $rule ?? [];
$waitParts = explode(':', (string) ($defaultWaitValue ?? '00:30'));
$minutes = (int) ($waitParts[0] ?? 0) * 60 + (int) ($waitParts[1] ?? 0);
?>
<div class="row" id="dispatchRuleTimes" data-existing-rules="<?= esc(json_encode($existingRules ?? []), 'attr') ?>" data-rule-id="<?= (int) ($savedRule['id'] ?? 0) ?>">
    <?php foreach (['time_from' => ['Start time (AM/PM)', '00:00', '5:00 AM'], 'time_to' => ['End time (AM/PM)', '23:59', '5:00 PM']] as $field => [$caption, $fallback, $example]): ?>
    <div class="col-md-6 mb-4">
        <label for="<?= $field ?>" class="form-label-modern"><?= $caption ?> <span class="text-danger">*</span></label>
        <input type="text" class="input-modern" id="<?= $field ?>" name="<?= $field ?>" value="<?= esc(old($field, operations_time($savedRule[$field] ?? $fallback))) ?>" placeholder="<?= $example ?>" maxlength="9" required autocomplete="off" aria-describedby="<?= $field ?>Help">
        <div id="<?= $field ?>Help" class="form-text-modern">Enter a time with AM or PM, such as <?= $example ?>.</div>
    </div>
    <?php endforeach; ?>
    <div class="col-md-6 mb-4">
        <label for="wait_minutes" class="form-label-modern">Departure interval (minutes) <span class="text-danger">*</span></label>
        <input type="number" class="input-modern" id="wait_minutes" name="wait_minutes" min="1" max="1439" step="1" value="<?= esc(old('wait_minutes', $minutes)) ?>" required>
        <div class="form-text-modern">Boarding duration for this round, for example 20 or 25 minutes.</div>
    </div>
    <div class="col-md-6 mb-4">
        <label for="label" class="form-label-modern">Rule name (optional)</label>
        <input type="text" class="input-modern" id="label" name="label" value="<?= esc(old('label', $savedRule['label'] ?? '')) ?>" placeholder="e.g. Morning departures" maxlength="50">
    </div>
</div>
