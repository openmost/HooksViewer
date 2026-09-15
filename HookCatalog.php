<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\HooksViewer;

/**
 * Discovers every Matomo event name by statically scanning the codebase for
 * Piwik::postEvent('…') / $dispatcher->postEvent('…') call sites.
 *
 * For each event it also records where it is posted (file and line) and the
 * docblock written right above the call, which core uses to document events
 * (description and @param tags).
 *
 * The result is cached in tmp/cache/ and invalidated whenever the most-recent
 * mtime across scanned source trees changes, so adding a new core or plugin
 * file forces a rescan on the next request, with no manual list to maintain.
 *
 * Wildcard hooks (names containing PHP variables, sprintf placeholders, or
 * obvious template fragments) are filtered out: they cannot be subscribed to
 * by a single static event name and would only pollute the registration map.
 *
 * Constant references like self::FOO_EVENT are resolved opportunistically by
 * scanning the same file for `const FOO_EVENT = 'whatever'`.
 */
class HookCatalog
{
    /** Cache version: bump when the discovery logic changes shape. */
    private const CACHE_VERSION = 3;

    /** Directories to walk, relative to PIWIK_INCLUDE_PATH. */
    private const SCAN_ROOTS = ['core', 'plugins'];

    /**
     * How long the persisted cache is trusted without re-validating its
     * signature. Within this window the cache file's existence is enough; we
     * only rescan directory mtimes after the TTL elapses or on explicit
     * invalidate(). This keeps cache hits in the sub-millisecond range.
     */
    private const SIGNATURE_TTL_SECONDS = 300;

    /** Longest docblock kept for an event, anything longer is not an event description. */
    private const DOCBLOCK_MAX_LENGTH = 8000;

    /** In-memory copy of the cache payload for the current request. */
    private static $payload = null;

    /**
     * Return the discovered list of fully-qualified event names. Reads from the
     * filesystem cache if it is still fresh; rebuilds otherwise.
     *
     * @return string[]
     */
    public function getHooks(): array
    {
        return $this->load()['hooks'];
    }

    /**
     * Where each event is posted and how it is documented.
     *
     * @return array<string, array{locations: array<array{file: string, line: int}>, description: string, params: array<array{type: string, name: string, description: string}>}>
     */
    public function getHookDetails(): array
    {
        return $this->load()['details'];
    }

    /** Unix timestamp of the last scan. */
    public function getScannedAt(): int
    {
        return (int) $this->load()['scannedAt'];
    }

    /**
     * Force the cache to be rebuilt on next read. Used after plugin
     * install/activate so newly added core hooks (or new plugins) are picked up
     * without waiting for the mtime signature to change.
     */
    public function invalidate(): void
    {
        self::$payload = null;
        $cacheFile = $this->cacheFile();
        if (is_file($cacheFile)) {
            @unlink($cacheFile);
        }
    }

    private function load(): array
    {
        if (self::$payload !== null) {
            return self::$payload;
        }

        $cacheFile = $this->cacheFile();

        if (is_readable($cacheFile)) {
            $age = time() - (int) @filemtime($cacheFile);
            $cached = @include $cacheFile;

            if (
                is_array($cached)
                && ($cached['version'] ?? null) === self::CACHE_VERSION
                && isset($cached['hooks'], $cached['details'])
                && is_array($cached['hooks']) && is_array($cached['details'])
            ) {
                if ($age < self::SIGNATURE_TTL_SECONDS) {
                    // Within TTL: trust the cache without rescanning mtimes.
                    return self::$payload = $cached;
                }
                if (($cached['signature'] ?? null) === $this->sourceSignature()) {
                    // Signature still matches: refresh the file mtime to push the TTL forward.
                    @touch($cacheFile);
                    return self::$payload = $cached;
                }
            }
        }

        $details = $this->scan();

        $payload = [
            'version'   => self::CACHE_VERSION,
            'signature' => $this->sourceSignature(),
            'scannedAt' => time(),
            'hooks'     => array_keys($details),
            'details'   => $details,
        ];
        @file_put_contents($cacheFile, "<?php\nreturn " . var_export($payload, true) . ";\n", LOCK_EX);

        return self::$payload = $payload;
    }

    /**
     * A short signature describing the scanned file tree. We hash directory
     * mtimes rather than every individual file: any add/remove/rename inside
     * a directory bumps that directory's mtime, and editing a file bumps the
     * file's mtime which we still pick up via its parent directory on most
     * filesystems. Cache-hit cost stays in single-digit milliseconds even on
     * large installs.
     */
    private function sourceSignature(): string
    {
        $parts = [];
        $base = $this->basePath();

        foreach (self::SCAN_ROOTS as $root) {
            $rootPath = $base . DIRECTORY_SEPARATOR . $root;
            if (!is_dir($rootPath)) {
                continue;
            }

            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveCallbackFilterIterator(
                    new \RecursiveDirectoryIterator($rootPath, \FilesystemIterator::SKIP_DOTS),
                    function (\SplFileInfo $current): bool {
                        if (!$current->isDir()) {
                            return false;
                        }
                        $name = $current->getFilename();
                        return $name !== 'vendor' && $name !== 'tests' && $name !== 'node_modules';
                    }
                ),
                \RecursiveIteratorIterator::SELF_FIRST
            );

            foreach ($iterator as $dir) {
                /** @var \SplFileInfo $dir */
                $parts[] = $dir->getMTime();
            }
            // Include the root mtime too.
            $parts[] = @filemtime($rootPath);
        }

        return md5(implode('|', $parts));
    }

    /**
     * Walk SCAN_ROOTS and yield every .php file. Skips vendor/ and tests/:
     * those never legitimately register events that affect a running install,
     * and skipping them shaves the scan time substantially.
     */
    private function phpFiles(): \Generator
    {
        $base = $this->basePath();
        foreach (self::SCAN_ROOTS as $root) {
            $rootPath = $base . DIRECTORY_SEPARATOR . $root;
            if (!is_dir($rootPath)) {
                continue;
            }

            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveCallbackFilterIterator(
                    new \RecursiveDirectoryIterator($rootPath, \FilesystemIterator::SKIP_DOTS),
                    function (\SplFileInfo $current): bool {
                        $name = $current->getFilename();
                        if ($current->isDir()) {
                            return $name !== 'vendor' && $name !== 'tests' && $name !== 'node_modules';
                        }
                        return substr($name, -4) === '.php';
                    }
                )
            );

            foreach ($iterator as $file) {
                /** @var \SplFileInfo $file */
                if ($file->isFile()) {
                    yield $file->getPathname();
                }
            }
        }
    }

    /**
     * Read every PHP file once and pull out literal event names from postEvent
     * calls. Matches both `Piwik::postEvent('Foo.bar'` and `->postEvent('Foo.bar'`.
     *
     * Same-file constant references (self::FOO, static::FOO) are resolved
     * against `const FOO = 'value'` declarations in that file.
     *
     * @return array<string, array> event name => details, sorted by name
     */
    private function scan(): array
    {
        $details = [];

        // Capture the first argument to postEvent: string literal, constant, or self::CONST.
        // Pattern explained: postEvent ( <ws> ( '...' | "..." | self::CONST | static::CONST | CONST_NAME )
        $pattern = '/postEvent\s*\(\s*(?P<arg>'
            . "'(?:\\\\'|[^'])*'"           // single-quoted string
            . '|"(?:\\\\"|[^"])*"'          // double-quoted string
            . '|(?:self|static|[A-Za-z_\\\\][A-Za-z0-9_\\\\]*)::[A-Z_][A-Z0-9_]*' // ClassRef::CONST
            . '|[A-Z][A-Z0-9_]*'            // bare CONST
            . ')/';

        foreach ($this->phpFiles() as $file) {
            $source = @file_get_contents($file);
            if ($source === false || strpos($source, 'postEvent') === false) {
                continue;
            }

            if (!preg_match_all($pattern, $source, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE)) {
                continue;
            }

            $constants = $this->extractConstants($source);
            $relativePath = $this->relativePath($file);

            foreach ($matches as $match) {
                $name = $this->resolveArgument($match['arg'][0], $constants);
                if ($name === null) {
                    continue;
                }

                $offset = $match[0][1];
                if (!isset($details[$name])) {
                    $details[$name] = ['locations' => [], 'description' => '', 'params' => []];
                }
                $details[$name]['locations'][] = [
                    'file' => $relativePath,
                    'line' => $offset > 0 ? substr_count($source, "\n", 0, $offset) + 1 : 1,
                ];

                if ($details[$name]['description'] === '' && $details[$name]['params'] === []) {
                    [$description, $params] = $this->parseDocBlock($this->docBlockBefore($source, $offset));
                    $details[$name]['description'] = $description;
                    $details[$name]['params'] = $params;
                }
            }
        }

        ksort($details, SORT_STRING);
        return $details;
    }

    /**
     * The docblock ending on the line right above the statement that posts the
     * event, or an empty string when there is none.
     */
    private function docBlockBefore(string $source, int $offset): string
    {
        $lineStart = strrpos(substr($source, 0, $offset), "\n");
        if ($lineStart === false) {
            return '';
        }

        $before = rtrim(substr($source, 0, $lineStart));
        if (substr($before, -2) !== '*/') {
            return '';
        }

        $start = strrpos($before, '/**');
        if ($start === false || strlen($before) - $start > self::DOCBLOCK_MAX_LENGTH) {
            return '';
        }

        return substr($before, $start);
    }

    /**
     * Split a docblock into its free text and its @param tags. Other tags
     * (@api, @deprecated, …) are dropped.
     *
     * @return array{0: string, 1: array<array{type: string, name: string, description: string}>}
     */
    private function parseDocBlock(string $docBlock): array
    {
        if ($docBlock === '') {
            return ['', []];
        }

        $text = [];
        $params = [];
        $inTag = false;

        foreach (preg_split('/\R/', $docBlock) as $line) {
            $line = preg_replace('/^\s*(?:\/\*\*|\*\/|\*)\s?/', '', $line);
            $line = rtrim(preg_replace('/\s*\*\/\s*$/', '', $line));
            $trimmed = trim($line);

            if (preg_match('/^@param\s+(\S+)(?:\s+(&?\$\S+))?\s*(.*)$/', $trimmed, $m)) {
                $params[] = ['type' => $m[1], 'name' => $m[2] ?? '', 'description' => $m[3] ?? ''];
                $inTag = true;
                continue;
            }
            if (strpos($trimmed, '@') === 0) {
                $inTag = false;
                continue;
            }
            if ($inTag) {
                if ($trimmed === '') {
                    $inTag = false;
                } elseif ($params !== []) {
                    $params[count($params) - 1]['description'] = trim($params[count($params) - 1]['description'] . ' ' . $trimmed);
                }
                continue;
            }

            $text[] = $line;
        }

        return [trim(implode("\n", $text)), $params];
    }

    /**
     * Map `const NAME = 'value';` declarations to their string values. Captures
     * both class constants and top-level `const FOO = '…'`. Anything that is
     * not a single-line string literal is skipped: we only need the easy wins.
     *
     * @return array<string,string>
     */
    private function extractConstants(string $source): array
    {
        $out = [];

        if (preg_match_all(
            '/\bconst\s+(?P<name>[A-Z_][A-Z0-9_]*)\s*=\s*(?P<val>\'[^\']*\'|"[^"]*")\s*;/',
            $source,
            $m
        )) {
            foreach ($m['name'] as $i => $name) {
                $out[$name] = $this->stripQuotes($m['val'][$i]);
            }
        }

        return $out;
    }

    /**
     * Resolve the textual first argument of a postEvent call to a static event
     * name, or return null if the argument refers to runtime data (variable,
     * concatenation, sprintf placeholder, etc).
     */
    private function resolveArgument(string $arg, array $constants): ?string
    {
        $arg = trim($arg);

        // Literal string.
        if ($arg !== '' && ($arg[0] === "'" || $arg[0] === '"')) {
            $value = $this->stripQuotes($arg);
            return $this->isUsableEventName($value) ? $value : null;
        }

        // ClassRef::CONST: only resolvable if the constant is in the same file.
        if (strpos($arg, '::') !== false) {
            [, $constName] = explode('::', $arg, 2);
            if (isset($constants[$constName])) {
                $value = $constants[$constName];
                return $this->isUsableEventName($value) ? $value : null;
            }
            return null;
        }

        // Bare uppercase constant.
        if (preg_match('/^[A-Z][A-Z0-9_]*$/', $arg) && isset($constants[$arg])) {
            $value = $constants[$arg];
            return $this->isUsableEventName($value) ? $value : null;
        }

        return null;
    }

    /**
     * An event name is "usable" only if it can be subscribed to by a single
     * literal string. Names containing variables, sprintf placeholders, or
     * obvious dynamic fragments are rejected.
     */
    private function isUsableEventName(string $name): bool
    {
        if ($name === '' || strlen($name) > 200) {
            return false;
        }
        // Must look like a Matomo event (dotted identifier path).
        return (bool) preg_match('/^[A-Za-z][A-Za-z0-9]*(?:\.[A-Za-z][A-Za-z0-9]*)+$/', $name);
    }

    private function stripQuotes(string $literal): string
    {
        if (strlen($literal) < 2) {
            return $literal;
        }
        $quote = $literal[0];
        if ($quote !== "'" && $quote !== '"') {
            return $literal;
        }
        $inner = substr($literal, 1, -1);
        // Best-effort unescape: we only care about \\' and \\" anyway.
        return str_replace(['\\' . $quote, '\\\\'], [$quote, '\\'], $inner);
    }

    /** Path relative to the Matomo root, with forward slashes (e.g. core/FrontController.php). */
    private function relativePath(string $file): string
    {
        $base = $this->basePath() . DIRECTORY_SEPARATOR;
        if (strpos($file, $base) === 0) {
            $file = substr($file, strlen($base));
        }
        return str_replace('\\', '/', $file);
    }

    private function basePath(): string
    {
        if (defined('PIWIK_INCLUDE_PATH')) {
            return rtrim(PIWIK_INCLUDE_PATH, '/\\');
        }
        // Fallback: this file lives at <base>/plugins/HooksViewer/HookCatalog.php.
        return dirname(__DIR__, 2);
    }

    /**
     * Matomo tmp directory. Honours a customised 'path.tmp' (e.g. multi-instance
     * setups) once the DI container exists, falls back to <base>/tmp before that.
     */
    public static function tmpPath(): string
    {
        try {
            $path = \Piwik\Container\StaticContainer::get('path.tmp');
            if (is_string($path) && $path !== '') {
                return rtrim($path, '/\\');
            }
        } catch (\Throwable $e) {
            // Container not built yet (very early hooks).
        }

        $base = defined('PIWIK_INCLUDE_PATH') ? rtrim(PIWIK_INCLUDE_PATH, '/\\') : dirname(__DIR__, 2);
        return $base . DIRECTORY_SEPARATOR . 'tmp';
    }

    private function cacheFile(): string
    {
        $dir = self::tmpPath() . DIRECTORY_SEPARATOR . 'cache';
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        return $dir . DIRECTORY_SEPARATOR . 'hooksviewer-catalog.php';
    }
}
