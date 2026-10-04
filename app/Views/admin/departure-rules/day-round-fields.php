<?php
$selectedDays = old('days_of_week', departure_rule_days($rule ?? []));
$selectedDays = is_array($selectedDays) ? array_map('intval', $selectedDays) : departure_rule_days($rule ?? []);
$selectedRound = max(1, (int) old('round_number', $rule['round_number'] ?? 1));
$roundChoices = departure_round_choices($existingRules ?? [], $selectedRound);
?>
<div class="departure-rule-schedule mb-4">
    <fieldset class="rule-days-fieldset" id="ruleDays" aria-describedby="ruleDaysSummary">
        <div class="rule-days-heading">
            <legend class="form-label-modern mb-0">Days</legend>
            <label class="rule-every-day"><input type="checkbox" id="ruleEveryDay" <?= count($selectedDays) === 7 ? 'checked' : '' ?>> Every day</label>
        </div>
        <input type="hidden" name="day_selection" value="1">
        <div class="rule-days-grid">
            <?php foreach ([1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday', 7 => 'Sunday'] as $dayNumber => $dayName): ?>
            <label class="rule-day-option">
                <input type="checkbox" name="days_of_week[]" value="<?= $dayNumber ?>" <?= in_array($dayNumber, $selectedDays, true) ? 'checked' : '' ?>>
                <span><?= $dayName ?></span>
            </label>
            <?php endforeach; ?>
        </div>
        <p class="rule-days-summary" id="ruleDaysSummary" aria-live="polite"><?= esc(departure_rule_day_label(['days_of_week' => $selectedDays])) ?></p>
    </fieldset>
    <div class="rule-round-field">
        <label for="round_number" class="form-label-modern">Assign to round <span class="text-danger">*</span></label>
        <?php $roundScopes = array_map(static fn(array $r): array => ['s' => $r['round_scope'] ?? '', 'r' => (int) ($r['round_number'] ?? 1)], $existingRules ?? []); ?>
        <select id="round_number" name="round_number" class="select-modern" data-no-autocomplete required data-round-scopes="<?= esc(json_encode($roundScopes), 'attr') ?>">
            <?php foreach ($roundChoices as $roundChoice): ?>
            <option value="<?= $roundChoice ?>" <?= $roundChoice === $selectedRound ? 'selected' : '' ?>>Round <?= $roundChoice ?></option>
            <?php endforeach; ?>
        </select>
        <p class="form-text-modern mt-2 mb-0">This rule applies when the dispatcher selects this round in Queue Management.</p>
    </div>
</div>
<script src="<?= app_asset_url('js/departure-rule-days.js') ?>" defer></script>
