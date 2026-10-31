<template>
  <AppLayout>
    <section class="mt-6 max-w-4xl">
      <h3 class="text-amber-600 font-bold text-xl mb-4">My slots</h3>

      <!-- Nouveau créneau -->
      <form class="p-4 bg-white rounded-lg shadow-md grid grid-cols-1 sm:grid-cols-5 gap-4 items-end mb-6" @submit.prevent="submit">
        <div>
          <label for="date" class="block text-sm font-medium text-gray-700">Date</label>
          <input id="date" v-model="form.date" type="date" :min="today" required class="mt-1 w-full border rounded px-2 py-1" />
        </div>
        <div>
          <label for="start_time" class="block text-sm font-medium text-gray-700">Start</label>
          <input id="start_time" v-model="form.start_time" type="time" required class="mt-1 w-full border rounded px-2 py-1" />
        </div>
        <div>
          <label for="end_time" class="block text-sm font-medium text-gray-700">End</label>
          <input id="end_time" v-model="form.end_time" type="time" required class="mt-1 w-full border rounded px-2 py-1" />
        </div>
        <div>
          <label for="slot_type" class="block text-sm font-medium text-gray-700">Type</label>
          <select id="slot_type" v-model="form.slot_type" class="mt-1 w-full border rounded px-2 py-1 capitalize">
            <option v-for="type in slotTypes" :key="type" :value="type">{{ type }}</option>
          </select>
        </div>
        <button
          type="submit"
          :disabled="form.processing"
          class="py-2 px-4 bg-amber-600 hover:bg-amber-700 text-white rounded-md disabled:opacity-50"
        >
          Add slot
        </button>

        <div v-if="hasErrors" class="sm:col-span-5 text-red-600 text-sm space-y-1">
          <p v-for="(error, field) in form.errors" :key="field">{{ error }}</p>
        </div>
      </form>

      <!-- Liste -->
      <div v-if="slots.length" class="overflow-x-auto">
        <table class="min-w-full bg-white rounded-lg shadow-md">
          <thead>
            <tr class="text-left border-b border-stone-200">
              <th class="px-4 py-2">Date</th>
              <th class="px-4 py-2">Time</th>
              <th class="px-4 py-2">Type</th>
              <th class="px-4 py-2">Status</th>
              <th class="px-4 py-2">Patient</th>
              <th class="px-4 py-2">Action</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="slot in slots" :key="slot.id" class="border-b border-stone-100">
              <td class="px-4 py-2">{{ formatDate(slot.date) }}</td>
              <td class="px-4 py-2">{{ formatRange(slot.start_time, slot.end_time) }}</td>
              <td class="px-4 py-2 capitalize">{{ slot.slot_type }}</td>
              <td class="px-4 py-2"><StatusBadge :status="slot.status" /></td>
              <td class="px-4 py-2">{{ slot.appointments[0]?.patient?.name ?? '—' }}</td>
              <td class="px-4 py-2">
                <button
                  v-if="slot.status === 'available'"
                  class="px-3 py-1 bg-red-500 text-white rounded hover:bg-red-600"
                  @click="destroy(slot)"
                >
                  Delete
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <p v-else class="p-6 bg-white rounded-lg shadow-md text-stone-600">
        You have no upcoming slots yet.
      </p>
    </section>
  </AppLayout>
</template>

<script setup lang="js">
import { computed } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import StatusBadge from '@/Components/StatusBadge.vue'
import { formatDate, formatRange } from '@/utils/appointments'

const props = defineProps({
  slots: { type: Array, default: () => [] },
  slotTypes: { type: Array, default: () => [] },
})

const route = window.route
const today = new Date().toLocaleDateString('en-CA') // YYYY-MM-DD, heure locale

const form = useForm({
  date: today,
  start_time: '09:00',
  end_time: '09:30',
  slot_type: props.slotTypes[0] ?? 'consultation',
})

const hasErrors = computed(() => Object.keys(form.errors).length > 0)

const submit = () => {
  form.post(route('doctor.storeSlot'), { preserveScroll: true })
}

const destroy = (slot) => {
  if (!confirm(`Delete the slot of ${formatDate(slot.date)} at ${formatRange(slot.start_time, slot.end_time)}?`)) {
    return
  }
  router.delete(route('doctor.destroySlot', slot.id), { preserveScroll: true })
}
</script>
