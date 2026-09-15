# Matomo HooksViewer Plugin

## Description

> **Never install this plugin on a production instance.**
> It is a development tool: it exposes internal hook arguments and
> writes a log file on every request.

### What it does

HooksViewer subscribes to **every event Matomo dispatches** and shows you,
for each request, which hooks fire, in what order, and with what
arguments. It is the fastest way to find the right event to listen to
when you build a Matomo plugin.

Two outputs run side by side:

1. **Inline panel** in the Matomo UI, for super users. Every HTML
   response gets a collapsible *HooksViewer* panel listing the hooks
   fired while it was built: at the top of full pages, and inside each
   widget or AJAX HTML fragment. Open a hook to see a pretty-printed,
   multi-line dump of its arguments.
2. **Log file** at `tmp/logs/hooksviewer.log`. Every hook from every
   request, including API calls, tracker hits and console commands, is
   appended with a timestamp, a request id and the arguments. Watch it
   with `tail -f tmp/logs/hooksviewer.log`.

### Safe for Matomo's responses

The panel is written once the response is complete, and only when that
response really is HTML. JSON controllers, API calls, graph data,
exports, images and tracker hits stay byte-exact, and pages keep their
doctype, so the dashboard, widgets and third-party clients keep working
while you explore.

### How the hook list stays current

The list of subscribed events is **discovered automatically** by
scanning `core/` and `plugins/` for `Piwik::postEvent('…')` call sites
the first time the plugin runs, and whenever the source tree changes.
The result is cached in `tmp/cache/hooksviewer-catalog.php`.

You do not have to update the plugin when Matomo or a third-party plugin
introduces new events: they show up the next time the cache is rebuilt.

### Install

1. Open the Marketplace in your Matomo admin (as a super user).
2. Search for **HooksViewer**.
3. Install, then activate.

### Use

1. Activate the plugin while you are exploring or debugging.
2. Browse the page or trigger the workflow you care about.
3. Expand the *HooksViewer* panel, or `tail -f` the log.
4. **Deactivate the plugin when you are done.**

### Requirements

- Matomo 6.x
- PHP 8.1 or newer
- MySQL 8.0+ or MariaDB 10.6+

For Matomo 5, use HooksViewer 2.x.

### Author

Built by [Openmost](https://openmost.com/matomo/extensions/hooks-viewer).
Issues and pull requests welcome at <https://github.com/openmost/HooksViewer>.

### License

GPL v3 or later.
