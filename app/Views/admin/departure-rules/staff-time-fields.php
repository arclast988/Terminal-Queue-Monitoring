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
                <!-- Hours Column -->
                <div class="tp-col">
                    <button type="button" class="tp-btn tp-btn-up" data-tp-action="hour-up" aria-label="Increase hour" tabindex="-1"><i class="bi bi-chevron-up"></i></button>
                    <input type="text" class="tp-input" id="<?= $field ?>_tp_hour" data-tp-input="hour" maxlength="2" inputmode="numeric" value="<?= date('g', $clockStamp) ?>" aria-label="Hour">
                    <button type="button" class="tp-btn tp-btn-down" data-tp-action="hour-down" aria-label="Decrease hour" tabindex="-1"><i class="bi bi-chevron-down"></i></button>
                </div>
                <!-- Separator -->
                <span class="tp-sep">:</span>
                <!-- Minutes Column -->
                <div class="tp-col">
                    <button type="button" class="tp-btn tp-btn-up" data-tp-action="min-up" aria-label="Increase minute" tabindex="-1"><i class="bi bi-chevron-up"></i></button>
                    <input type="text" class="tp-input" id="<?= $field ?>_tp_min" data-tp-input="min" maxlength="2" inputmode="numeric" value="<?= date('i', $clockStamp) ?>" aria-label="Minute">
                    <button type="button" class="tp-btn tp-btn-down" data-tp-action="min-down" aria-label="Decrease minute" tabindex="-1"><i class="bi bi-chevron-down"></i></button>
                </div>
                <!-- AM/PM Column -->
                <div class="tp-period-col">
                    <button type="button" class="tp-period-btn <?= date('A', $clockStamp) === 'AM' ? 'active' : '' ?>" data-tp-action="period" data-period="AM" aria-pressed="<?= date('A', $clockStamp) === 'AM' ? 'true' : 'false' ?>">AM</button>
                    <button type="button" class="tp-period-btn <?= date('A', $clockStamp) === 'PM' ? 'active' : '' ?>" data-tp-action="period" data-period="PM" aria-pressed="<?= date('A', $clockStamp) === 'PM' ? 'true' : 'false' ?>">PM</button>
                </div>
                <!-- Set Button -->
                <button type="button" class="tp-set-btn" data-clock-set aria-label="Set time">
                    <i class="bi bi-check2"></i>
                    <span>Set</span>
                </button>
                <!-- Hidden synchronized select elements for test & form compatibility -->
                <div class="tp-hidden-selects" aria-hidden="true">
                    <select id="<?= $field ?>Hour" class="form-select" data-clock-hour data-no-autocomplete tabindex="-1">
                        <?php for ($hour = 1; $hour <= 12; $hour++): ?><option value="<?= $hour ?>" <?= (int) date('g', $clockStamp) === $hour ? 'selected' : '' ?>><?= $hour ?></option><?php endfor; ?>
                    </select>
                    <select id="<?= $field ?>Minute" class="form-select" data-clock-minute data-no-autocomplete tabindex="-1">
                        <?php for ($minute = 0; $minute < 60; $minute++): ?><option value="<?= sprintf('%02d', $minute) ?>" <?= (int) date('i', $clockStamp) === $minute ? 'selected' : '' ?>><?= sprintf('%02d', $minute) ?></option><?php endfor; ?>
                    </select>
                    <select id="<?= $field ?>Period" class="form-select" data-clock-period data-no-autocomplete tabindex="-1">
                        <?php foreach (['AM', 'PM'] as $period): ?><option value="<?= $period ?>" <?= date('A', $clockStamp) === $period ? 'selected' : '' ?>><?= $period ?></option><?php endforeach; ?>
                    </select>
                </div>
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
