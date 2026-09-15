<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\HooksViewer;

use Piwik\Piwik;
use Piwik\Plugin\Manager;

class HooksViewer extends \Piwik\Plugin
{
    /** Size above which tmp/logs/hooksviewer.log is rotated to hooksviewer.log.1. */
    private const LOG_MAX_BYTES = 10485760;

    /** Maximum number of events kept in memory for the inline panel (the log keeps them all). */
    private const PANEL_MAX_EVENTS = 5000;

    /** Maximum length of the arguments dump kept in memory for each event of the panel. */
    private const PANEL_MAX_ARGS_LENGTH = 20000;

    /** Maximum nested depth to walk before bailing out with "…". */
    private const DESCRIBE_MAX_DEPTH = 4;

    /** Indentation used between nested levels. Two spaces keeps lines short. */
    private const DESCRIBE_INDENT = '  ';

    /** Maximum number of array entries / object props to emit per level. */
    private const DESCRIBE_MAX_ENTRIES = 30;

    /** Maximum displayed length of a string value (longer values are truncated). */
    private const DESCRIBE_MAX_STRING = 240;

    /** Translations used by the Vue components (HooksPanel, HooksCatalog). */
    private const CLIENT_SIDE_TRANSLATIONS = [
        'HooksViewer_HooksViewer',
        'HooksViewer_CatalogIntro',
        'HooksViewer_ProductionWarning',
        'HooksViewer_LogFile',
        'HooksViewer_SearchPlaceholder',
        'HooksViewer_SearchInArguments',
        'HooksViewer_AllCategories',
        'HooksViewer_OnlyWithListeners',
        'HooksViewer_Showing',
        'HooksViewer_HooksCount',
        'HooksViewer_OneHook',
        'HooksViewer_ListenedCount',
        'HooksViewer_RequestId',
        'HooksViewer_MoreInLog',
        'HooksViewer_NoMatch',
        'HooksViewer_Hook',
        'HooksViewer_Description',
        'HooksViewer_Listeners',
        'HooksViewer_PostedIn',
        'HooksViewer_Parameters',
        'HooksViewer_NoDescription',
        'HooksViewer_Dynamic',
        'HooksViewer_DynamicHelp',
        'HooksViewer_ListenExample',
        'HooksViewer_DeveloperReference',
        'HooksViewer_OpenInCatalog',
        'HooksViewer_Rescan',
        'HooksViewer_Rescanned',
    ];

    /** Cached decision: can this request carry an HTML panel at all? null = not yet decided. */
    private static $htmlRequest = null;

    /** Running count of events emitted in this request (for ordering hints). */
    private static $eventIndex = 0;

    /** In-memory copy of the discovered hook list, populated lazily. */
    private static $hooks = null;

    /** Resolved log file path (false if logs cannot be written). */
    private static $logPath = null;

    /** Short identifier for the current request, so log lines can be correlated. */
    private static $requestId = null;

    /** Whether the output buffer that injects the panel has been started. */
    private static $bufferStarted = false;

    /** Whether the current user may see the panel. Decided once the user is authenticated. */
    private static $canDisplay = false;

    /** Events captured for the panel, as [index, hook name, arguments dump]. */
    private static $events = [];

    /** Events that fired after PANEL_MAX_EVENTS was reached (still written to the log). */
    private static $droppedEvents = 0;

    /** Number of captured events already written into the response. */
    private static $renderedCount = 0;

    /** Kind of response being buffered: null (undecided), 'page', 'fragment' or 'none'. */
    private static $outputKind = null;

    /** Whether the panel has already been written into the response. */
    private static $panelInjected = false;

    public function __construct($pluginName = false)
    {
        parent::__construct($pluginName);

        $this->startOutputBuffer();
    }

    /**
     * Subscribe to every discovered hook.
     *
     * The list is built dynamically by HookCatalog (which scans core/ and
     * plugins/ for postEvent('…') call sites), so newly added events show up
     * automatically, with no hand-maintained list to drift behind core.
     */
    public function registerEvents()
    {
        $map = [];
        foreach ($this->getHooks() as $hookName) {
            $map[$hookName] = self::hookToMethod($hookName);
        }

        // Needed by the plugin itself, whatever the scan found.
        foreach (['AssetManager.getStylesheetFiles', 'Translate.getClientSideTranslationKeys'] as $hookName) {
            $map[$hookName] = self::hookToMethod($hookName);
        }

        return $map;
    }

    /**
     * Plugin lifecycle: clear the merged-asset bundle so our stylesheet is
     * picked up the moment the plugin is activated, instead of being served
     * stale from tmp/assets/.
     */
    public function activate()
    {
        $this->resetAssetCache();
        (new HookCatalog())->invalidate();
    }

    public function install()
    {
        $this->resetAssetCache();
    }

    public function deactivate()
    {
        $this->resetAssetCache();
    }

    private function resetAssetCache(): void
    {
        try {
            \Piwik\AssetManager::getInstance()->removeMergedAssets();
        } catch (\Throwable $e) {
            // Cache reset is best-effort, failing here must never block activation.
        }
    }

    private function getHooks(): array
    {
        if (self::$hooks === null) {
            self::$hooks = (new HookCatalog())->getHooks();
        }
        return self::$hooks;
    }

    /**
     * Convert a hook name like "Foo.bar.baz" into a method name "hook_Foo_bar_baz".
     * Using a prefix + replaced delimiter avoids collisions and keeps __call dispatch trivial.
     */
    private static function hookToMethod(string $hookName): string
    {
        return 'hook_' . str_replace('.', '_', $hookName);
    }

    private function methodToHook(string $method): ?string
    {
        if (strncmp($method, 'hook_', 5) !== 0) {
            return null;
        }
        $candidate = str_replace('_', '.', substr($method, 5));
        return in_array($candidate, $this->getHooks(), true) ? $candidate : null;
    }

    /**
     * Generic dispatcher for every registered hook.
     *
     * Matomo's EventDispatcher uses call_user_func_array, which does not preserve
     * references unless the receiver declares them. Our generic receiver therefore
     * cannot mutate by-ref args, which is fine because we only observe.
     * Hooks needing real by-ref behavior are handled via dedicated methods below.
     */
    public function __call($name, $arguments)
    {
        $hookName = $this->methodToHook((string) $name);
        if ($hookName === null) {
            throw new \BadMethodCallException(sprintf('Call to undefined method %s::%s()', static::class, $name));
        }
        $this->captureHook($hookName, $arguments);
        return null;
    }

    /**
     * Explicit handler: registers the plugin's own stylesheet by reference.
     */
    public function hook_AssetManager_getStylesheetFiles(&$files)
    {
        $files[] = 'plugins/HooksViewer/stylesheets/style.less';
        $this->captureHook('AssetManager.getStylesheetFiles', [$files]);
    }

    /**
     * Explicit handler: exposes the translations of the Vue components by reference.
     */
    public function hook_Translate_getClientSideTranslationKeys(&$translationKeys)
    {
        foreach (self::CLIENT_SIDE_TRANSLATIONS as $translationKey) {
            $translationKeys[] = $translationKey;
        }
        $this->captureHook('Translate.getClientSideTranslationKeys', [$translationKeys]);
    }

    /**
     * Log every hook, and keep it in memory for the inline panel.
     *
     * Nothing is echoed while the hook fires: Matomo captures output in nested
     * buffers (Twig rendering, FrontController::fetchDispatch()), so markup
     * printed at that moment would end up inside attributes, graph data or JSON
     * bodies. The panel is written once the response is complete, see
     * renderInlinePanel().
     */
    private function captureHook(string $hookName, array $args): void
    {
        $index = ++self::$eventIndex;
        $argsText = $this->summariseArgs($args);

        $this->logEvent($hookName, $index, $argsText);

        if ($hookName === 'Platform.initialized') {
            // Fired right after authentication: the earliest point where access is known.
            self::$canDisplay = self::currentUserCanSeeHooks();
        }

        if (!self::$bufferStarted) {
            return;
        }

        if (count(self::$events) >= self::PANEL_MAX_EVENTS) {
            self::$droppedEvents++;
            return;
        }

        if (strlen($argsText) > self::PANEL_MAX_ARGS_LENGTH) {
            $argsText = mb_strcut($argsText, 0, self::PANEL_MAX_ARGS_LENGTH, 'UTF-8') . "\n… (truncated, see the log file)";
        }
        self::$events[] = [$index, $hookName, $argsText];
    }

    /**
     * The panel exposes internal arguments (configuration, visitor data, request
     * parameters), so only super users get to see it. Hooks of every request are
     * still written to the log file.
     */
    private static function currentUserCanSeeHooks(): bool
    {
        try {
            return Piwik::hasUserSuperUserAccess();
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Wrap the whole response in an output buffer so the panel can be injected
     * once the response is complete and its real Content-Type is known.
     *
     * Started from the constructor, which runs while plugins are loaded during
     * bootstrap, before any output and outside any nested buffer.
     */
    private function startOutputBuffer(): void
    {
        if (self::$bufferStarted || !self::isHtmlRequest()) {
            return;
        }

        try {
            // Plugins are also instantiated when listed (Plugins admin, Marketplace):
            // only buffer when this plugin is actually loaded for the request.
            if (!Manager::getInstance()->isPluginActivated($this->getPluginName())) {
                return;
            }
        } catch (\Throwable $e) {
            return;
        }

        self::$bufferStarted = ob_start([self::class, 'renderInlinePanel']);
    }

    /**
     * Output buffer callback: write the captured hooks into HTML responses.
     *
     * - Full pages get the panel right after the opening <body> tag, so the
     *   doctype stays first and the page does not fall back to quirks mode.
     * - HTML fragments (widgets and other AJAX HTML) get it prepended.
     * - Anything else (JSON controllers declaring #[JsonResponse], images,
     *   exports, redirects, plain text) is left byte-exact.
     * Hooks that fire after the panel was written (e.g. when the response is
     * flushed in several chunks) are appended at the end of the response.
     */
    public static function renderInlinePanel(string $buffer, int $phase): string
    {
        if (($phase & PHP_OUTPUT_HANDLER_CLEAN) !== 0 || !self::$canDisplay) {
            return $buffer;
        }

        $isFinal = ($phase & PHP_OUTPUT_HANDLER_FINAL) !== 0;

        if (self::$outputKind === null) {
            if (trim($buffer) === '' && !$isFinal) {
                return $buffer;
            }
            self::$outputKind = self::detectOutputKind($buffer);
        }

        if (self::$outputKind === 'none') {
            return $buffer;
        }

        if (!self::$panelInjected) {
            $position = self::$outputKind === 'fragment' ? 0 : self::findBodyContentStart($buffer);
            if ($position === null) {
                if (!$isFinal) {
                    return $buffer;
                }
                $position = strlen($buffer);
            }
            self::$panelInjected = true;
            return substr_replace($buffer, self::renderPendingEvents(), $position, 0);
        }

        if (!$isFinal) {
            return $buffer;
        }

        $panel = self::renderPendingEvents();
        if ($panel === '') {
            return $buffer;
        }
        $closingBody = strripos($buffer, '</body>');
        return substr_replace($buffer, $panel, $closingBody === false ? strlen($buffer) : $closingBody, 0);
    }

    private static function detectOutputKind(string $buffer): string
    {
        foreach (headers_list() as $header) {
            if (stripos($header, 'Content-Type:') !== 0) {
                continue;
            }
            $value = strtolower(trim(substr($header, 13)));
            if ($value !== '' && strpos($value, 'text/html') !== 0) {
                return 'none';
            }
        }

        $start = ltrim(substr($buffer, 0, 1024));
        if ($start === '') {
            return 'none';
        }
        if (preg_match('/^(?:<!doctype\b|<html\b)/i', $start)) {
            return 'page';
        }
        // JSON sent without a Content-Type, plain text, JavaScript: never touch.
        return $start[0] === '<' ? 'fragment' : 'none';
    }

    private static function findBodyContentStart(string $buffer): ?int
    {
        if (!preg_match('/<body\b[^>]*>/i', $buffer, $match, PREG_OFFSET_CAPTURE)) {
            return null;
        }
        return $match[0][1] + strlen($match[0][0]);
    }

    /**
     * Mount point of the HooksViewer.HooksPanel Vue component, which renders the
     * searchable list from the JSON props. Its content is only a fallback for
     * responses where Vue components are not compiled (error pages, some AJAX
     * HTML): the hook names, without their arguments.
     */
    private static function renderPendingEvents(): string
    {
        $events = array_slice(self::$events, self::$renderedCount);
        self::$renderedCount = count(self::$events);

        if ($events === []) {
            return '';
        }

        $droppedCount = self::$droppedEvents;
        self::$droppedEvents = 0;

        $jsonFlags = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE | JSON_PARTIAL_OUTPUT_ON_ERROR;
        $attribute = static function ($value) use ($jsonFlags): string {
            return htmlspecialchars((string) json_encode($value, $jsonFlags), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        };

        $names = '';
        foreach ($events as [$index, $hookName]) {
            $names .= '<li value="' . $index . '">' . htmlspecialchars($hookName, ENT_QUOTES) . '</li>';
        }

        $count = count($events);
        $summary = sprintf(
            '<span class="hv-panel-title">HooksViewer</span> %d hook%s, request %s',
            $count,
            $count > 1 ? 's' : '',
            htmlspecialchars(self::shortRequestId(), ENT_QUOTES)
        );
        if ($droppedCount > 0) {
            $summary .= sprintf(', %d more in tmp/logs/hooksviewer.log', $droppedCount);
        }

        return '<div class="hv-panel-root" vue-entry="HooksViewer.HooksPanel"'
            . ' events="' . $attribute($events) . '"'
            . ' request-id="' . $attribute(self::shortRequestId()) . '"'
            . ' dropped-count="' . $attribute($droppedCount) . '">'
            . '<details class="hv-panel"><summary class="hv-panel-summary">' . $summary . '</summary>'
            . '<ol class="hv-panel-fallback">' . $names . '</ol></details>'
            . '</div>';
    }

    /**
     * Append the event to tmp/logs/hooksviewer.log so devs can watch every
     * hook fire across every request, including JSON/tracker ones that can't
     * accept inline markers.
     */
    private function logEvent(string $hookName, int $index, string $argsText): void
    {
        if (self::$logPath === null) {
            self::$logPath = $this->resolveLogPath();
        }
        if (self::$logPath === false) {
            return;
        }

        $line = sprintf(
            "[%s] %s #%d %s :: %s\n",
            date('Y-m-d H:i:s'),
            self::shortRequestId(),
            $index,
            $hookName,
            str_replace(["\r\n", "\n", "\r"], ' | ', $argsText)
        );
        @file_put_contents(self::$logPath, $line, FILE_APPEND | LOCK_EX);
    }

    /**
     * @return string|false
     */
    private function resolveLogPath()
    {
        $dir = HookCatalog::tmpPath() . DIRECTORY_SEPARATOR . 'logs';
        if (!is_dir($dir) && !@mkdir($dir, 0755, true) && !is_dir($dir)) {
            return false;
        }

        $path = $dir . DIRECTORY_SEPARATOR . 'hooksviewer.log';
        // Every request appends hundreds of lines: keep the file from growing forever.
        if (is_file($path) && (int) @filesize($path) > self::LOG_MAX_BYTES) {
            @rename($path, $path . '.1');
        }
        return $path;
    }

    private static function shortRequestId(): string
    {
        if (self::$requestId === null) {
            self::$requestId = bin2hex(random_bytes(3));
        }
        return self::$requestId;
    }

    /**
     * Render args as a safe, readable string. Avoids print_r/var_export on huge
     * graphs (e.g. Container/Site objects) by truncating depth and length.
     */
    private function summariseArgs(array $args): string
    {
        $blocks = [];
        foreach ($args as $i => $arg) {
            $blocks[] = '#' . $i . ' = ' . $this->describe($arg, 0);
        }
        return implode("\n", $blocks);
    }

    /**
     * Pretty-print a value across multiple indented lines so deeply-nested
     * arrays / objects stay readable. Inspired by var_export but tuned for
     * the args panel: short scalars stay on one line, arrays and objects
     * always break across lines, output never explodes on huge structures.
     */
    private function describe($value, int $depth): string
    {
        if ($depth > self::DESCRIBE_MAX_DEPTH) {
            return '…';
        }

        if ($value === null) {
            return 'null';
        }
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }
        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }
        if (is_string($value)) {
            return $this->describeString($value);
        }
        if (is_array($value)) {
            return $this->describeArray($value, $depth);
        }
        if (is_object($value)) {
            return $this->describeObject($value, $depth);
        }
        if (is_resource($value)) {
            return 'resource(' . get_resource_type($value) . ')';
        }
        return gettype($value);
    }

    private function describeString(string $value): string
    {
        $len = strlen($value);
        if ($len > self::DESCRIBE_MAX_STRING) {
            // mb_strcut never splits a multi-byte character in half.
            $value = mb_strcut($value, 0, self::DESCRIBE_MAX_STRING, 'UTF-8') . '…';
        }
        // var_export gives us proper quoting + escaping for control chars.
        return 'string(' . $len . ') ' . var_export($value, true);
    }

    private function describeArray(array $value, int $depth): string
    {
        $count = count($value);
        if ($count === 0) {
            return 'array(0) []';
        }

        // Numeric arrays of scalars stay on a single line, easier to scan.
        if ($this->isFlatNumericList($value)) {
            $parts = [];
            foreach ($value as $v) {
                $parts[] = $this->describe($v, $depth + 1);
            }
            return 'array(' . $count . ') [' . implode(', ', $parts) . ']';
        }

        $indent      = str_repeat(self::DESCRIBE_INDENT, $depth + 1);
        $closeIndent = str_repeat(self::DESCRIBE_INDENT, $depth);
        $lines       = [];
        $i           = 0;
        foreach ($value as $k => $v) {
            if ($i++ >= self::DESCRIBE_MAX_ENTRIES) {
                $lines[] = $indent . '… (' . ($count - self::DESCRIBE_MAX_ENTRIES) . ' more)';
                break;
            }
            $lines[] = $indent . $this->formatKey($k) . ' => ' . $this->describe($v, $depth + 1) . ',';
        }

        return 'array(' . $count . ") [\n" . implode("\n", $lines) . "\n" . $closeIndent . ']';
    }

    private function describeObject(object $value, int $depth): string
    {
        $class = get_class($value);

        if ($value instanceof \UnitEnum) {
            return $class . '::' . $value->name;
        }

        // Treat stdClass / generic value objects like associative arrays so the
        // dump is useful instead of just "object(stdClass)".
        if ($value instanceof \stdClass || $value instanceof \JsonSerializable || $value instanceof \ArrayObject) {
            try {
                $array = $value instanceof \JsonSerializable
                    ? (array) $value->jsonSerialize()
                    : (array) $value;
            } catch (\Throwable $e) {
                return 'object(' . $class . ')';
            }
            return 'object(' . $class . ') ' . $this->describeArray($array, $depth);
        }

        return 'object(' . $class . ')';
    }

    private function formatKey($key): string
    {
        return is_int($key) ? (string) $key : var_export((string) $key, true);
    }

    /** Lists of at most 12 short scalars, rendered on a single line. */
    private function isFlatNumericList(array $value): bool
    {
        if (count($value) > 12) {
            return false;
        }
        $expected = 0;
        foreach ($value as $k => $v) {
            if ($k !== $expected++) {
                return false;
            }
            if (is_array($v) || is_object($v)) {
                return false;
            }
            if (is_string($v) && strlen($v) > 40) {
                return false;
            }
        }
        return true;
    }

    /**
     * Decide once per request whether the response may carry the panel at all.
     * The final decision is taken on the actual response in detectOutputKind(),
     * this early check only avoids buffering requests that are never HTML:
     *   - CLI / tracker mode
     *   - tracker endpoints (matomo.php, piwik.php)
     *   - API calls (module=API, module=Proxy)
     *   - an explicit non-HTML format (json, xml, csv, tsv, rss…)
     */
    private static function isHtmlRequest(): bool
    {
        if (self::$htmlRequest !== null) {
            return self::$htmlRequest;
        }

        if (PHP_SAPI === 'cli') {
            return self::$htmlRequest = false;
        }
        if (defined('PIWIK_TRACKER_MODE') && PIWIK_TRACKER_MODE) {
            return self::$htmlRequest = false;
        }

        $requestUri = (string) ($_SERVER['REQUEST_URI'] ?? '');
        $scriptName = (string) ($_SERVER['SCRIPT_NAME'] ?? '');
        foreach (['matomo.php', 'piwik.php'] as $trackerEndpoint) {
            if (strpos($scriptName, $trackerEndpoint) !== false || strpos($requestUri, '/' . $trackerEndpoint) !== false) {
                return self::$htmlRequest = false;
            }
        }

        $module = $_GET['module'] ?? $_POST['module'] ?? null;
        if ($module === 'API' || $module === 'Proxy') {
            return self::$htmlRequest = false;
        }

        $format = $_GET['format'] ?? $_POST['format'] ?? '';
        $format = is_string($format) ? strtolower($format) : '';
        if ($format !== '' && !in_array($format, ['html', 'html2', 'original'], true)) {
            return self::$htmlRequest = false;
        }

        return self::$htmlRequest = true;
    }
}
