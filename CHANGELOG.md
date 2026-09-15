## Changelog

### v6.1.0

**Search in the panel**

- The HooksViewer panel is now a Vue component with a search field:
  filter the hooks by name, or inside their arguments.
- Each hook links to its entry in the new hook catalog.
- The list is only rendered once the panel is opened, so pages with
  many widgets stay fast.

**Hook catalog**

- New *Administration → Diagnostic → Hooks Viewer* page, for super
  users: every known hook with its description and parameters (from the
  source docblock), the file and line where it is posted, the plugins
  listening to it, a `registerEvents()` snippet and a link to the
  developer reference.
- Search, filter by category, show only hooks with listeners, rescan
  the source code on demand.
- Hooks that are listened to but built at runtime (like
  `Controller.CoreHome.index`) are listed as dynamic.

**Other**

- English and French translations.

### v6.0.0

Matomo 6 compatibility, and a rebuilt inline output that no longer
breaks Matomo's pages and responses.

**Compatibility**

- Requires Matomo `>=6.0.0-b1,<7.0.0-b1` and PHP 8.1+ (MySQL 8.0+ or
  MariaDB 10.6+, as required by Matomo 6).
- No more deprecated `E_USER_ERROR` on PHP 8.4+.
- ~270 events discovered out of the box on Matomo 6.

**Inline panel**

- Hooks are collected during the request and written once the response
  is complete, in one collapsible *HooksViewer* panel: right after
  `<body>` on full pages, at the top of widgets and AJAX HTML fragments.
- The panel summary shows the number of hooks and the request id used
  in the log file.
- The panel is only visible to super users. The log file still records
  the hooks of every request.
- Panel styles follow the Matomo theme (light and dark).

**Fixes**

- Pages are no longer rendered in quirks mode (markup was printed
  before the doctype).
- JSON controller actions (`#[JsonResponse]`, e.g.
  `Dashboard.getAllDashboards`) and graph data (jqPlot `data-data`) are
  no longer corrupted by hook markup.
- No more "Session must be started before any output" errors: headers,
  cookies and redirects are sent normally.
- Multi-byte strings are truncated without breaking characters, enums
  are shown as `Class::CASE`.

**Log file**

- `tmp/logs/hooksviewer.log` is rotated to `hooksviewer.log.1` above
  10 MB.
- The log and the hook catalog cache honour a customised Matomo
  `path.tmp`.

**Other**

- Homepage and support addresses moved to openmost.com.

### v2.0.1

update: README.md

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
  responses: Matomo's API and `matomo.php` payloads stay byte-exact.
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
