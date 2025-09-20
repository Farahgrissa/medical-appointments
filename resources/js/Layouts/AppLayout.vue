<template>
  <div class="flex min-h-screen bg-stone-50">
    <!-- Sidebar -->
    <SidebarMenu class="w-64 flex-shrink-0" />

    <!-- Right Side: Header + Page Content -->
    <div class="flex-1 flex flex-col">
      <!-- Header -->
      <Header />

      <!-- Page Content -->
      <main class="flex-1 p-6">
        <slot />
      </main>
    </div>
  </div>
</template>

<script>
import Header from '@/Components/Header.vue';
import SidebarMenu from '@/Components/SidebarMenu.vue';

export default {
  components: {
    Header,
 
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
