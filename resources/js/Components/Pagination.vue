<!-- Liens d'un paginator Laravel (->paginate()) -->
<template>
  <nav v-if="links.length > 3" class="flex flex-wrap gap-1 mt-4">
    <template v-for="(link, index) in links" :key="index">
      <span
        v-if="!link.url"
        class="px-3 py-1 rounded border border-stone-200 text-stone-400 text-sm"
        v-html="link.label"
      />
      <Link
        v-else
        :href="link.url"
        preserve-scroll
        :class="[
          'px-3 py-1 rounded border text-sm',
          link.active ? 'bg-amber-600 text-white border-amber-600' : 'bg-white border-stone-300 hover:border-amber-500',
        ]"
        v-html="link.label"
      />
    </template>
  </nav>
</template>

<script setup lang="js">
import { Link } from '@inertiajs/vue3'

defineProps({
  links: { type: Array, default: () => [] },
})
</script>
