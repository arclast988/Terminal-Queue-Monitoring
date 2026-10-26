<?php

namespace App\Libraries;

/** Readable descriptions for both current and historical activity records. */
final class ActivityLogFormatter
{
    private const ACTIONS = [
        'Login' => 'Signed in',
        'Logout' => 'Signed out',
        'Password Reset' => 'Reset password',
        'Password Changed' => 'Changed password',
        'Profile' => 'Changed profile picture',
        'Create user' => 'Added account',
        'Create user with routes' => 'Added dispatcher account and assigned routes',
        'Update user' => 'Updated account',
        'Update dispatcher routes' => 'Changed dispatcher route access',
        'Deactivate user' => 'Archived account',
        'Activate user' => 'Restored account',
        'Delete user' => 'Permanently deleted account',
        'Bulk deactivate users' => 'Archived selected accounts',
        'Bulk activate users' => 'Restored selected accounts',
        'Bulk delete users' => 'Permanently deleted selected accounts',
        'Update user avatar' => 'Changed account profile picture',
        'Remove user avatar' => 'Removed account profile picture',
        'Assign vehicle to route' => 'Registered vehicle and assigned route',
        'Reassign vehicle route' => 'Changed vehicle route',
        'Update vehicle' => 'Updated vehicle information',
        'Deactivate vehicle' => 'Archived vehicle',
        'Activate vehicle' => 'Restored vehicle',
        'Delete vehicle' => 'Permanently deleted vehicle',
        'Bulk deactivate vehicles' => 'Archived selected vehicles',
        'Bulk activate vehicles' => 'Restored selected vehicles',
        'Bulk delete vehicles' => 'Permanently deleted selected vehicles',
        'Create route' => 'Added route',
        'Update route' => 'Updated route',
        'Update route group' => 'Updated route for all vehicle types',
        'Delete route' => 'Deleted route',
        'Delete route group' => 'Deleted route for all vehicle types',
        'Deactivate route group' => 'Archived route',
        'Activate route group' => 'Restored route',
        'Bulk deactivate routes' => 'Archived selected routes',
        'Bulk activate routes' => 'Restored selected routes',
        'Bulk delete routes' => 'Permanently deleted selected routes',
        'Assign fare to route' => 'Set route fare',
        'Update fare' => 'Changed fare',
        'Delete fare' => 'Removed fare',
        'Add discount' => 'Added passenger discount',
        'Update discount' => 'Changed passenger discount',
        'Delete discount' => 'Removed passenger discount',
        'Create terminal' => 'Added terminal',
        'Update terminal' => 'Updated terminal',
        'Delete terminal' => 'Deleted terminal',
        'Bulk delete terminals' => 'Deleted selected terminals',
        'Add vehicle type' => 'Added vehicle type',
        'Update vehicle type' => 'Updated vehicle type',
        'Delete vehicle type' => 'Deleted vehicle type',
        'Create announcement' => 'Posted announcement',
        'Update announcement' => 'Edited announcement',
        'Delete announcement' => 'Deleted announcement',
        'Delete all announcements' => 'Deleted all announcements',
        'Bulk delete announcements' => 'Deleted selected announcements',
        'Create departure rule' => 'Added departure schedule',
        'Update departure rule' => 'Changed departure schedule',
        'Delete departure rule' => 'Deleted departure schedule',
        'Bulk delete departure rules' => 'Deleted selected departure schedules',
        'System Settings' => 'Changed system settings',
        'Automatic queue reset' => 'Cleared queue automatically',
        'Content Manager' => 'Updated website content',
        'Change dispatch round' => 'Changed dispatch round',
        'Add to queue' => 'Added vehicle to waiting line',
        'Start Boarding' => 'Started passenger boarding',
        'Depart Vehicle' => 'Marked vehicle as departed',
        'Cancel Trip' => 'Canceled trip',
        'Cancel Selected Trips' => 'Canceled selected trips',
        'Undo Cancel Trip' => 'Restored canceled trip',
        'Updated Driver' => 'Changed trip driver',
        'Reordered Queue' => 'Changed vehicle order in waiting line',
        'Unauthorized queue action attempt' => 'Blocked change to an unassigned route',
        'Unauthorized queue reorder attempt' => 'Blocked reordering of an unassigned route',
    ];

    public static function action(?string $action): string
    {
        foreach (self::ACTIONS as $stored => $label) {
            if (strcasecmp(trim($action ?? ''), $stored) === 0) {
                return $label;
            }
        }

        return trim($action ?? '') ?: 'Other activity';
    }

    /** Keep searches for the words shown on screen compatible with stored actions. */
    public static function matchingActions(string $search): array
    {
        $matches = [];
        foreach (self::ACTIONS as $stored => $label) {
            if ($search !== '' && stripos($label, $search) !== false) {
                $matches[] = $stored;
            }
        }

        return $matches;
    }

    public static function details(?string $action, ?string $details): string
    {
        $text = trim($details ?? '');
        $key = strtolower(trim($action ?? ''));
        $label = self::action($action);
        if ($text === '' || $text === '—') {
            return $label . '. No additional details were recorded.';
        }

        // Announcement messages are user content; never rewrite words inside them.
        if ($key === 'create announcement') {
            return 'Posted announcement: ' . $text;
        }
        if (in_array($key, ['update announcement', 'delete announcement'], true)) {
            return preg_replace('/^ID (\d+): /', $label . ' #$1: ', $text);
        }

        if ($key === 'system settings') {
            if (preg_match('/^Updated operational rules: Queue Cooldown=(\d+)m, Log Retention=(\d+)d, Departure Retention=(\d+)d$/', $text, $m)) {
                return "Changed operating settings: vehicles must wait {$m[1]} minutes before joining the waiting line again; activity logs are kept for {$m[2]} days; departure history is kept for {$m[3]} days.";
            }
            if (preg_match('/^Updated role theme colours \((\d+) settings\)$/', $text, $m)) {
                return "Changed the colors used for guest, dispatcher, or administrator pages ({$m[1]} color settings).";
            }
            $text = preg_replace('/^Updated system themes & identity \(name: (.*)\)$/s', 'Changed the system name and appearance. System name: $1.', $text);
            $text = preg_replace('/^Uploaded new login page hero illustration: /', 'Changed the sign-in page illustration. File: ', $text);
            $text = preg_replace('/^Restored system logo in login page hero panel$/', 'Restored the system logo on the sign-in page.', $text);
            $text = preg_replace('/^(?:Switched|Saved) background display mode to: (single|slideshow)$/', 'Changed how background pictures are shown: $1.', $text);
            $text = preg_replace('/^(Uploaded new|Reset) slideshow background picture for slot (\d+)/', '$1 rotating background picture #$2', $text);
            $text = preg_replace('/^Added new slideshow slot (\d+): /', 'Added rotating background picture #$1. File: ', $text);
            $text = preg_replace('/^Deleted slideshow slot (\d+)\. Active slots remaining: /', 'Removed rotating background picture #$1. Remaining picture numbers: ', $text);
            $text = preg_replace('/^Reset all slideshow background pictures to defaults$/', 'Restored all default rotating background pictures.', $text);
            $text = str_replace('are shown: single.', 'are shown: one picture.', $text);
            $text = str_replace('are shown: slideshow.', 'are shown: rotating pictures.', $text);
        } elseif ($key === 'content manager' && str_starts_with($text, 'Updated managed content: ')) {
            $groups = explode(', ', substr($text, strlen('Updated managed content: ')));
            $text = 'Updated website content: ' . implode(', ', array_map(static fn ($group) => ucwords(str_replace('_', ' ', $group)), $groups)) . '.';
        } elseif ($key === 'login' || $key === 'logout') {
            $text = preg_replace('/^Staff\b/', 'Dispatcher', $text);
            $text = preg_replace('/^Super_admin\b/i', 'Super administrator', $text);
            $text = preg_replace('/^Admin\b/', 'Administrator', $text);
            $text = str_replace([' logged in', ' logged out'], [' signed in', ' signed out'], $text);
        } elseif ($key === 'password changed') {
            $text = preg_replace('/^Password changed with code authentication for /', 'Changed password after confirming a verification code for ', $text);
        } elseif ($key === 'create user') {
            $text = preg_replace('/^Created user /', 'Added account for ', $text);
            $text = preg_replace_callback('/ with role: (staff|admin|super_admin)$/', static fn ($m) => ' with access as ' . ['staff' => 'Dispatcher', 'admin' => 'Administrator', 'super_admin' => 'Super administrator'][$m[1]] . '.', $text);
        } elseif ($key === 'create user with routes') {
            $text = preg_replace('/^Created dispatcher /', 'Added dispatcher account for ', $text);
            $text = str_replace(' with routes: ', ' with access to these routes: ', $text);
        } elseif ($key === 'update dispatcher routes') {
            $text = preg_replace('/^Updated dispatcher /', 'Changed route access for dispatcher ', $text);
            $text = str_replace(['. Added: ', '. Removed: '], ['. Gave access to: ', '. Removed access to: '], $text);
        } elseif (str_contains($key, 'departure rule')) {
            $text = preg_replace('/^(Added|Updated|Deleted) departure rule: /', '$1 departure schedule: ', $text);
            $text = preg_replace('/\b(\d+) min\b/', '$1 minutes', $text);
        } elseif ($key === 'add vehicle type') {
            $text = preg_replace('/ \((#[0-9a-f]{6}), ([a-z0-9-]+)\)\.$/i', '. Display color: $1; icon: $2.', $text);
        } elseif ($key === 'delete vehicle type') {
            $text = preg_replace('/ \(([a-z0-9_-]+)\) and associated routes and fares\.$/i', ' and its routes and fares.', $text);
        } elseif ($key === 'start boarding' || $key === 'depart vehicle' || $key === 'cancel trip') {
            $text = preg_replace('/^(Start Boarding|Depart Vehicle|Cancel Trip) for /', $label . ' for ', $text);
        } elseif ($key === 'cancel selected trips') {
            $text = preg_replace('/^Canceled queue trips: /', 'Canceled trips with waiting-line record numbers: ', $text);
        } elseif ($key === 'add to queue') {
            $text = preg_replace('/ to queue for /', ' to the waiting line for ', $text, 1);
            $text = preg_replace('/\. Rule: (.*) \((\d+) min\)\.$/s', '. Departure schedule: $1. Waiting time: $2 minutes.', $text);
        } elseif ($key === 'reordered queue') {
            $text = preg_replace('/ queue\.$/', ' waiting line.', $text);
        } elseif ($key === 'undo cancel trip') {
            $text = preg_replace('/ back to the active queue\.$/', ' to the waiting line.', $text);
        }

        // Translate record-management terms only for the actions that generate them.
        if (preg_match('/^(?:bulk )?(?:deactivate|activate|delete) (?:user|vehicle|route)/', $key)) {
            $text = preg_replace('/^(?:Deactivated|Reactivated|Activated) /', str_contains($key, 'deactivate') ? 'Archived ' : 'Restored ', $text);
            $text = preg_replace('/^(Archived|Restored|Permanently deleted) (\d+ )?(archived )?user(?=\(s\)|\s|$)/', '$1 $2$3account', $text);
            $text = preg_replace_callback('/\b(\d+) (archived )?(account|vehicle|route group|route)\(s\)/', static function ($m) {
                $noun = $m[3] === 'route group' ? 'route' : $m[3];
                return $m[1] . ' ' . $m[2] . $noun . ((int) $m[1] === 1 ? '' : 's');
            }, $text);
            $text = preg_replace('/^(Archived|Restored) route group /', '$1 route ', $text);
            $text = preg_replace('/ and moved to archive\.?$/', '. Available to restore from the archive.', $text);
        }

        // A few actions store only the affected route; supply the missing context.
        if (in_array($key, ['create route', 'assign fare to route', 'update route', 'update route group', 'delete route', 'change dispatch round'], true)) {
            $text = $label . ': ' . $text;
        }

        return $text;
    }
}
