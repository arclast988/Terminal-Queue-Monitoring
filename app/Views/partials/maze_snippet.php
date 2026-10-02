<?php
/**
 * Maze Universal Snippet (Remote Testing Mode)
 * Activates exclusively when running inside a Maze test context
 * (embedded iframe on t.maze.co, opened by Maze, or active test session),
 * preventing unnecessary 500KB+ background prompt scripts and DOM observers
 * from executing during standalone mobile browsing.
 */
?>
<!-- Maze Universal Snippet -->
<script>
(function (m, a, z, e) {
  var inIframe = false;
  try { inIframe = (m.self !== m.top) || (m.parent !== m); } catch (err) { inIframe = true; }
  var hasOpener = !!m.opener;
  var ref = a.referrer || '';
  var isFromMaze = ref.indexOf('maze.co') !== -1;
  var search = (m.location && m.location.search) ? m.location.search : '';
  var hash = (m.location && m.location.hash) ? m.location.hash : '';
  var hasMazeParam = /[?&#](lwt|maze|test)=/i.test(search + hash);
  var wasActive = false;
  try {
    wasActive = !!m.sessionStorage.getItem('maze_test_active') || !!m.sessionStorage.getItem('maze:lwt-start') || !!m.sessionStorage.getItem('maze-us');
  } catch (err) {}

  var shouldRun = inIframe || hasOpener || isFromMaze || hasMazeParam || wasActive;
  if (!shouldRun) return;

  try { m.sessionStorage.setItem('maze_test_active', '1'); } catch (err) {}
  var t;
  try { t = m.sessionStorage.getItem('maze-us'); } catch (err) {}
  if (!t) {
    t = new Date().getTime();
    try { m.sessionStorage.setItem('maze-us', t); } catch (err) {}
  }
  m.mazeUniversalSnippetApiKey = e;

  var u = document.currentScript || (function () {
    var w = document.getElementsByTagName('script');
    return w[w.length - 1];
  })();
  var v = u && u.nonce;

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
