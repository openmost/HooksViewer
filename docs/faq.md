## FAQ

### How do I install the plugin?

It is published on the official Matomo Marketplace, like any other
plugin:

- Open the administration panel.
- Go to *Marketplace → Plugins*.
- Search for **HooksViewer**, install it, then activate it.

### Why "never install in production"?

The plugin renders internal hook arguments into the page (database
configuration, visitor IPs, request parameters, etc.) and writes a
log file on every request. Both are useful while debugging and
unacceptable in production.

### Where do I see the hooks?

Two places, at the same time:

1. **Inline in the DOM**, as `<details><summary>` blocks placed exactly
   where the event was dispatched. Works on regular admin pages and on
   widget AJAX content (`format=html`).
2. **In the log file** at `tmp/logs/hooksviewer.log`. This catches
   every hook from every request — including JSON API calls, tracker
   hits, and CLI commands, where the plugin cannot safely inject
   markup. Tail it with `tail -f tmp/logs/hooksviewer.log`.

### I activated the plugin but my CSS still looks unstyled.

Matomo caches the merged stylesheet bundle on disk. The plugin clears
that cache on activation. If you somehow get out of sync, deactivate
the plugin and reactivate it — the next request will rebuild the
bundle.

### How does the plugin keep up with new Matomo events?

The list of subscribed hooks is **not hand-maintained**. The plugin
scans `core/` and `plugins/` for `Piwik::postEvent('…')` calls and
caches the discovered list under `tmp/cache/`. The cache is rebuilt
whenever the source tree changes, so new events appear automatically
the next time the cache is invalidated.

### Does the plugin break Matomo's API or tracker?

No. Inline output is **only** emitted for HTML responses (regular
pages and widget AJAX with `format=html`). API JSON, CSV exports,
XML, image responses, and tracker hits are left untouched. Use the
log file to observe hooks fired during those requests.

### Some hooks I expected to see are missing.

A few things to check:

- Is the event a wildcard (e.g. `Controller.$module.$action`)? Those
  are skipped because they have no fixed name to subscribe to.
- Did the event fire at all on this request? Check
  `tmp/logs/hooksviewer.log` — if it's not there either, the code
  path was not reached.
- Was the hook fired during a JSON API call? Inline rendering is
  intentionally disabled for those; check the log file.

### Is the plugin active for everyone in my Matomo instance?

Yes. While it is activated, every visitor and every admin user sees
the inline output and the log file grows. Deactivate it as soon as
you are done debugging.

### How can I contribute?

Open an issue or a pull request on
<https://github.com/openmost/HooksViewer>.

### How long will it be maintained?

As long as I keep using Matomo across projects, which is the
foreseeable future. I'm the first user of this plugin — if it breaks
on a Matomo upgrade I'll see it before you do.
