<template>
  <AppLayout>
    <section class="mt-6 max-w-4xl">
      <h3 class="text-amber-600 font-bold text-xl mb-4">Find a doctor</h3>

      <form class="flex gap-2 mb-4" @submit.prevent="search">
        <input
          v-model="query"
          type="text"
          placeholder="Search by name or specialty..."
          class="flex-1 px-4 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-amber-400"
        />
        <button type="submit" class="px-4 py-2 bg-amber-600 text-white rounded hover:bg-amber-700">
          Search
        </button>
      </form>

      <div v-if="doctors.length" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div
          v-for="doctor in doctors"
          :key="doctor.id"
          class="p-4 bg-white rounded-lg shadow-md flex justify-between items-center"
        >
          <div>
            <p class="font-semibold">{{ doctor.name }}</p>
            <p v-if="doctor.specialty" class="text-sm text-stone-700">{{ doctor.specialty }}</p>
            <p class="text-sm text-stone-600">
              {{ doctor.available_slots_count }} available slot{{ doctor.available_slots_count === 1 ? '' : 's' }}
            </p>
          </div>
          <Link
            v-if="doctor.available_slots_count"
            :href="route('patient.doctorSlots', doctor.id)"
            class="px-3 py-2 bg-amber-600 text-white rounded hover:bg-amber-700 text-sm"
          >
            See slots
          </Link>
          <span v-else class="text-sm text-stone-400">Fully booked</span>
        </div>
      </div>

      <p v-else class="p-6 bg-white rounded-lg shadow-md text-stone-600">No doctor found.</p>
    </section>
  </AppLayout>
</template>

<script setup lang="js">
import { ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
  doctors: { type: Array, default: () => [] },
  filters: Object,
})

const route = window.route
const query = ref(props.filters?.search ?? '')

const search = () => {
  router.get(route('patient.doctors'), { search: query.value || undefined }, { preserveState: true })
}
</script>
