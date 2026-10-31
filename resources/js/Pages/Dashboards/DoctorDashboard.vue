<template>
  <AppLayout>
    <!-- Appointments -->
    <section class="mt-6">
      <h3 class="text-amber-600 font-bold text-xl mb-2">Upcoming Appointments</h3>

      <div v-if="appointments.length" class="overflow-x-auto">
        <table class="min-w-full bg-white rounded-lg shadow-md">
          <thead>
            <tr class="text-left border-b border-stone-200">
              <th class="px-4 py-2">Patient</th>
              <th class="px-4 py-2">Date</th>
              <th class="px-4 py-2">Time</th>
              <th class="px-4 py-2">Reason</th>
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
              <td class="px-4 py-2">{{ formatDate(appointment.appointment_date) }}</td>
              <td class="px-4 py-2">{{ formatRange(appointment.start_time, appointment.end_time) }}</td>
              <td class="px-4 py-2 text-stone-600">{{ appointment.reason || '—' }}</td>
              <td class="px-4 py-2"><StatusBadge :status="appointment.status" /></td>
              <td class="px-4 py-2 whitespace-nowrap">
                <button
                  v-if="appointment.status === 'pending'"
                  class="px-3 py-1 bg-green-500 text-white rounded hover:bg-green-600 mr-2"
                  @click="confirmAppointment(appointment)"
                >
                  Accept
                </button>
                <button
                  class="px-3 py-1 bg-red-500 text-white rounded hover:bg-red-600"
                  @click="cancelAppointment(appointment)"
                >
                  Cancel
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <p v-else class="p-6 bg-white rounded-lg shadow-md text-stone-600">
        No upcoming appointments.
      </p>
    </section>

    <!-- Available Slots -->
    <section class="mt-6">
      <div class="flex justify-between items-center mb-2">
        <h3 class="text-amber-600 font-bold text-xl">Available Slots</h3>
        <Link
          :href="route('doctor.slots')"
          class="px-3 py-2 bg-amber-600 text-white rounded hover:bg-amber-700 text-sm"
        >
          Manage my slots
        </Link>
      </div>

      <div v-if="availableSlots.length" class="overflow-x-auto">
        <table class="min-w-full bg-white rounded-lg shadow-md">
          <thead>
            <tr class="text-left border-b border-stone-200">
              <th class="px-4 py-2">Date</th>
              <th class="px-4 py-2">Time</th>
              <th class="px-4 py-2">Type</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="slot in availableSlots" :key="slot.id" class="border-b border-stone-100">
              <td class="px-4 py-2">{{ formatDate(slot.date) }}</td>
              <td class="px-4 py-2">{{ formatRange(slot.start_time, slot.end_time) }}</td>
              <td class="px-4 py-2 capitalize">{{ slot.slot_type }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <p v-else class="p-6 bg-white rounded-lg shadow-md text-stone-600">
        No free slots. Add some so patients can book appointments.
      </p>
    </section>
  </AppLayout>
</template>

<script setup lang="js">
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import StatusBadge from '@/Components/StatusBadge.vue'
import { formatDate, formatRange } from '@/utils/appointments'

// Props envoyées par DoctorController@dashboard
defineProps({
  appointments: { type: Array, default: () => [] },
  availableSlots: { type: Array, default: () => [] },
})

const route = window.route

const confirmAppointment = (appointment) => {
  router.patch(route('doctor.confirmAppointment', appointment.id), {}, { preserveScroll: true })
}

const cancelAppointment = (appointment) => {
  if (!confirm(`Cancel the appointment with ${appointment.patient?.name}?`)) {
    return
  }
  router.patch(route('doctor.cancelAppointment', appointment.id), {}, { preserveScroll: true })
}
</script>
