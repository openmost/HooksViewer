<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <details class="hv-panel" :open="isOpen" @toggle="onToggle">
    <summary class="hv-panel-summary">
      <span class="hv-panel-title">HooksViewer</span>
      {{ summary }}
    </summary>
    <div v-if="isOpen" class="hv-panel-body">
      <div class="hv-toolbar">
        <input
          v-model="query"
          type="search"
          class="hv-search browser-default"
          :placeholder="translate('HooksViewer_SearchPlaceholder')"
        />
        <label class="hv-checkbox">
          <input v-model="searchInArguments" type="checkbox" />
          <span>{{ translate('HooksViewer_SearchInArguments') }}</span>
        </label>
        <span class="hv-count">
          {{ translate('HooksViewer_Showing', filteredEvents.length, allEvents.length) }}
        </span>
        <a class="hv-link" :href="hookUrl('')">{{ translate('HooksViewer_HooksViewer') }}</a>
      </div>
      <p v-if="!filteredEvents.length" class="hv-empty">{{ translate('HooksViewer_NoMatch') }}</p>
      <div class="hv-panel-events">
        <details v-for="event in filteredEvents" :key="event.index" class="hv-event">
          <summary>
            <span class="hv-event-index">#{{ event.index }}</span> {{ event.name }}
          </summary>
          <pre class="hv-args"><code>{{ event.args }}</code></pre>
          <a class="hv-link" :href="hookUrl(event.name)">{{ translate('HooksViewer_OpenInCatalog') }}</a>
        </details>
      </div>
    </div>
  </details>
</template>

<script lang="ts">
import {
  computed,
  defineComponent,
  PropType,
  ref,
} from 'vue';
import { translate } from 'CoreHome';
import { catalogUrl, filterPanelEvents, toPanelEvents } from '../hooks';
import type { RawPanelEvent } from '../hooks';

/**
 * Collapsible list of the hooks fired while a response was built, injected by the server
 * right after <body> on pages and at the top of widgets. The list is only rendered once the
 * panel is opened, as a single widget request can fire more than a thousand hooks.
 */
export default defineComponent({
  name: 'HooksPanel',
  props: {
    events: {
      type: Array as PropType<RawPanelEvent[]>,
      required: true,
    },
    requestId: {
      type: String,
      default: '',
    },
    droppedCount: {
      type: Number,
      default: 0,
    },
  },
  setup(props) {
    const isOpen = ref(false);
    const query = ref('');
    const searchInArguments = ref(false);

    const allEvents = computed(() => toPanelEvents(props.events));
    const filteredEvents = computed(() => filterPanelEvents(
      allEvents.value,
      query.value,
      searchInArguments.value,
    ));

    const summary = computed(() => {
      const parts = [
        allEvents.value.length === 1
          ? translate('HooksViewer_OneHook')
          : translate('HooksViewer_HooksCount', String(allEvents.value.length)),
        translate('HooksViewer_RequestId', props.requestId),
      ];
      if (props.droppedCount > 0) {
        parts.push(translate('HooksViewer_MoreInLog', String(props.droppedCount)));
      }
      return parts.join(', ');
    });

    function onToggle(event: Event) {
      isOpen.value = (event.target as HTMLDetailsElement).open;
    }

    function hookUrl(hookName: string) {
      return catalogUrl(window.location.search, hookName);
    }

    return {
      isOpen,
      query,
      searchInArguments,
      allEvents,
      filteredEvents,
      summary,
      onToggle,
      hookUrl,
      translate,
    };
  },
});
</script>
