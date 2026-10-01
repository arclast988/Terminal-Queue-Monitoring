<?php
declare(strict_types=1);

// CLI-only fixture: exercise the actual CSS generator without a database.
function esc($value): string { return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function app_bg_image(): string { return '/fixture/bg-fallback.svg'; }

$source = $argv[2] ?? dirname(__DIR__, 2) . '/app/Common.php';
require $source;
$count = (int) ($argv[1] ?? 3);
$slides = array_slice(['/fixture/bg-first.svg', '/fixture/bg-second.svg', '/fixture/bg-third.svg'], 0, $count);
echo app_bg_slideshow_css($slides, 0.12, 'body:not(.auth-page)::before');
