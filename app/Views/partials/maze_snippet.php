<?php
/**
 * Maze Universal Snippet (Deferred Non-Blocking)
 * Loads asynchronously after the page has finished rendering,
 * ensuring fast initial load while enabling remote usability testing.
 */
?>
<!-- Maze Universal Snippet -->
<script>
(function (m, a, z, e) {
  var t, u, v;
  try { t = m.sessionStorage.getItem('maze-us'); } catch (err) {}
  if (!t) {
    t = new Date().getTime();
    try { m.sessionStorage.setItem('maze-us', t); } catch (err) {}
  }
  m.mazeUniversalSnippetApiKey = e;

  u = document.currentScript || (function () {
    var w = document.getElementsByTagName('script');
    return w[w.length - 1];
  })();
  v = u && u.nonce;

  function injectMaze() {
    if (m._mazeInjected) return;
    m._mazeInjected = true;
    var s = a.createElement('script');
    s.src = z + '?apiKey=' + e;
    s.async = true;
    s.defer = true;
    if (v) s.setAttribute('nonce', v);
    (a.head || a.getElementsByTagName('head')[0] || a.documentElement).appendChild(s);
  }

  if (a.readyState === 'complete') {
    setTimeout(injectMaze, 60);
  } else {
    m.addEventListener('load', function () { setTimeout(injectMaze, 60); }, { once: true });
  }
})(window, document, 'https://snippet.maze.co/maze-universal-loader.js', 'b00dffea-1c8b-4fbb-a3aa-645655850f28');
</script>
<!-- End Maze Universal Snippet -->
