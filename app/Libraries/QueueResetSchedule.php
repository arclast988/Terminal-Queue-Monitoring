<?php

namespace App\Libraries;

final class QueueResetSchedule
{
    public static function configuredCutoff(?int $now = null): ?string
    {
        return self::cutoff($now ?? time(), (string) get_system_setting('queue_reset_time', '00:00'),
            get_system_setting('queue_reset_enabled', '1') === '1', (int) get_system_setting('queue_reset_effective_from', '0'));
    }

    /** Most recent daily cutoff, or null while disabled/before a changed schedule starts. */
    public static function cutoff(int $now, string $clock = '00:00', bool $enabled = true, int $effectiveFrom = 0): ?string
    {
        if (!$enabled) return null;
        if (!preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $clock)) $clock = '00:00';
        [$hour, $minute] = array_map('intval', explode(':', $clock));
        $local = (new \DateTimeImmutable('@' . $now))->setTimezone(new \DateTimeZone('Asia/Manila'));
        $cutoff = $local->setTime($hour, $minute);
        if ($cutoff > $local) $cutoff = $cutoff->modify('-1 day');
        if ($effectiveFrom > 0 && $cutoff->getTimestamp() <= $effectiveFrom) return null;
        return $cutoff->format('Y-m-d H:i:s');
    }
}
