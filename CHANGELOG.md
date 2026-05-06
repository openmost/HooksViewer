## Changelog

### v2.0.0

Major rewrite. The plugin now covers (almost) every event Matomo dispatches
and renders them inline at their dispatch point.

**Hook discovery**

- New `HookCatalog` scans `core/` and `plugins/` for every
  `Piwik::postEvent('…')` call site. Same-file `self::FOO_EVENT`
  constants are resolved automatically.
- Result is cached in `tmp/cache/hooksviewer-catalog.php` and rebuilt
  whenever the source tree changes (mtime-based signature, 5 min TTL).
- ~258 events discovered out of the box on a clean Matomo 5 install,
  vs. ~210 in the previous hand-maintained list.

**Rendering**

- Events are rendered **at the moment they fire**, inline in the DOM,
  as `<details><summary>HookName</summary><pre><code>args…</code></pre></details>`.
- Args are pretty-printed across multiple indented lines (depth-limited,
  truncated at 30 entries per level / 240 chars per string), so deep
  arrays and objects stay readable.
- Args panel is locked to a dark background with white text so it
  remains legible regardless of the active Matomo theme.

**Safety**

- Inline emission is suppressed for JSON, XML, CSV, image, and tracker
  responses — Matomo's API and `matomo.php` payloads stay byte-exact.
- Widget AJAX requests (`format=html`) keep their inline output, so
  hooks like `ViewDataTable.filterViewDataTable`,
  `Visualization.beforeRender`, `Metrics.isLowerValueBetter`, and
  `Widget.filterWidgets` are visible inside each widget as it loads.
- Every fired hook (HTML or not) is also appended to
  `tmp/logs/hooksviewer.log` with timestamp, request id, and args.
  Watch with `tail -f`.
- Activation/deactivation now flushes Matomo's merged-asset bundle so
  the plugin's stylesheet is picked up immediately.

**Internals**

- One generic `__call()` dispatcher replaces 200+ hand-rolled stub
  methods. The whole plugin is ~340 lines of PHP.
- Compatible with Matomo 5.x.

### v1.0.0

Initial plugin commit.
