# Interaction performance review

Reviewed base: `c4048e1923aac4452526f7d9b45f7eca5192df8c`.

## 1. Before and after

| Area and source | Before | After | Behavior preserved |
| --- | --- | --- | --- |
| Shared loader: `public/assets/js/global-loader.js` | Progress advanced by animating `width`. Old completion timers could reset a newer request and release every loading button. | Progress uses a fixed-width element and `scaleX`; tracked completion timers cancel when a new operation starts. Request counts and button ownership are separate. | Same public methods, fetch/XHR arguments, response objects, rejection objects and silent endpoints. No network request waits for animation. |
| Button feedback | Replaced `innerHTML` after 200 ms, rebuilding child nodes. Pending protection started with the animation. | Registers pending state immediately; appends an absolute feedback overlay while leaving original nodes, values and dimensions intact. Click and repeat-submit guards protect only an already-pending action. | First handler runs normally. Existing child listeners, native successful-control values, validation and custom cancellation remain intact. Native submitters stay enabled so their name/value is submitted. |
| Table/card feedback | Spinner overlay could intercept input; an old removal timer could remove a reused overlay. | Decorative structural skeleton, status text and `pointer-events: none`; removal timer is canceled on reuse. | Existing table controls and data remain available. No fake records or synthetic data are introduced. |
| Login: `app/Views/auth/login.php` | Login card used a 700 ms entrance with a 250 ms delay. | Title, fields, options and CTA use transform/opacity with a complete sequence budget of 240 ms. | Same form action/method, CSRF field, autofocus, password toggle, Remember me and validators. No exit animation delays authentication or redirects. |
| Shared motion: `public/assets/css/interaction-motion.css` | Motion depended on viewport/preferences; the shared overrides were not loaded by the reviewed page heads. | Shared overrides are loaded across authentication, guest and authenticated templates. Full mode uses 120–180 ms interactions; lite mode uses 100 ms fades; reduced mode is static. | Server-rendered content stays focusable and clickable during entry. Bootstrap owns dialog state, focus and routing behavior. |
| Guest passenger fill: `app/Views/public/enhanced_dashboard.php` | Live passenger updates animated bar width for 400 ms. | Initial PHP markup, new-card markup and both update paths set a ratio used by `scaleX`. | Same percentages, colors, capacities and server-confirmed queue state. |
| JSON queue polling: `public/js/queue-sync.js` | Multiple slow JSON reads could overlap; anonymous optimistic/storage listeners survived `destroy()`. Hidden tabs still woke the interval timer. | One active read plus at most one trailing reconciliation. Lifecycle generations ignore old responses, optional abort controllers cancel discarded reads, named listeners are removed, and hidden polling timers stop. | Same URLs, request methods and headers, data fields, WS cooldown, structural refresh, modal protections, connected heartbeat and reconnection behavior. Return-to-tab reconciliation is immediate. |

The shared policy is inlined from `public/assets/js/interaction-motion.js` by `app/Views/partials/interaction_assets.php`. This avoids an additional blocking JavaScript request. It does not inspect account data or change application logic. Hardware hints are optional: up to four reported logical processors or up to 4 GB reported memory select lite mode; missing hints select full mode unless reduced motion is requested. Preference changes update presentation live. GPU hints are limited to the small active progress bar, with existing short-lived passenger badges retained.

## 2. Production code and API contract

- [Shared motion policy](../public/assets/js/interaction-motion.js)
- [Shared authentication background controller](../public/assets/js/auth-background.js)
- [Shared CSS](../public/assets/css/interaction-motion.css)
- [Progress, pending buttons and skeleton helpers](../public/assets/js/global-loader.js)
- [Queue request/lifecycle handling](../public/js/queue-sync.js)
- [Typed UI and queue API contracts](../types/interaction-contracts.d.ts)

The application remains PHP/CodeIgniter and vanilla JavaScript. TypeScript checks the new motion policy and public contract declarations. Test tools are isolated in `tests/frontend`; there are no new runtime dependencies.

Backend audit: `Api/QueueStatus::index()` already projects the active queue fields explicitly, releases the PHP session lock before read-only work, and maintains a two-second cache invalidated by the sync token. `Home::status()` already collapses concurrent cache misses with a file lock and checks the cache again after acquiring it. No response fields, cache lifetimes, database queries, controllers, authentication, permissions, routing, capacity/cooldown checks or mutation payloads were changed. Photos, driver names, positions, statuses, timestamps, colors and sync hashes remain available to existing consumers.

Existing optimistic passenger feedback and its error/reconciliation hooks are retained. Queue membership, departures, capacity decisions and permissions continue to use server-confirmed results. Extending optimistic writes to those actions would change the system's semantics and is outside this refactor.

## 3. Verification and metrics

Local checks: 20 request/lifecycle regressions pass; the typed motion policy and auth background controller pass strict TypeScript; edited external JavaScript parses; `git diff --check` passes. The regression suite exercises immediate transport calls, unchanged arguments/response identity, completion overlap, independent button ownership, original DOM/listeners, canceled forms, duplicate native submits, synchronous XHR errors, reused table overlays, hardware/preferences, coalesced polls, lifecycle cleanup, stale responses and WS/optimistic cooldown protection. Background regressions cover hidden tabs, live motion changes, stale image/frame callbacks, failed images, branding changes, BFCache restoration and reinitialization.

| Metric | Before | After | Evidence |
| --- | --- | --- | --- |
| Login-card entrance budget | 950 ms | Complete stagger: 240 ms | CSS configuration; browser fixture checks maximum delay + duration |
| Four JSON refreshes arriving before any response | Four concurrent requests | One concurrent request, at most one trailing read | Deterministic request regression |
| Pending spinner child-node replacements | Replaced original children | Zero original child replacements | Node identity/listener regression and browser dimension test |
| Queue timer while hidden | Interval wakes and skips work | No queue poll interval | Timer/lifecycle regression |
| Progress animation property | Width | Transform | Source diff and Chromium fixture |
| Progress layouts during 36 frame updates | 36 on the initial recording | 0 | Recorded Chromium fixture; 1280 × 900 viewport; latest run linked from the PR |
| Production dependency additions | — | Zero | Dependency changes are limited to isolated test tools |

The frontend workflow runs actual Chromium checks for native form payloads and validation, duplicate submits, dimensions and focus, low-motion modes, non-blocking skeletons, completion overlap and a 36-frame before/after progress rendering fixture. Rendering metrics are saved in the `interaction-rendering-chromium` CI artifact. The existing CI workflow continues to run PHP lint, database migrations and PHPUnit.

Local browser installation returned invalid archives, so real-engine checks run in GitHub CI. Local PHP 8.3 validates view syntax and renders the 20 seeded templates; application CI uses PHP 8.2 and the existing database/test environment. Final CI results are linked from the pull request. The existing suite recorded 219 PHP tests with 1592 assertions and the Chromium rendering metric above. Native browser tests verify actual form payloads and duplicate protection while a response is pending. The fixture isolates loader rendering; it is not a live-system TTI, memory, mobile-device or guaranteed-60-FPS measurement. Numerical CPU/memory/TTI improvements are not claimed without profiling the deployed system.

Transform/opacity changes make the small animated elements eligible for compositor work and avoid the previous per-frame width layout. Initial style calculation, painting and layer creation still cost CPU/memory. The lite/reduced policies remove continuous decorative motion and heavy blur while preserving operational content. Reference: [web.dev animation performance guidance](https://web.dev/articles/animations-guide).

Reproduce with `npm ci --prefix tests/frontend`, `npm test --prefix tests/frontend`, `npm run --prefix tests/frontend typecheck`, then `tests/frontend/node_modules/.bin/playwright install chromium` and `npm run --prefix tests/frontend test:browser` in a full checkout that contains the reviewed base commit.

## 4. Responsive layout and browser compatibility follow-up

The follow-up changes presentation CSS and cache versions. The existing controls, PHP conditions, form names, IDs, actions, event handlers, permissions and queue rules remain intact.

| Area | Before | After |
| --- | --- | --- |
| Guest navigation at 1201–1280 px | A generic authenticated-navigation rule hid Home, Schedules and Fares while the guest toggle was also hidden. | The 1280 px rule is scoped to `header#site-header`. Guest navigation retains its existing 900 px drawer breakpoint. |
| Editable auth branding | A long unbroken system name could extend beyond the phone viewport. | Brand text can shrink and wrap within the page; logos and text are retained. |
| Login/reset password on phones | The shared important input padding overrode the 56 px space for the visibility toggle. | Auth password fields explicitly retain 56 px right padding. Same inputs and toggle handlers. |
| Verification-code inputs | Shared mobile input padding consumed much of each narrow digit field. | Auth OTP fields retain their existing sizing and use zero horizontal padding. Same six fields, code assembly and verification rules. |
| Mobile operational tables | Unbounded labels and the default minimum width of flex values could overflow with long labels or unbroken names. Route values inherited desktop `nowrap`. | Row labels retain a 35% column and wrap within its bounds; values and nested route names can shrink and wrap without deleting data. Stacked text cells retain full-width labels. Desktop tables and selection visibility rules are retained. |

`tests/frontend/render-responsive-fixture.php` renders actual production views with deterministic helper and data values. No application helper or database is replaced in production. The browser harness keeps the actual authentication and navigation handlers, isolates realtime transport, and serves the same Bootstrap 5.3.0 CSS and JavaScript used by the authenticated templates from an isolated test dependency. Management fixtures include the real footer, inline page handlers and modal markup. External integrations and deployed backend behavior are outside these layout fixtures.

The workflow uses Chromium, Firefox and WebKit. Each engine checks guest navigation on 11 widths from 320 to 2560 px, all four authentication views on phone/tablet/desktop/landscape viewports, and seven guest/operational views with long names. It also checks low-hardware/reduced-motion policies, hidden clear controls, native field values, password toggles, OTP assembly, and before/after reproductions of the CSS defects. Screenshots and JSON results are stored in `interaction-rendering-{chromium,firefox,webkit}` artifacts.

These are engine/viewport compatibility checks with seeded templates, not a guarantee for every physical device, Safari release, embedded browser or installed extension. The CSS fixes introduce no layout animation, event listener, timer or production dependency. They do not change API payloads or add data requests. Browser support for optional motion hardware hints remains optional; missing hints keep full mode unless reduced motion is requested. Existing `vh` fallbacks remain alongside dynamic viewport units.

Reproduce in a full checkout with PHP 8.2 and the isolated npm tools: install `tests/frontend/node_modules/.bin/playwright install --with-deps chromium firefox webkit`, then run `TQ_BROWSER=chromium npm run --prefix tests/frontend test:responsive` (repeat for `firefox` and `webkit`). Before/after CSS fixtures use commit `2cfa15b98ed55fe20128eacfc66577b49cbfc0a5` by default; `TQ_RESPONSIVE_BASE_REF` can override it.

## 5. Continued management and lifecycle audit

| Area | Before | After | Behavior preserved |
| --- | --- | --- | --- |
| Login and recovery backgrounds | Four copies of slideshow code. Recovery-page timers rescheduled every six seconds while hidden; image/frame callbacks could outlive a branding update. | One typed controller, inlined by a shared view partial without an extra blocking request. Hidden, lite and reduced modes have no slideshow timer; pending frames and callbacks are canceled. BFCache resumes one controller. | The visible photo, six-second cadence in full mode, failed-image fallback and live branding updates. Login, recovery forms, OTP assembly and the resend countdown are untouched. |
| Recovery-card entrances | 700 ms animation plus 200 ms delay. | 180 ms transform/opacity entrance, or a 100 ms fade in lite mode. | Focusable fields, native validation and submit handlers are immediately available. |
| Shared motion stylesheet | Main pages and reusable headers loaded different version URLs for the same stylesheet. | One stylesheet link on each complete page. Standalone headers retain their stylesheet fallback. | Navigation markup, IDs, permissions and handlers. |
| Mobile management values | Anonymous flex text, raw terminal/message text and nested route values extended beyond their cells with long names. History route spans and route-management headings also kept their default flex minimum width. | Scoped mobile wrapping keeps vehicle/history names and routes, route-card headings, terminal details, announcement messages and departure destinations within their cards. Raw text cells use stacked labels. | Complete displayed values, filters, selection and actions; desktop sizing is retained. |
| Settings color fields | The default minimum width of a text input prevented it from shrinking beside the color picker on 320 px screens. | Color-field containers and text inputs may shrink within their existing flex row. | Input types, values, names, required rules, color-sync handlers and settings payloads. |

Coverage now includes users, vehicles, routes, terminals, announcements, departure rules, history, logs and settings, in addition to the original 11 views. Each engine checks 95 layout/viewport cases and eight additional modal/settings/selection cases. The browser checks inspect long values at phone, tablet, landscape and desktop widths. They also open and dismiss the real registration/type/bulk dialogs, check modal focus, retain native multipart fields and CSRF values, reject invalid registration without a POST, exercise keyboard settings tabs and verify bulk-selection payload/state without submitting a mutation. Tests wait for Bootstrap's `shown.bs.modal` event before testing dismissal. Phone screenshots and case-level JSON results are retained as CI artifacts.

For multi-image recovery backgrounds, the previous six-second hidden-tab timer implied ten wakeups per minute; the new controller has zero owned slideshow timers while hidden or in lite/reduced mode. This is a lifecycle count, not a measured CPU percentage. No permanent GPU layer or animation loop is added. Core API controllers, response fields, authentication, routes and database schema remain unchanged.

## 6. Static system-background regression

The shared system background originally defined its photos only inside slideshow keyframes. Lite-mode and reduced-motion rules disabled those keyframes, leaving guest, staff and admin pages with `background-image: none`. The CSS generator in `app/Common.php` and the live-branding rules in `public/js/ws-client.js` now also define the first photo outside the keyframes. That declaration intentionally has normal priority, so full-mode keyframes can continue changing photos on their existing six-second cadence. Static modes retain a photo without adding any timer, animation or request loop. Shared client cache versions are consistent across templates.

Live branding's later, more specific pseudo-element CSS could also restart animation after static mode was selected. Background-specific motion overrides now win regardless of stylesheet insertion order, including the CSS reduced-motion fallback without JavaScript.

`tests/frontend/backgrounds.browser.cjs` exercises the real PHP CSS generator and live-branding client in Chromium, Firefox and WebKit. It checks phone/desktop static backgrounds, reduced-motion changes, live photo updates, single/empty fallbacks and full-mode photo rotation. A before/after fixture renders the previous source from `64adc8c9c456bc7888f69db4bd8465bc96b76af0` and confirms the original blank background in both static modes. Image decoding, computed visibility and screenshots are included. No authentication, validation, queue state, transport, branding payload or setting is changed.

## 7. Overlapping pending-button text

The guest theme sets both `color` and `-webkit-text-fill-color` with high-specificity `!important` rules. The loader's lower-specificity transparent-color rule could not override them, so a raw-text button such as TRACK STATUS still painted its original label underneath Searching. Previous browser fixtures omitted the generated theme CSS and did not catch that collision.

| Before | After | Behavior preserved |
| --- | --- | --- |
| The original guest button label and the loading label were both visible. WebKit text fill could keep the original text visible even if normal color was transparent. | While feedback is visible, the loader temporarily makes both text-paint properties transparent with inline priority. The feedback retains its visible color. | Original text nodes, icons, listeners, dimensions, focus and the native successful submitter remain intact. |
| Rendering checks used neutral fixture styles. | Pending-label fixtures render actual guest, login and settings views with the real generated theme CSS. Before/after checks reproduce the original overlap. | Authentication, validation, form actions, payloads and event handlers are unchanged. |

The loader restores each original inline value and its priority when feedback ends; it does not rewrite button HTML or disable the native submitter. The existing immediate request path, pending ownership, repeat-submission guard, delay and cleanup remain unchanged. The six loader references use one new cache version so deployed pages request the corrected script.

`tests/frontend/pending-buttons.browser.cjs` checks 18 combinations of production view, phone/desktop width and full/lite/reduced mode per engine. It checks single-label paint, exact size retention, node/listener identity, focus and text-style restoration; it also verifies native input submit values and that the first keyboard action sends the unchanged payload before feedback, rejects repeated activation and releases feedback after an HTTP error. Screenshots and JSON records are saved with the existing CI artifacts. Run `TQ_BROWSER=chromium npm run --prefix tests/frontend test:pending` in a full checkout; repeat for Firefox and WebKit. The default before/after source is `64adc8c9c456bc7888f69db4bd8465bc96b76af0`.

The fix adds two temporary text-paint properties per active button, with no added animation loop, timer, listener, request or permanent GPU layer. Text paint changes once when feedback starts and once when it ends; it is not animated and does not change layout dimensions. Static background modes retain a decoded photo without slideshow work. No CPU percentage, memory reduction or universal device frame rate is claimed without a physical-device benchmark.
