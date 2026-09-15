<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class="hv-catalog">
    <ContentBlock :content-title="translate('HooksViewer_HooksViewer')">
      <p>{{ translate('HooksViewer_CatalogIntro') }}</p>
      <div class="alert alert-warning">{{ translate('HooksViewer_ProductionWarning') }}</div>
      <div v-if="rescanned" class="alert alert-success">{{ translate('HooksViewer_Rescanned') }}</div>
      <p class="hv-catalog-log">{{ translate('HooksViewer_LogFile', logPath) }}</p>

      <div class="hv-toolbar">
        <input
          v-model="query"
          type="search"
          class="hv-search browser-default"
          :placeholder="translate('HooksViewer_SearchPlaceholder')"
        />
        <select v-model="category" class="hv-select browser-default">
          <option value="">{{ translate('HooksViewer_AllCategories') }}</option>
          <option v-for="name in categories" :key="name" :value="name">{{ name }}</option>
        </select>
        <label class="hv-checkbox">
          <input v-model="onlyWithListeners" type="checkbox" />
          <span>{{ translate('HooksViewer_OnlyWithListeners') }}</span>
        </label>
        <span class="hv-count">
          {{ translate('HooksViewer_Showing', visibleHooks.length, hooks.length) }},
          {{ translate('HooksViewer_ListenedCount', String(listenedCount)) }}
        </span>
        <a class="btn btn-flat hv-rescan" :href="rescanUrl">{{ translate('HooksViewer_Rescan') }}</a>
      </div>

      <table class="entityTable hv-catalog-table">
        <thead>
          <tr>
            <th>{{ translate('HooksViewer_Hook') }}</th>
            <th>{{ translate('HooksViewer_Description') }}</th>
            <th>{{ translate('HooksViewer_Listeners') }}</th>
            <th>{{ translate('HooksViewer_PostedIn') }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="!visibleHooks.length">
            <td colspan="4">{{ translate('HooksViewer_NoMatch') }}</td>
          </tr>
          <template v-for="hook in visibleHooks" :key="hook.name">
            <tr
              class="hv-hook-row"
              :class="{ 'hv-hook-row-open': expanded[hook.name] }"
              @click="toggle(hook.name)"
            >
              <td class="hv-hook-name">
                <code>{{ hook.name }}</code>
                <span v-if="hook.dynamic" class="hv-badge" :title="translate('HooksViewer_DynamicHelp')">
                  {{ translate('HooksViewer_Dynamic') }}
                </span>
              </td>
              <td class="hv-hook-summary">{{ firstParagraph(hook.description) || '-' }}</td>
              <td class="hv-hook-listeners">{{ hook.listeners.join(', ') || '-' }}</td>
              <td class="hv-hook-locations">
                <div v-for="location in hook.locations" :key="`${location.file}:${location.line}`">
                  {{ location.file }}:{{ location.line }}
                </div>
                <span v-if="!hook.locations.length">-</span>
              </td>
            </tr>
            <tr v-if="expanded[hook.name]" class="hv-hook-details">
              <td colspan="4">
                <p v-if="hook.dynamic">{{ translate('HooksViewer_DynamicHelp') }}</p>
                <p v-if="hook.description" class="hv-hook-description">{{ hook.description }}</p>
                <p v-else-if="!hook.dynamic">{{ translate('HooksViewer_NoDescription') }}</p>

                <template v-if="hook.params.length">
                  <h3>{{ translate('HooksViewer_Parameters') }}</h3>
                  <ul class="hv-hook-params">
                    <li v-for="(param, index) in hook.params" :key="index">
                      <code>{{ param.type }} {{ param.name }}</code>
                      <span v-if="param.description"> {{ param.description }}</span>
                    </li>
                  </ul>
                </template>

                <h3>{{ translate('HooksViewer_ListenExample') }}</h3>
                <pre class="hv-args"><code>{{ listenSnippet(hook.name) }}</code></pre>

                <a
                  class="hv-link"
                  :href="referenceUrl(hook.name)"
                  target="_blank"
                  rel="noreferrer noopener"
                  @click.stop
                >{{ translate('HooksViewer_DeveloperReference') }}</a>
              </td>
            </tr>
          </template>
        </tbody>
      </table>
    </ContentBlock>
  </div>
</template>

<script lang="ts">
import {
  computed,
  defineComponent,
  PropType,
  reactive,
  ref,
} from 'vue';
import { ContentBlock, translate } from 'CoreHome';
import {
  categoriesOf,
  developerReferenceAnchor,
  filterCatalogHooks,
  firstParagraph,
  listenSnippet,
} from '../hooks';
import type { CatalogHook } from '../hooks';

/**
 * Administration page listing every hook known to the plugin: discovered in the source code,
 * or only known because a plugin listens to it.
 */
export default defineComponent({
  name: 'HooksCatalog',
  components: {
    ContentBlock,
  },
  props: {
    hooks: {
      type: Array as PropType<CatalogHook[]>,
      required: true,
    },
    rescanUrl: {
      type: String,
      required: true,
    },
    rescanned: {
      type: Boolean,
      default: false,
    },
    initialHook: {
      type: String,
      default: '',
    },
    logPath: {
      type: String,
      default: '',
    },
  },
  setup(props) {
    const query = ref(props.initialHook);
    const category = ref('');
    const onlyWithListeners = ref(false);
    const expanded = reactive<Record<string, boolean>>({});

    if (props.initialHook) {
      expanded[props.initialHook] = true;
    }

    const categories = computed(() => categoriesOf(props.hooks));
    const visibleHooks = computed(() => filterCatalogHooks(
      props.hooks,
      query.value,
      category.value,
      onlyWithListeners.value,
    ));
    const listenedCount = computed(
      () => props.hooks.filter((hook) => hook.listeners.length).length,
    );

    function toggle(hookName: string) {
      expanded[hookName] = !expanded[hookName];
    }

    function referenceUrl(hookName: string) {
      return `https://developer.matomo.org/api-reference/events#${developerReferenceAnchor(hookName)}`;
    }

    return {
      query,
      category,
      onlyWithListeners,
      expanded,
      categories,
      visibleHooks,
      listenedCount,
      toggle,
      referenceUrl,
      firstParagraph,
      listenSnippet,
      translate,
    };
  },
});
</script>
