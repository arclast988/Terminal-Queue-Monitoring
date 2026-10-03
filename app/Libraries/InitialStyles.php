<?php

namespace App\Libraries;

final class InitialStyles
{
    /** Deliver page CSS before body markup without changing its cascade order. */
    public static function prepare(string $html): string
    {
        if (stripos($html, '<body') === false || stripos($html, '<style') === false) {
            return $html;
        }

        // Treat raw text and inactive/nested markup as whole tokens. In particular,
        // JavaScript strings containing HTML must remain untouched.
        $pattern = '~<!--.*?-->|<(script|textarea|template|noscript|svg)\b[^>]*>.*?</\1\s*>|<head\b[^>]*>|</head\s*>|<style\b[^>]*>.*?</style\s*>~is';
        if (!preg_match_all($pattern, $html, $tokens, PREG_SET_ORDER | PREG_OFFSET_CAPTURE)) {
            return $html;
        }

        $headOpen = false;
        $headEnd = null;
        $headTag = '';
        $styles = [];
        foreach ($tokens as $token) {
            [$markup, $offset] = $token[0];
            if (preg_match('~^<head\b~i', $markup)) {
                $headOpen = true;
            } elseif ($headOpen && $headEnd === null && preg_match('~^</head\s*>$~i', $markup)) {
                $headEnd = $offset;
                $headTag = $markup;
            } elseif ($headEnd !== null && preg_match('~^<style\b~i', $markup)) {
                $styles[] = [$markup, $offset];
            }
        }
        if ($headEnd === null || !$styles) {
            return $html;
        }

        $bodyStart = $headEnd + strlen($headTag);
        $rest = substr($html, $bodyStart);
        foreach (array_reverse($styles) as [$markup, $offset]) {
            $start = $offset - $bodyStart;
            $rest = substr($rest, 0, $start) . substr($rest, $start + strlen($markup));
        }

        return substr($html, 0, $headEnd) . implode('', array_column($styles, 0)) . $headTag . $rest;
    }
}
