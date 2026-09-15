<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\HooksViewer;

use Piwik\Common;
use Piwik\Container\StaticContainer;
use Piwik\Nonce;
use Piwik\Piwik;
use Piwik\Plugin\ControllerAdmin;
use Piwik\Plugin\Manager;
use Piwik\Url;

class Controller extends ControllerAdmin
{
    private const RESCAN_NONCE = 'HooksViewer.rescan';

    /**
     * Administration > Diagnostic > Hooks Viewer: catalog of every known hook.
     */
    public function index()
    {
        Piwik::checkUserHasSuperUserAccess();

        $rescanUrl = 'index.php' . Url::getCurrentQueryStringWithParametersModified([
            'action' => 'rescan',
            'nonce' => Nonce::getNonce(self::RESCAN_NONCE),
            'hook' => null,
            'rescanned' => null,
        ]);

        return $this->renderTemplate('index', [
            'hooks' => $this->buildHookList((new HookCatalog())->getHookDetails()),
            'rescanUrl' => $rescanUrl,
            'rescanned' => Common::getRequestVar('rescanned', 0, 'int') === 1,
            'initialHook' => Common::getRequestVar('hook', '', 'string'),
            'logPath' => HookCatalog::tmpPath() . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . 'hooksviewer.log',
        ]);
    }

    /**
     * Rebuild the hook catalog now instead of waiting for the source tree signature to change.
     */
    public function rescan()
    {
        Piwik::checkUserHasSuperUserAccess();
        Nonce::checkNonce(self::RESCAN_NONCE, Common::getRequestVar('nonce', '', 'string'));

        $catalog = new HookCatalog();
        $catalog->invalidate();
        $catalog->getHooks();

        Url::redirectToUrl('index.php' . Url::getCurrentQueryStringWithParametersModified([
            'action' => 'index',
            'nonce' => null,
            'rescanned' => 1,
        ]));
    }

    /**
     * Merge the discovered hooks with the plugins currently listening to them.
     * Events that are listened to but never found in the source code (names
     * built at runtime, like Controller.CoreHome.index) are listed as dynamic.
     */
    private function buildHookList(array $details): array
    {
        $listeners = $this->getListeners();
        $hooks = [];

        foreach ($details as $name => $detail) {
            $hooks[$name] = [
                'name' => $name,
                'category' => $this->getCategory($name),
                'description' => $detail['description'],
                'params' => $detail['params'],
                'locations' => $detail['locations'],
                'listeners' => $listeners[$name] ?? [],
                'dynamic' => false,
            ];
        }

        foreach ($listeners as $name => $plugins) {
            if (isset($hooks[$name])) {
                continue;
            }
            $hooks[$name] = [
                'name' => $name,
                'category' => $this->getCategory($name),
                'description' => '',
                'params' => [],
                'locations' => [],
                'listeners' => $plugins,
                'dynamic' => true,
            ];
        }

        uksort($hooks, 'strcasecmp');
        return array_values($hooks);
    }

    /**
     * @return array<string, string[]> event name => names of the listening plugins
     */
    private function getListeners(): array
    {
        $listeners = [];

        foreach (Manager::getInstance()->getLoadedPlugins() as $plugin) {
            $pluginName = $plugin->getPluginName();
            if ($pluginName === 'HooksViewer') {
                continue;
            }

            try {
                $events = $plugin->registerEvents();
            } catch (\Throwable $e) {
                continue;
            }

            foreach (is_array($events) ? array_keys($events) : [] as $eventName) {
                $listeners[(string) $eventName][] = $pluginName;
            }
        }

        try {
            $observers = StaticContainer::get('observers.global');
        } catch (\Throwable $e) {
            $observers = [];
        }
        foreach (is_array($observers) ? $observers : [] as $observer) {
            if (is_array($observer) && isset($observer[0]) && is_string($observer[0])) {
                $listeners[$observer[0]][] = 'observers.global';
            }
        }

        return array_map(function (array $plugins): array {
            return array_values(array_unique($plugins));
        }, $listeners);
    }

    private function getCategory(string $name): string
    {
        $position = strpos($name, '.');
        return $position === false ? $name : substr($name, 0, $position);
    }
}
