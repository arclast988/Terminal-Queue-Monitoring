<script>
<?= file_get_contents(FCPATH . 'assets/js/auth-background.js') ?>
window.TerminalAuthBackground.mount(<?= json_encode([
    'slides' => $authSlides,
    'defaults' => $defaultAuthSlides,
], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES) ?>);
</script>
