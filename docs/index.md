## Documentation

HooksViewer is a development tool that subscribes to every Matomo event
and surfaces them, in real time, while you are browsing the admin or
exercising the API.

### Two outputs

**Inline DOM** — On HTML pages and on widget AJAX requests
(`format=html`), each fired event is rendered as a `<details><summary>`
block at the exact place in the response stream where it was dispatched.
Open the summary to see a clean, indented dump of the hook arguments.

**Log file** — `tmp/logs/hooksviewer.log` receives one line per fired
event from **every** request, including JSON API calls, tracker hits,
and CLI commands. Each line carries a timestamp, a short request id so
you can correlate concurrent requests, an event index, the hook name,
and a compact view of the args.

```
tail -f tmp/logs/hooksviewer.log
```

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

Some Matomo responses cannot tolerate any extra bytes:

- `module=API` (JSON / XML / CSV / TSV / RSS)
- `matomo.php` and `piwik.php` (tracker hits and image responses)
- CLI commands

For these, HooksViewer **does not inject anything** into the response.
Use the log file (`tmp/logs/hooksviewer.log`) to observe their hooks.

For HTML pages and widget AJAX (`format=html`), output is rendered
inline as `<details>` elements styled to stay readable on light and
dark themes alike.

### Useful references

- Event guide: <https://developer.matomo.org/guides/events>
- Core event reference: <https://developer.matomo.org/api-reference/events>

### Reminder

This plugin exposes internal arguments (including configuration values
and visitor data) and writes a log file on every request. **Never
install it on a production instance.**
