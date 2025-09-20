<template>
  <AppLayout>
    <!-- Available Slots -->
    <section class="mt-6">
      <h3 class="text-amber-600 font-bold text-xl mb-2">Available Slots</h3>
      <div class="overflow-x-auto">
        <table class="min-w-full bg-white rounded-lg shadow-md">
          <thead>
            <tr class="text-left border-b border-stone-200">
              <th class="px-4 py-2">Date</th>
              <th class="px-4 py-2">Time</th>
              <th class="px-4 py-2">Status</th>
              <th class="px-4 py-2">Action</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="slot in slots"
              :key="slot.id"
              class="border-b border-stone-100"
            >
              <td class="px-4 py-2">{{ slot.date }}</td>
              <td class="px-4 py-2">{{ slot.time }}</td>
              <td class="px-4 py-2">{{ slot.status }}</td>
              <td class="px-4 py-2">
                <button
                  class="px-3 py-1 bg-green-500 text-white rounded hover:bg-green-600"
                  @click="bookSlot(slot.id)"
                  :disabled="slot.status !== 'Available'"
                >
                  Book
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </section>

    <!-- Appointments -->
    <section class="mt-6">
      <h3 class="text-amber-600 font-bold text-xl mb-2">Appointments</h3>
      <div class="overflow-x-auto">
        <table class="min-w-full bg-white rounded-lg shadow-md">
          <thead>
            <tr class="text-left border-b border-stone-200">
              <th class="px-4 py-2">Patient</th>
              <th class="px-4 py-2">Date</th>
              <th class="px-4 py-2">Time</th>
              <th class="px-4 py-2">Status</th>
              <th class="px-4 py-2">Action</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="appointment in appointments"
              :key="appointment.id"
              class="border-b border-stone-100"
            >
              <td class="px-4 py-2">{{ appointment.patient?.name }}</td>
              <td class="px-4 py-2">{{ appointment.date }}</td>
              <td class="px-4 py-2">{{ appointment.time }}</td>
              <td class="px-4 py-2">{{ appointment.status }}</td>
              <td class="px-4 py-2">
                <button
                  class="px-3 py-1 bg-green-500 text-white rounded hover:bg-green-600 mr-2"
                  @click="acceptAppointment(appointment.id)"
                  :disabled="appointment.status !== 'Pending'"
                >
                  Accept
                </button>
                <button
                  class="px-3 py-1 bg-red-500 text-white rounded hover:bg-red-600"
                  @click="cancelAppointment(appointment.id)"
                >
                  Cancel
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </section>
  </AppLayout>
</template>

<script setup>
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

// Props envoyées par Laravel via Inertia
const props = defineProps({
  slots: Array,
  appointments: Array
})

// Methods (Inertia POST/PUT/DELETE)
const bookSlot = (id) => {
  router.post(`/slots/${id}/book`)
}

const acceptAppointment = (id) => {
  router.put(`/appointments/${id}/accept`)
}

const cancelAppointment = (id) => {
  router.delete(`/appointments/${id}/cancel`)
}
</script>
