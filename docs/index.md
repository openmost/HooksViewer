## Documentation

HooksViewer is a development tool that subscribes to every Matomo event
and surfaces them while you are browsing the Matomo UI or exercising the
API.

### Two outputs

**Inline panel**: every HTML response (full pages, widgets, AJAX HTML
fragments) gets a collapsible *HooksViewer* panel, visible to super
users only. Its summary shows how many hooks fired and the request id.
Expand it to list the hooks in the order they fired, then expand a hook
to see a clean, indented dump of its arguments.

- Full pages show the panel at the very top of the page.
- Each dashboard widget shows its own panel, so hooks like
  `ViewDataTable.filterViewDataTable`, `Visualization.beforeRender` or
  `Metrics.isLowerValueBetter` are visible for the widget that
  triggered them.

**Log file**: `tmp/logs/hooksviewer.log` receives one line per fired
event from **every** request, including JSON API calls, tracker hits,
console commands, and requests made by users who cannot see the panel.
Each line carries a timestamp, a short request id (the same one as in
the panel summary), an event index, the hook name, and a compact view of
the arguments.

```
tail -f tmp/logs/hooksviewer.log
```

The log is rotated to `hooksviewer.log.1` once it grows above 10 MB.

### Where the hook list comes from

The plugin does not ship a hand-maintained list. On the first request
after activation it scans `core/` and `plugins/` for every
`Piwik::postEvent('…')` call site, resolves same-file constant
references, and persists the result to
`tmp/cache/hooksviewer-catalog.php`. The cache is invalidated whenever
the source tree changes, so new events introduced by a Matomo upgrade
or by a third-party plugin appear automatically.

Wildcard event names (those containing PHP variables or `sprintf`
placeholders, e.g. `Controller.$module.$action`) are skipped because
they cannot be subscribed to as a single static name.

### Response safety

Nothing is printed while a hook fires. Matomo renders templates and
widgets into nested output buffers, so markup printed at that moment
would end up inside HTML attributes, graph data or JSON bodies.

Instead, hooks are collected during the request and the panel is
written once the response is complete:

- only when the final `Content-Type` is HTML;
- right after `<body>` for full pages, so the doctype stays first;
- at the top of HTML fragments.

These responses are never modified:

- `module=API` (JSON / XML / CSV / TSV / RSS)
- controller actions returning JSON (`#[JsonResponse]`, e.g.
  `Dashboard.getAllDashboards`)
- `matomo.php` and `piwik.php` (tracker hits and image responses)
- exports, images, redirects and plain text responses
- console commands

Use the log file to observe their hooks.

### Useful references

- Event guide: <https://developer.matomo.org/guides/events>
- Core event reference: <https://developer.matomo.org/api-reference/events>

### Reminder

This plugin exposes internal arguments (including configuration values
and visitor data) and writes a log file on every request. **Never
install it on a production instance.**
