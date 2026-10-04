<?php
$savedRule = $rule ?? [];
$waitParts = explode(':', (string) ($defaultWaitValue ?? '00:30'));
$minutes = (int) ($waitParts[0] ?? 0) * 60 + (int) ($waitParts[1] ?? 0);
?>
<div class="row" id="dispatchRuleTimes" data-existing-rules="<?= esc(json_encode($existingRules ?? []), 'attr') ?>" data-rule-id="<?= (int) ($savedRule['id'] ?? 0) ?>">
    <?php foreach (['time_from' => ['Start time (AM/PM)', '00:00'], 'time_to' => ['End time (AM/PM)', '23:59']] as $field => [$caption, $fallback]):
        $clockValue = departure_clock_value((string) old($field, operations_time($savedRule[$field] ?? $fallback)), true) ?? $fallback;
        $clockStamp = strtotime($clockValue);
    ?>
    <div class="col-md-6 mb-4 dispatch-time-field">
        <label for="<?= $field ?>" class="form-label-modern"><?= $caption ?> <span class="text-danger">*</span></label>
        <div class="dispatch-time-wrap" data-clock-field="<?= $field ?>">
            <input type="text" class="input-modern" id="<?= $field ?>" name="<?= $field ?>" value="<?= esc(date('g:i A', $clockStamp)) ?>" readonly required aria-describedby="<?= $field ?>Help" data-clock-display>
            <button type="button" class="dispatch-time-button" data-clock-toggle aria-label="Set <?= strtolower($caption) ?>" aria-controls="<?= $field ?>Picker" aria-expanded="false"><i class="bi bi-clock" aria-hidden="true"></i></button>
            <div class="dispatch-time-picker" id="<?= $field ?>Picker" role="group" aria-label="<?= $caption ?> picker" hidden>
                <div><label for="<?= $field ?>Hour">Hour</label><select id="<?= $field ?>Hour" class="form-select" data-clock-hour data-no-autocomplete>
                    <?php for ($hour = 1; $hour <= 12; $hour++): ?><option value="<?= $hour ?>" <?= (int) date('g', $clockStamp) === $hour ? 'selected' : '' ?>><?= $hour ?></option><?php endfor; ?>
                </select></div>
                <div><label for="<?= $field ?>Minute">Minute</label><select id="<?= $field ?>Minute" class="form-select" data-clock-minute data-no-autocomplete>
                    <?php for ($minute = 0; $minute < 60; $minute++): ?><option value="<?= sprintf('%02d', $minute) ?>" <?= (int) date('i', $clockStamp) === $minute ? 'selected' : '' ?>><?= sprintf('%02d', $minute) ?></option><?php endfor; ?>
                </select></div>
                <div><label for="<?= $field ?>Period">AM / PM</label><select id="<?= $field ?>Period" class="form-select" data-clock-period data-no-autocomplete>
                    <?php foreach (['AM', 'PM'] as $period): ?><option value="<?= $period ?>" <?= date('A', $clockStamp) === $period ? 'selected' : '' ?>><?= $period ?></option><?php endforeach; ?>
                </select></div>
                <button type="button" class="btn-modern btn-modern-primary btn-modern-sm" data-clock-set><i class="bi bi-check2" aria-hidden="true"></i> Set time</button>
            </div>
        </div>
        <div id="<?= $field ?>Help" class="form-text-modern">Use the clock button to set the time.</div>
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
