# HooksViewer

See every event hook Matomo dispatches, in which order and with which arguments, to find the right hook when you build a Matomo plugin.

> **Never install this plugin on a production instance.** It is a development tool: it shows internal hook arguments (configuration values, visitor data, request parameters) and writes a log file on every request.

## Features

- **Every event, discovered automatically**: the plugin scans `core/` and `plugins/` for `Piwik::postEvent()` call sites and subscribes to every hook it finds, including the hooks of third-party plugins. The list is cached in `tmp/cache/hooksviewer-catalog.php` and rebuilt when the source tree changes.
- **Inline panel**, for super users: every HTML response gets a collapsible *HooksViewer* panel with the hooks fired while it was built, in order. Full pages show it at the top, each widget and AJAX HTML fragment shows its own. Open a hook to read a pretty-printed, multi-line dump of its arguments.
- **Search in the panel**: filter the hooks by name, or tick *Search in arguments* to find the hook that receives a given report or setting.
- **Hook catalog** (Administration > Diagnostic > Hooks Viewer): every known hook with its description and parameters from the source docblock, the file and line where it is posted, the plugins listening to it, a ready-to-paste `registerEvents()` snippet and a link to the developer reference. Search it, filter it by category, show only the hooks with listeners, or rescan the source code on demand. Hooks whose name is built at runtime are listed as dynamic.
- **Log file** at `tmp/logs/hooksviewer.log`: every hook of every request, including API calls, tracker hits and console commands, with a timestamp, a request id and the arguments. Rotated above 10 MB.
- **Safe for Matomo responses**: the panel is written once the response is complete, and only into HTML responses. API calls, JSON controller actions, exports, images, redirects and tracker hits stay byte-exact, and pages keep their doctype.
- **Theme-aware**: the panel and the argument dumps follow the light or dark Matomo theme.
- **12 languages**: English, Arabic, Chinese (Simplified), Chinese (Traditional), Dutch, French, German, Italian, Japanese, Polish, Portuguese and Spanish.

## Requirements

- Matomo 6 (`>=6.0.0-b1,<7.0.0-b1`)
- PHP 8.1 or higher

For Matomo 5, use HooksViewer 5.x.

## Installation / Configuration

1. Install the plugin from the Matomo Marketplace (**Administration > Marketplace**), on a development instance only, and activate it.
2. Browse the page or trigger the workflow you care about, then expand the *HooksViewer* panel and search it, or run `tail -f tmp/logs/hooksviewer.log`.
3. Click *Open in the hook catalog* on a hook, or go to **Administration > Diagnostic > Hooks Viewer**, to read its documentation and see who listens to it.
4. **Deactivate the plugin when you are done.**

There is no setting.

## Privacy and data

- The panel and the hook catalog are only visible to super users, but the log file records the hooks of every request, including those of other users, with their arguments.
- Nothing is sent outside your server.
- The log file and the catalog cache stay in Matomo's `tmp/` directory.

## Need help with Matomo?

Openmost is an official Matomo Implementation Partner. If you would rather hand over the build, we develop [custom Matomo plugins](https://openmost.com/matomo/services/plugin-development?utm_source=matomo_marketplace&utm_medium=referral&utm_campaign=services&utm_content=hooksviewer) from a written spec, tested on the Matomo versions you run, published on the Marketplace or delivered privately.

## Support

- Homepage: <https://openmost.com/matomo/extensions/hooks-viewer>
- Email: [ronan@openmost.com](mailto:ronan@openmost.com)
- Source code and issues: <https://github.com/openmost/HooksViewer>

## Screenshots

See the `screenshots/` folder: the inline panel with the arguments of a hook, and the hook catalog with its search and filters and the `registerEvents()` snippet of a hook.

## License

GPL v3 or later
