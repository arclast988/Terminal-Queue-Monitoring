<!-- Restore the selected panel before first paint, while the page initializes. -->
<script>
(function () {
    var tabs = ['identity', 'media', 'themes', 'footer', 'operations'];
    var tab = window.location.hash.slice(1);
    try { if (tabs.indexOf(tab) < 0) tab = localStorage.getItem('sb_active_tab'); } catch (error) {}
    if (tabs.indexOf(tab) >= 0) document.documentElement.setAttribute('data-settings-initial-tab', tab);
})();
</script>
<style>
html[data-settings-initial-tab] .settings-page .tab-content { display: none; }
html[data-settings-initial-tab="identity"] #tab-identity,
html[data-settings-initial-tab="media"] #tab-media,
html[data-settings-initial-tab="themes"] #tab-themes,
html[data-settings-initial-tab="footer"] #tab-footer,
html[data-settings-initial-tab="operations"] #tab-operations { display: block; animation: none; }
html[data-settings-initial-tab] .settings-tab.active {
    background: transparent !important;
    color: #64748b !important;
    border-color: transparent !important;
    box-shadow: none;
}
html[data-settings-initial-tab] .settings-tab.active i { color: #64748b !important; }
html[data-settings-initial-tab="identity"] #tabBtn-identity,
html[data-settings-initial-tab="media"] #tabBtn-media,
html[data-settings-initial-tab="themes"] #tabBtn-themes,
html[data-settings-initial-tab="footer"] #tabBtn-footer,
html[data-settings-initial-tab="operations"] #tabBtn-operations {
    background: var(--primary) !important;
    border-color: var(--primary) !important;
    color: var(--on-primary, #fff) !important;
}
html[data-settings-initial-tab="identity"] #tabBtn-identity i,
html[data-settings-initial-tab="media"] #tabBtn-media i,
html[data-settings-initial-tab="themes"] #tabBtn-themes i,
html[data-settings-initial-tab="footer"] #tabBtn-footer i,
html[data-settings-initial-tab="operations"] #tabBtn-operations i { color: var(--on-primary, #fff) !important; }
</style>
