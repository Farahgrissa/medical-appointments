<!-- Filtres des listes de rendez-vous : recherche, période, statut -->
<template>
  <form class="flex flex-wrap gap-2 mb-4" @submit.prevent="apply">
    <input
      v-model="form.search"
      type="text"
      :placeholder="searchPlaceholder"
      class="flex-1 min-w-48 px-4 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-amber-400"
    />
    <select v-model="form.period" class="px-4 py-2 border border-gray-300 rounded" @change="apply">
      <option value="">All dates</option>
      <option value="upcoming">Upcoming</option>
      <option value="past">Past</option>
    </select>
    <select v-model="form.status" class="px-4 py-2 border border-gray-300 rounded" @change="apply">
      <option value="">All statuses</option>
      <option v-for="status in statuses" :key="status" :value="status">{{ statusLabel(status) }}</option>
    </select>
    <button type="submit" class="px-4 py-2 bg-amber-600 text-white rounded hover:bg-amber-700">Search</button>
  </form>
</template>

<script setup lang="js">
import { reactive } from 'vue'
import { router } from '@inertiajs/vue3'
import { statusLabel } from '@/utils/appointments'

const props = defineProps({
  url: String,
  filters: Object,
  statuses: { type: Array, default: () => [] },
  searchPlaceholder: { type: String, default: 'Search...' },
})

const form = reactive({
  search: props.filters?.search ?? '',
  period: props.filters?.period ?? '',
  status: props.filters?.status ?? '',
})

const apply = () => {
  // Les filtres vides ne sont pas envoyés dans l'URL
  const query = Object.fromEntries(Object.entries(form).filter(([, value]) => value))
  router.get(props.url, query, { preserveState: true, preserveScroll: true })
}
</script>
