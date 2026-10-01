## FAQ

### How do I install the plugin?

It is published on the official Matomo Marketplace, like any other
plugin:

- Open the administration panel.
- Go to *Marketplace*.
- Search for **HooksViewer**, install it, then activate it.

### Which Matomo versions are supported?

HooksViewer 5.x runs on Matomo 5.10.0 or higher. For Matomo 6, use
HooksViewer 6.x, which adds a search field in the panel and a hook
catalog page.

### Why "never install in production"?

The plugin renders internal hook arguments into the page (database
configuration, visitor IPs, request parameters, etc.) and writes a
log file on every request. Both are useful while debugging and
unacceptable in production.

### Where do I see the hooks?

Two places, at the same time:

1. **In the HooksViewer panel**, for super users: a collapsible panel at
   the top of each HTML page, and at the top of each widget AJAX
   fragment (`format=html`), listing the hooks fired while it was built,
   in order, each one with its arguments.
2. **In the log file** at `tmp/logs/hooksviewer.log`. This catches
   every hook from every request, including JSON API calls, tracker
   hits, and CLI commands, where the plugin cannot safely inject
   markup. Tail it with `tail -f tmp/logs/hooksviewer.log`.

### I activated the plugin but my CSS still looks unstyled.

Matomo caches the merged stylesheet bundle on disk. The plugin clears
that cache on activation. If you somehow get out of sync, deactivate
the plugin and reactivate it: the next request will rebuild the
bundle.

### How does the plugin keep up with new Matomo events?

The list of subscribed hooks is **not hand-maintained**. The plugin
scans `core/` and `plugins/` for `Piwik::postEvent('…')` calls and
caches the discovered list under `tmp/cache/`. The cache is rebuilt
whenever the source tree changes, so new events appear automatically
the next time the cache is invalidated.

### Does the plugin break Matomo's API or tracker?

No. The panel is written once the response is complete, and **only**
into HTML responses (regular pages and widget AJAX with `format=html`),
never inside a `<script>` block. API JSON, CSV exports,
XML, image responses, and tracker hits are left untouched. Use the
log file to observe hooks fired during those requests.

### Some hooks I expected to see are missing.

A few things to check:

- Is the event a wildcard (e.g. `Controller.$module.$action`)? Those
  are skipped because they have no fixed name to subscribe to.
- Did the event fire at all on this request? Check
  `tmp/logs/hooksviewer.log`: if it's not there either, the code
  path was not reached.
- Was the hook fired during a JSON API call? The panel is
  intentionally disabled for those; check the log file.

### Is the plugin active for everyone in my Matomo instance?

The panel is only shown to super users. The log file, however,
records the hooks of every request from every user while the plugin
is activated. Deactivate it as soon as
you are done debugging.

### Will the log file fill my disk?

No. It is rotated to `hooksviewer.log.1` once it grows above 10 MB, so
at most about 20 MB are kept.

### How can I contribute?

Open an issue or a pull request on
<https://github.com/openmost/HooksViewer>.

### How long will it be maintained?

As long as I keep using Matomo across projects, which is the
foreseeable future. I'm the first user of this plugin: if it breaks
on a Matomo upgrade I'll see it before you do.
