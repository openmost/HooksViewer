/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/** A hook fired during a request, as sent by the server: [index, name, arguments dump]. */
export type RawPanelEvent = [number, string, string];

export interface PanelEvent {
  index: number;
  name: string;
  args: string;
}

export interface HookLocation {
  file: string;
  line: number;
}

export interface HookParam {
  type: string;
  name: string;
  description: string;
}

export interface CatalogHook {
  name: string;
  category: string;
  description: string;
  params: HookParam[];
  locations: HookLocation[];
  listeners: string[];
  dynamic: boolean;
}

function searchTerms(query: string): string[] {
  return query.toLowerCase().split(/\s+/).filter((term) => term.length > 0);
}

/** Every term of the query must appear in at least one of the texts (case insensitive). */
export function matchesQuery(texts: string[], query: string): boolean {
  const terms = searchTerms(query);
  if (!terms.length) {
    return true;
  }
  const haystack = texts.join('\n').toLowerCase();
  return terms.every((term) => haystack.includes(term));
}

export function toPanelEvents(rawEvents: RawPanelEvent[]): PanelEvent[] {
  return rawEvents.map(([index, name, args]) => ({ index, name, args }));
}

export function filterPanelEvents(
  events: PanelEvent[],
  query: string,
  searchInArguments: boolean,
): PanelEvent[] {
  if (!searchTerms(query).length) {
    return events;
  }
  return events.filter((event) => matchesQuery(
    searchInArguments ? [event.name, event.args] : [event.name],
    query,
  ));
}

export function filterCatalogHooks(
  hooks: CatalogHook[],
  query: string,
  category: string,
  onlyWithListeners: boolean,
): CatalogHook[] {
  return hooks.filter((hook) => (!category || hook.category === category)
    && (!onlyWithListeners || hook.listeners.length > 0)
    && matchesQuery([
      hook.name,
      hook.description,
      ...hook.listeners,
      ...hook.locations.map((location) => location.file),
    ], query));
}

export function categoriesOf(hooks: CatalogHook[]): string[] {
  return [...new Set(hooks.map((hook) => hook.category))]
    .sort((a, b) => a.localeCompare(b));
}

/** First paragraph of a description, on a single line. */
export function firstParagraph(description: string): string {
  return (description.split(/\n\s*\n/)[0] || '').replace(/\s+/g, ' ').trim();
}

/** Anchor of an event on https://developer.matomo.org/api-reference/events */
export function developerReferenceAnchor(hookName: string): string {
  return hookName.toLowerCase().replace(/[^a-z0-9]/g, '');
}

/** registerEvents() snippet subscribing to the hook ('Request.dispatch' => 'onRequestDispatch'). */
export function listenSnippet(hookName: string): string {
  const method = `on${hookName.split('.')
    .map((part) => part.charAt(0).toUpperCase() + part.slice(1))
    .join('')}`;

  return [
    'public function registerEvents()',
    '{',
    '    return [',
    `        '${hookName}' => '${method}',`,
    '    ];',
    '}',
  ].join('\n');
}

/** URL of the hook catalog admin page, keeping the site, period and date of the current page. */
export function catalogUrl(currentSearch: string, hookName = ''): string {
  const current = new URLSearchParams(currentSearch);
  const params = new URLSearchParams({ module: 'HooksViewer', action: 'index' });

  ['idSite', 'period', 'date'].forEach((name) => {
    const value = current.get(name);
    if (value) {
      params.set(name, value);
    }
  });

  if (hookName) {
    params.set('hook', hookName);
  }

  return `index.php?${params.toString()}`;
}
