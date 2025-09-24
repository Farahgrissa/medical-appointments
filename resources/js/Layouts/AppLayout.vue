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
        <slot />
      </main>
    </div>
  </div>
</template>

<script lang="js">
import appHeader from '@/Components/appHeader.vue';
import SidebarMenu from '@/Components/SidebarMenu.vue';

export default {
  components: {
    appHeader,
 
    SidebarMenu,
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
