/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

import { describe, expect, it } from 'vitest';
import {
  catalogUrl,
  categoriesOf,
  developerReferenceAnchor,
  filterCatalogHooks,
  filterPanelEvents,
  firstParagraph,
  listenSnippet,
  toPanelEvents,
} from './hooks';
import type { CatalogHook } from './hooks';

const events = toPanelEvents([
  [1, 'Request.dispatch', "#0 = string(8) 'CoreHome'"],
  [2, 'Request.dispatch.end', '#0 = null'],
  [3, 'Platform.initialized', ''],
]);

function hook(overrides: Partial<CatalogHook>): CatalogHook {
  return {
    name: 'Request.dispatch',
    category: 'Request',
    description: '',
    params: [],
    locations: [],
    listeners: [],
    dynamic: false,
    ...overrides,
  };
}

describe('filterPanelEvents', () => {
  it('returns every event for an empty query', () => {
    expect(filterPanelEvents(events, '  ', false)).toHaveLength(3);
  });

  it('matches hook names case insensitively, every term being required', () => {
    expect(filterPanelEvents(events, 'request END', false).map((e) => e.index)).toEqual([2]);
  });

  it('only searches arguments when asked', () => {
    expect(filterPanelEvents(events, 'corehome', false)).toEqual([]);
    expect(filterPanelEvents(events, 'corehome', true).map((e) => e.index)).toEqual([1]);
  });
});

describe('filterCatalogHooks', () => {
  const hooks = [
    hook({ name: 'Request.dispatch', listeners: ['CoreHome'], locations: [{ file: 'core/FrontController.php', line: 646 }] }),
    hook({ name: 'Platform.initialized', category: 'Platform', description: 'After authentication' }),
  ];

  it('filters by category and listeners', () => {
    expect(filterCatalogHooks(hooks, '', 'Platform', false).map((h) => h.name)).toEqual(['Platform.initialized']);
    expect(filterCatalogHooks(hooks, '', '', true).map((h) => h.name)).toEqual(['Request.dispatch']);
  });

  it('searches names, descriptions, listeners and files', () => {
    expect(filterCatalogHooks(hooks, 'authentication', '', false)).toHaveLength(1);
    expect(filterCatalogHooks(hooks, 'corehome', '', false)).toHaveLength(1);
    expect(filterCatalogHooks(hooks, 'frontcontroller', '', false)).toHaveLength(1);
  });

  it('lists sorted unique categories', () => {
    expect(categoriesOf([...hooks, hook({})])).toEqual(['Platform', 'Request']);
  });
});

describe('helpers', () => {
  it('keeps the first paragraph on one line', () => {
    expect(firstParagraph('Triggered after\nthe dispatch.\n\n_Note:_ more')).toBe('Triggered after the dispatch.');
  });

  it('builds developer reference anchors', () => {
    expect(developerReferenceAnchor('Request.dispatch.end')).toBe('requestdispatchend');
  });

  it('builds a registerEvents snippet', () => {
    expect(listenSnippet('Request.dispatch.end')).toContain("'Request.dispatch.end' => 'onRequestDispatchEnd',");
  });

  it('builds the catalog URL from the current site, period and date only', () => {
    expect(catalogUrl('?module=CoreHome&action=index&idSite=1&period=day&date=yesterday&token_auth=x', 'Request.dispatch'))
      .toBe('index.php?module=HooksViewer&action=index&idSite=1&period=day&date=yesterday&hook=Request.dispatch');
  });
});
