<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\HooksViewer;

class HooksViewer extends \Piwik\Plugin
{
    /** Cached HTML-context decision for the current request. null = not yet decided. */
    private static $htmlContext = null;

    /** Running count of events emitted in this request (for ordering hints). */
    private static $eventIndex = 0;

    /** In-memory copy of the discovered hook list — populated lazily. */
    private static $hooks = null;

    /** Resolved log file path (false if logs cannot be written). */
    private static $logPath = null;

    /** Short identifier for the current request, so log lines can be correlated. */
    private static $requestId = null;

    /**
     * Subscribe to every discovered hook.
     *
     * The list is built dynamically by HookCatalog (which scans core/ and
     * plugins/ for postEvent('…') call sites), so newly added events show up
     * automatically — no hand-maintained list to drift behind core.
     */
    public function registerEvents()
    {
        $map = [];
        foreach ($this->getHooks() as $hookName) {
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
        if (class_exists('\\Piwik\\AssetManager')) {
            try {
                \Piwik\AssetManager::getInstance()->removeMergedAssets();
            } catch (\Throwable $e) {
                // Cache reset is best-effort — failing here must never block activation.
            }
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
     * cannot mutate by-ref args — which is fine because we only observe.
     * Hooks needing real by-ref behavior (e.g. AssetManager.getStylesheetFiles)
     * are handled via dedicated methods below.
     */
    public function __call($name, $arguments)
    {
        $hookName = $this->methodToHook((string)$name);
        if ($hookName === null) {
            trigger_error(sprintf('Call to undefined method %s::%s()', static::class, $name), E_USER_ERROR);
            return null;
        }
        $this->captureHook($hookName, $arguments);
        return null;
    }

    /**
     * Explicit handler: this one needs by-reference access to register the
     * plugin's own stylesheet. Overrides the generic dispatch for this hook.
     */
    public function hook_AssetManager_getStylesheetFiles(&$files)
    {
        $files[] = 'plugins/HooksViewer/stylesheets/style.less';
        $this->captureHook('AssetManager.getStylesheetFiles', [$files]);
    }

    /**
     * Render the hook inline at the moment it fires (HTML responses only) and
     * always append a line to the request-scoped log file.
     *
     * Why no inline emission on non-HTML responses?
     *   - JSON has no comment syntax, so any marker prefix breaks API consumers
     *     (widgets, dashboard, third-party clients).
     *   - Image / tracker responses must be byte-exact.
     *   - CSV / XML are similarly fragile.
     * Devs who need to see hooks fire on JSON/tracker requests can `tail -f`
     * tmp/logs/hooksviewer.log — every hook from every request shows up there.
     */
    private function captureHook(string $hookName, array $args): void
    {
        $index = ++self::$eventIndex;
        $argsText = $this->summariseArgs($args);

        $this->logEvent($hookName, $index, $argsText);

        if (!$this->isHtmlContext()) {
            return;
        }

        ob_start();
        echo '<details class="hv-event" data-hook="', htmlspecialchars($hookName, ENT_QUOTES), '">',
            '<summary><span class="hv-event-index">#', $index, '</span> ',
            htmlspecialchars($hookName, ENT_QUOTES),
            '</summary>',
            '<pre class="hv-args"><code>', htmlspecialchars($argsText, ENT_QUOTES), '</code></pre>',
            '</details>';
        echo ob_get_clean();
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
            if (self::$logPath === false) {
                return;
            }
        }
        if (self::$logPath === false) {
            return;
        }

        $line = sprintf(
            "[%s] %s #%d %s :: %s\n",
            date('Y-m-d H:i:s'),
            $this->shortRequestId(),
            $index,
            $hookName,
            str_replace(["\r\n", "\n", "\r"], ' | ', $argsText)
        );
        @file_put_contents(self::$logPath, $line, FILE_APPEND | LOCK_EX);
    }

    private function resolveLogPath()
    {
        $base = defined('PIWIK_INCLUDE_PATH')
            ? rtrim(PIWIK_INCLUDE_PATH, '/\\')
            : dirname(__DIR__, 2);
        $dir = $base . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . 'logs';
        if (!is_dir($dir) && !@mkdir($dir, 0755, true) && !is_dir($dir)) {
            return false;
        }
        return $dir . DIRECTORY_SEPARATOR . 'hooksviewer.log';
    }

    private function shortRequestId(): string
    {
        if (self::$requestId === null) {
            self::$requestId = substr(bin2hex(random_bytes(3)), 0, 6);
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

    /** Maximum nested depth to walk before bailing out with "…". */
    private const DESCRIBE_MAX_DEPTH = 4;

    /** Indentation used between nested levels. Two spaces keeps lines short. */
    private const DESCRIBE_INDENT = '  ';

    /** Maximum number of array entries / object props to emit per level. */
    private const DESCRIBE_MAX_ENTRIES = 30;

    /** Maximum displayed length of a string value (longer values are truncated). */
    private const DESCRIBE_MAX_STRING = 240;

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
            return (string)$value;
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
            $value = substr($value, 0, self::DESCRIBE_MAX_STRING) . '…';
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

        // Numeric arrays of scalars stay on a single line — easier to scan.
        if ($this->isFlatNumericList($value)) {
            $parts = [];
            $i = 0;
            foreach ($value as $v) {
                if ($i++ >= self::DESCRIBE_MAX_ENTRIES) {
                    $parts[] = '…';
                    break;
                }
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

        // Treat stdClass / generic value objects like associative arrays so the
        // dump is useful instead of just "object(stdClass)".
        if ($value instanceof \stdClass || $value instanceof \JsonSerializable || $value instanceof \ArrayObject) {
            $array = $value instanceof \JsonSerializable
                ? (array)$value->jsonSerialize()
                : (array)$value;
            return 'object(' . $class . ') ' . $this->describeArray($array, $depth);
        }

        return 'object(' . $class . ')';
    }

    private function formatKey($key): string
    {
        return is_int($key) ? (string)$key : var_export((string)$key, true);
    }

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
     * Decide once per request whether the response is HTML. Cached because a
     * hook may fire many times per response and the answer cannot meaningfully
     * change once headers have been sent.
     *
     * IMPORTANT: hooks often fire BEFORE Matomo has sent any Content-Type
     * header — by the time `headers_list()` reflects "application/json" the
     * decision has already been made and we may have polluted the response.
     * To stay safe we treat any of the following as non-HTML:
     *   - CLI / tracker mode
     *   - explicit XHR (X-Requested-With)
     *   - request looks like an API call (module=API, ?format=json/xml/csv,
     *     /matomo.php endpoint, etc.)
     *   - already-sent Content-Type that is not text/html
     * Anything else is assumed HTML.
     */
    private function isHtmlContext(): bool
    {
        if (self::$htmlContext !== null) {
            return self::$htmlContext;
        }

        // Hard-no contexts: response bytes must be exact.
        if (PHP_SAPI === 'cli') {
            return self::$htmlContext = false;
        }
        if (defined('PIWIK_TRACKER_MODE') && PIWIK_TRACKER_MODE) {
            return self::$htmlContext = false;
        }

        $requestUri = $_SERVER['REQUEST_URI'] ?? '';
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        if (
            strpos($scriptName, 'matomo.php') !== false
            || strpos($scriptName, 'piwik.php') !== false
            || strpos($requestUri, '/matomo.php') !== false
            || strpos($requestUri, '/piwik.php') !== false
        ) {
            return self::$htmlContext = false;
        }

        $module = $_GET['module'] ?? $_POST['module'] ?? null;
        $format = strtolower((string)($_GET['format'] ?? $_POST['format'] ?? ''));

        // API responses are JSON/XML/CSV — never inject HTML.
        if ($module === 'API' || $module === 'Proxy') {
            return self::$htmlContext = false;
        }

        // Explicit format wins. A widget AJAX request asks for format=html and
        // expects rendered markup; without this branch the X-Requested-With
        // check below would suppress hooks like ViewDataTable.filterViewDataTable
        // which only fire during widget rendering.
        if ($format === 'html' || $format === 'html2' || $format === 'original') {
            return self::$htmlContext = true;
        }
        if ($format !== '') {
            // json, xml, csv, tsv, rss, … — none accept inline markers.
            return self::$htmlContext = false;
        }

        // No explicit format. Many controller actions return HTML even when
        // requested via XHR (widget=1, dashboard inline rendering, etc.). Trust
        // the Content-Type header if it has been set; otherwise — including
        // for XHRs without a format — assume HTML so widget hooks stay visible.
        foreach (headers_list() as $header) {
            if (stripos($header, 'Content-Type:') !== 0) {
                continue;
            }
            $value = strtolower(trim(substr($header, 13)));
            if ($value === '' || strpos($value, 'text/html') === 0) {
                return self::$htmlContext = true;
            }
            return self::$htmlContext = false;
        }

        return self::$htmlContext = true;
    }
}
