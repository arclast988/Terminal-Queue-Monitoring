<?php

namespace App\Libraries;

final class DepartureRuleDefaults
{
    public static function rules(): array
    {
        return [
            ['time_from' => '05:00:00', 'time_to' => '12:00:00', 'wait_minutes' => 20, 'label' => 'Morning'],
            ['time_from' => '12:00:00', 'time_to' => '18:00:00', 'wait_minutes' => 25, 'label' => 'Afternoon'],
        ];
    }
}
