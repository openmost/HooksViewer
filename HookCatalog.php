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
    private const CACHE_VERSION = 2;

    /** Directories to walk, relative to PIWIK_INCLUDE_PATH. */
    private const SCAN_ROOTS = ['core', 'plugins'];

    /**
     * How long the persisted cache is trusted without re-validating its
     * signature. Within this window the cache file's existence is enough; we
     * only rescan directory mtimes after the TTL elapses or on explicit
     * invalidate(). This keeps cache hits in the sub-millisecond range.
     */
    private const SIGNATURE_TTL_SECONDS = 300;

    /**
     * Return the discovered list of fully-qualified event names. Reads from the
     * filesystem cache if it is still fresh; rebuilds otherwise.
     *
     * @return string[]
     */
    public function getHooks(): array
    {
        $cacheFile = $this->cacheFile();

        if (is_readable($cacheFile)) {
            $age = time() - (int)@filemtime($cacheFile);
            $cached = @include $cacheFile;

            if (
                is_array($cached)
                && ($cached['version'] ?? null) === self::CACHE_VERSION
                && isset($cached['hooks']) && is_array($cached['hooks'])
            ) {
                if ($age < self::SIGNATURE_TTL_SECONDS) {
                    // Within TTL: trust the cache without rescanning mtimes.
                    return $cached['hooks'];
                }
                if (($cached['signature'] ?? null) === $this->sourceSignature()) {
                    // Signature still matches: refresh the file mtime to push the TTL forward.
                    @touch($cacheFile);
                    return $cached['hooks'];
                }
            }
        }

        $hooks = $this->scan();

        $payload = "<?php\nreturn " . var_export([
            'version'   => self::CACHE_VERSION,
            'signature' => $this->sourceSignature(),
            'hooks'     => $hooks,
        ], true) . ";\n";
        @file_put_contents($cacheFile, $payload, LOCK_EX);

        return $hooks;
    }

    /**
     * Force the cache to be rebuilt on next read. Used after plugin
     * install/activate so newly added core hooks (or new plugins) are picked up
     * without waiting for the mtime signature to change.
     */
    public function invalidate(): void
    {
        $cacheFile = $this->cacheFile();
        if (is_file($cacheFile)) {
            @unlink($cacheFile);
        }
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
     * @return string[]
     */
    private function scan(): array
    {
        $hooks = [];

        foreach ($this->phpFiles() as $file) {
            $source = @file_get_contents($file);
            if ($source === false || strpos($source, 'postEvent') === false) {
                continue;
            }

            $constants = $this->extractConstants($source);

            // Capture the first argument to postEvent: string literal, constant, or self::CONST.
            // Pattern explained: postEvent ( <ws> ( '...' | "..." | self::CONST | static::CONST | CONST_NAME )
            $pattern = '/postEvent\s*\(\s*(?P<arg>'
                . "'(?:\\\\'|[^'])*'"           // single-quoted string
                . '|"(?:\\\\"|[^"])*"'          // double-quoted string
                . '|(?:self|static|[A-Za-z_\\\\][A-Za-z0-9_\\\\]*)::[A-Z_][A-Z0-9_]*' // ClassRef::CONST
                . '|[A-Z][A-Z0-9_]*'            // bare CONST
                . ')/';

            if (!preg_match_all($pattern, $source, $matches)) {
                continue;
            }

            foreach ($matches['arg'] as $rawArg) {
                $resolved = $this->resolveArgument($rawArg, $constants);
                if ($resolved !== null) {
                    $hooks[$resolved] = true;
                }
            }
        }

        $hooks = array_keys($hooks);
        sort($hooks, SORT_STRING);
        return $hooks;
    }

    /**
     * Map `const NAME = 'value';` declarations to their string values. Captures
     * both class constants and top-level `const FOO = '…'`. Anything that is
     * not a single-line string literal is skipped, we only need the easy wins.
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
        if (!preg_match('/^[A-Za-z][A-Za-z0-9]*(?:\.[A-Za-z][A-Za-z0-9]*)+$/', $name)) {
            return false;
        }
        return true;
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
