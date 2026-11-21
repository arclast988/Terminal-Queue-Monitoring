<style>
    .auth-bg-slideshow:not([data-bg-ready]) {
        background-image: var(--tq-background-photo, url(<?= json_encode($authFallbackImage, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES) ?>));
        background-position: center;
        background-size: cover;
    }
</style>
<noscript><style>#authBgSlideshow .auth-bg-slide:first-child { opacity: 1; }</style></noscript>
