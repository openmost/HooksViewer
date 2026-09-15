## FAQ

### How do I install the plugin?

It is published on the official Matomo Marketplace, like any other
plugin:

- Open the administration panel as a super user.
- Go to *Marketplace*.
- Search for **HooksViewer**, install it, then activate it.

### Which Matomo versions are supported?

HooksViewer 6.x runs on Matomo 6 (PHP 8.1+, MySQL 8.0+ or MariaDB
10.6+). For Matomo 5, use HooksViewer 2.x.

### Why "never install in production"?

The plugin shows internal hook arguments (database configuration,
visitor IPs, request parameters, etc.) and writes a log file on every
request. Both are useful while debugging and unacceptable in
production.

### Where do I see the hooks?

Two places, at the same time:

1. **In the HooksViewer panel**, at the top of each page and inside each
   widget. Expand it to list the hooks, expand a hook to read its
   arguments.
2. **In the log file** at `tmp/logs/hooksviewer.log`. This catches
   every hook from every request, including JSON API calls, tracker
   hits and console commands, where the plugin never adds any markup.
   Tail it with `tail -f tmp/logs/hooksviewer.log`.

### Who can see the panel?

Only super users. Everyone else browses Matomo normally, but the hooks
of their requests are still written to the log file.

### Why is the panel at the top of the page and not where the hook fired?

Printing markup at the exact moment a hook fires broke Matomo: it ended
up inside HTML attributes, graph data and JSON responses, and before
the page doctype. The panel is now written once the response is
complete. The hooks are still listed in the exact order they fired, and
each widget gets its own panel.

### I activated the plugin but the panel looks unstyled.

Matomo caches the merged stylesheet bundle on disk. The plugin clears
that cache on activation. If you somehow get out of sync, deactivate
the plugin and reactivate it: the next request rebuilds the bundle.

### How does the plugin keep up with new Matomo events?

The list of subscribed hooks is **not hand-maintained**. The plugin
scans `core/` and `plugins/` for `Piwik::postEvent('…')` calls and
caches the discovered list under `tmp/cache/`. The cache is rebuilt
whenever the source tree changes, so new events appear automatically.

### Does the plugin break Matomo's API or tracker?

No. The panel is **only** added to HTML responses. API responses, JSON
controller actions, CSV exports, XML, images, redirects and tracker hits
are left untouched. Use the log file to observe hooks fired during
those requests.

### Some hooks I expected to see are missing.

A few things to check:

- Is the event a wildcard (e.g. `Controller.$module.$action`)? Those
  are skipped because they have no fixed name to subscribe to.
- Did the event fire at all on this request? Check
  `tmp/logs/hooksviewer.log`: if it's not there either, the code
  path was not reached.
- Was the hook fired during a JSON or API request? No panel is added to
  those, check the log file.
- Does the panel say "more in tmp/logs/hooksviewer.log"? A single
  response keeps at most 5,000 hooks in its panel, the rest is in the
  log.

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
