<?php

namespace App\Log\Handlers;

use CodeIgniter\Log\Handlers\FileHandler;
use Throwable;

class WslFileHandler extends FileHandler
{
    /**
     * Handles logging the message.
     * Overrides core FileHandler to catch and suppress chmod warnings on WSL DrvFs mounts.
     */
    public function handle($level, $message): bool
    {
        // Suppress errors during execution of the parent handler to prevent chmod warnings
        // from being escalated to exceptions by CodeIgniter's error handler.
        $oldHandler = set_error_handler(static function ($severity, $message, $file, $line) {
            if (str_contains($message, 'chmod')) {
                return true; // Suppress chmod errors/warnings
            }
            return false; // Propagate others
        });

        try {
            $result = parent::handle($level, $message);
        } catch (Throwable $e) {
            $result = false;
        } finally {
            if ($oldHandler !== null) {
                set_error_handler($oldHandler);
            }
        }

        return $result;
    }
}
