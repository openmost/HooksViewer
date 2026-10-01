# HooksViewer

See every event hook Matomo dispatches, in which order and with which arguments, to find the right hook when you build a Matomo plugin.

> **Never install this plugin on a production instance.** It is a development tool: it shows internal hook arguments (configuration values, visitor data, request parameters) and writes a log file on every request.

## Features

- **Every event, discovered automatically**: the plugin scans `core/` and `plugins/` for `Piwik::postEvent()` call sites and subscribes to every hook it finds, including the hooks of third-party plugins. The list is cached in `tmp/cache/hooksviewer-catalog.php` and rebuilt when the source tree changes.
- **Inline panel**, for super users: every HTML response gets a collapsible *HooksViewer* panel with the hooks fired while it was built, in order. Full pages show it at the top, each widget and AJAX HTML fragment shows its own. Open a hook to read a pretty-printed, multi-line dump of its arguments.
- **Log file** at `tmp/logs/hooksviewer.log`: every hook of every request, including API calls, tracker hits and console commands, with a timestamp, a request id and the arguments. Rotated above 10 MB.
- **Safe for Matomo responses**: the panel is written once the response is complete, and only into HTML responses, never inside a `<script>` block. API calls, exports, images, redirects and tracker hits stay byte-exact. Use the log file to observe their hooks.
- **Theme-aware**: the panel and the argument dumps follow the light or dark Matomo theme.

The search field and the hook catalog page are only available in HooksViewer 6.x, for Matomo 6.

## Requirements

- Matomo 5.10.0 or higher (`>=5.10.0,<6.0.0-b1`)

For Matomo 6, use HooksViewer 6.x.

## Installation / Configuration

1. Install the plugin from the Matomo Marketplace (**Administration > Marketplace**), on a development instance only, and activate it.
2. Browse the page or trigger the workflow you care about, then expand the *HooksViewer* panel, or run `tail -f tmp/logs/hooksviewer.log`.
3. **Deactivate the plugin when you are done.**

There is no setting.

## Privacy and data

- The panel is only visible to super users, but the log file records the hooks of every request, including those of other users, with their arguments.
- Nothing is sent outside your server.
- The log file and the catalog cache stay in Matomo's `tmp/` directory. The log file is rotated to `hooksviewer.log.1` above 10 MB.

## Need help with Matomo?

Openmost is an official Matomo Implementation Partner. If you would rather hand over the build, we develop [custom Matomo plugins](https://openmost.com/matomo/services/plugin-development?utm_source=matomo_marketplace&utm_medium=referral&utm_campaign=services&utm_content=hooksviewer) from a written spec, tested on the Matomo versions you run, published on the Marketplace or delivered privately.

## Support

- Homepage: <https://openmost.com/matomo/extensions/hooks-viewer>
- Email: [ronan@openmost.com](mailto:ronan@openmost.com)
- Source code and issues: <https://github.com/openmost/HooksViewer>

## Screenshots

See the `screenshots/` folder: the inline panel with the arguments of a hook.

## License

GPL v3 or later
