<?php

namespace App\Libraries;

/** Serialize cache read/update operations across PHP workers on this instance. */
final class RateLimitLock
{
    public static function run(array $keys, callable $operation): mixed
    {
        $directory = WRITEPATH . 'cache/rate-limit-locks';
        if (!is_dir($directory) && !@mkdir($directory, 0770, true) && !is_dir($directory)) {
            throw new \RuntimeException('Rate limit storage is unavailable.');
        }
        // Fixed lock shards avoid creating a permanent file per attacker address.
        $shards = array_unique(array_map(static fn (string $key): string => substr(hash('sha256', $key), 0, 2), $keys));
        sort($shards);
        $handles = [];
        try {
            foreach ($shards as $shard) {
                $handle = fopen($directory . '/' . $shard . '.lock', 'c');
                if ($handle === false) throw new \RuntimeException('Rate limit storage is unavailable.');
                $handles[] = $handle;
                if (!flock($handle, LOCK_EX)) throw new \RuntimeException('Rate limit storage is unavailable.');
            }
            return $operation();
        } finally {
            foreach (array_reverse($handles) as $handle) {
                flock($handle, LOCK_UN);
                fclose($handle);
            }
        }
    }
}
