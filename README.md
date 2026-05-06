# Matomo HooksViewer Plugin

## Description

> **Never install this plugin on a production instance.**
> It is a development tool. Every page exposes internal arguments,
> appends to a log file, and adds DOM elements to Matomo's UI.

### What it does

HooksViewer subscribes to **every event Matomo dispatches** and shows you,
in real time, which hooks fire — where they fire, in what order, and with
what arguments.

Two outputs run side by side:

1. **Inline panel** in the Matomo UI: each event is rendered as a
   `<details><summary>` block at the exact spot in the DOM where it was
   dispatched. Open the summary to see a pretty-printed, multi-line dump
   of the hook arguments. Widget XHRs (`format=html`) also surface their
   internal hooks like `ViewDataTable.filterViewDataTable`,
   `Visualization.beforeRender`, `Metrics.isLowerValueBetter`, etc.
2. **Log file** at `tmp/logs/hooksviewer.log`. Every hook from every
   request — including JSON API calls, tracker hits, and CLI commands —
   is appended with timestamp, request id, and arguments. Watch with
   `tail -f tmp/logs/hooksviewer.log`.

The plugin **never injects markup into JSON, XML, CSV, image, or tracker
responses**, so Matomo's API stays valid and the dashboard, widgets, and
third-party clients keep working.

### How the hook list stays current

The list of subscribed events is **discovered automatically** by scanning
`core/` and `plugins/` for `Piwik::postEvent('…')` call sites the first
time the plugin runs (and whenever a source file is added/removed/edited).
The result is cached in `tmp/cache/hooksviewer-catalog.php`.

You do not have to update the plugin when Matomo introduces new events —
they show up the next time the cache is rebuilt.

### Install

This plugin is published on the official Matomo Marketplace.

1. Open the Marketplace in your Matomo admin.
2. Search for **HooksViewer**.
3. Install, then activate.

### Use

1. Activate the plugin while you are exploring or debugging.
2. Browse the page or trigger the workflow you care about.
3. Read the inline `<details>` blocks, or `tail -f` the log.
4. **Deactivate the plugin when you are done.**

### Requirements

- Matomo 5.x
- PHP 7.4+

### Author

Built by [Openmost](https://openmost.io). Issues and pull requests welcome
at <https://github.com/openmost/HooksViewer>.

### License

GPL v3 or later.
