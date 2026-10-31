<template>
  <div class="flex min-h-screen bg-stone-50">
    <!-- Sidebar -->
    <SidebarMenu class="w-64 flex-shrink-0" />

    <!-- Right Side: Header + Page Content -->
    <div class="flex-1 flex flex-col">
      <!-- Header -->
      <appHeader />

      <!-- Page Content -->
      <main class="flex-1 p-6">
        <!-- Flash messages (->with('success'|'error') côté Laravel) -->
        <div
          v-if="flash.success"
          class="mb-4 p-3 rounded bg-green-50 border border-green-200 text-green-700 text-sm"
        >
          {{ flash.success }}
        </div>
        <div
          v-if="flash.error"
          class="mb-4 p-3 rounded bg-red-50 border border-red-200 text-red-700 text-sm"
        >
          {{ flash.error }}
        </div>

        <slot />
      </main>
    </div>
  </div>
</template>

<script lang="js">
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import appHeader from '@/Components/appHeader.vue';
import SidebarMenu from '@/Components/SidebarMenu.vue';

export default {
  components: {
    appHeader,
    SidebarMenu,
  },
  setup() {
    const page = usePage();
    const flash = computed(() => page.props.flash ?? {});

    return { flash };
  },
};
</script>

<style scoped>
/* Sticky header */
header {
  position: sticky;
  top: 0;
  z-index: 50;
}
</style>
