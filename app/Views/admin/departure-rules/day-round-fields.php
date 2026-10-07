<?php
$selectedDays = old('days_of_week', departure_rule_days($rule ?? []));
$selectedDays = is_array($selectedDays) ? array_map('intval', $selectedDays) : departure_rule_days($rule ?? []);
$selectedRound = max(1, (int) old('round_number', $rule['round_number'] ?? 1));
$isCreatingRound = empty($rule['id']);
$formRouteId = old('route_id', $rule['route_id'] ?? $selectedRouteId ?? '');
$formTerminalId = (int) old('terminal_id', $rule['terminal_id'] ?? (count($terminals ?? []) === 1 ? $terminals[0]['id'] : 0));
$formDestination = null;
foreach ($routes ?? [] as $formRoute) {
    if ($formRouteId !== '' && (int) $formRoute['id'] === (int) $formRouteId) {
        $formDestination = $formRoute['destination'];
        $formTerminalId = (int) $formRoute['terminal_id'];
        break;
    }
}
$formRoundScope = departure_round_scope($formTerminalId, $formDestination);
$originalRoundScope = $formRoundScope;
$originalRound = $isCreatingRound ? 0 : max(1, (int) ($rule['round_number'] ?? 1));
foreach ($existingRules ?? [] as $savedRoundRule) {
    if (!$isCreatingRound && (int) $savedRoundRule['id'] === (int) $rule['id']) {
        $originalRoundScope = $savedRoundRule['round_scope'] ?? $formRoundScope;
        break;
    }
}
// Only unused rounds for the selected weekdays can be assigned. Rules for
// different destinations, or disjoint weekdays, keep their own numbering.
$currentRuleId = (int) ($rule['id'] ?? 0);
$configuredRounds = [];
foreach ($existingRules ?? [] as $savedRoundRule) {
    if (($savedRoundRule['round_scope'] ?? '') === $formRoundScope
        && (int) $savedRoundRule['id'] !== $currentRuleId
        && array_intersect($selectedDays, departure_rule_days($savedRoundRule))) {
        $configuredRounds[] = max(1, (int) $savedRoundRule['round_number']);
    }
}
$configuredRounds = array_values(array_unique($configuredRounds));
sort($configuredRounds);
$allowNewRound = $isCreatingRound || $formRoundScope !== $originalRoundScope
    || in_array($originalRound, $configuredRounds, true);
$roundChoices = [];
if (!$isCreatingRound && $formRoundScope === $originalRoundScope
    && !in_array($originalRound, $configuredRounds, true)) {
    $roundChoices[] = $originalRound;
}
$newRound = 1;
while ($newRound <= 999 && in_array($newRound, $configuredRounds, true)) $newRound++;
if (($allowNewRound || !$roundChoices) && $newRound <= 999) $roundChoices[] = $newRound;
if (!$allowNewRound && $originalRound > 1 && !in_array(1, $configuredRounds, true)) $roundChoices[] = 1;
$roundChoices = array_values(array_unique($roundChoices));
sort($roundChoices);
if ($allowNewRound && old('round_number') === null) $selectedRound = $newRound;
if (!in_array($selectedRound, $roundChoices, true)) $selectedRound = $roundChoices[0] ?? 0;
$singleRound = count($roundChoices) === 1 && !$configuredRounds;
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
        <label for="<?= $singleRound ? 'round_number_display' : 'round_number' ?>" class="form-label-modern" id="round_number_label">Assign to round <span class="text-danger">*</span></label>
        <?php $roundScopes = array_map(static fn(array $r): array => ['i' => (int) $r['id'], 's' => $r['round_scope'] ?? '', 'r' => (int) ($r['round_number'] ?? 1), 'd' => departure_rule_days($r)], $existingRules ?? []); ?>
        <select id="round_number" name="round_number" class="select-modern <?= $singleRound ? 'd-none' : '' ?>" <?= $singleRound ? 'hidden' : '' ?> data-no-autocomplete required data-round-scopes="<?= esc(json_encode($roundScopes), 'attr') ?>" data-round-create="<?= $isCreatingRound ? 'true' : 'false' ?>" data-round-rule-id="<?= $currentRuleId ?>" data-round-original="<?= $originalRound ?>" data-round-original-scope="<?= esc($originalRoundScope, 'attr') ?>">
            <?php foreach ($roundChoices as $roundChoice): ?>
            <option value="<?= $roundChoice ?>" <?= $roundChoice === $selectedRound ? 'selected' : '' ?>><?= $allowNewRound && !in_array($roundChoice, $configuredRounds, true) ? 'New round' : 'Round' ?> <?= $roundChoice ?></option>
            <?php endforeach; ?>
        </select>
        <input id="round_number_display" class="input-modern <?= $singleRound ? '' : 'd-none' ?>" <?= $singleRound ? '' : 'hidden' ?> type="text" value="Round <?= $selectedRound ?>" readonly>
        <p class="form-text-modern mt-2 mb-0">Each destination has one rule per round on a selected day. Add a new round for another time window; it becomes available in Queue Management.</p>
    </div>
</div>
<script src="<?= app_asset_url('js/departure-rule-days.js') ?>" defer></script>
