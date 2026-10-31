<template>
  <AppLayout>
    <!-- Upcoming Appointments -->
    <section class="mt-6">
      <div class="flex justify-between items-center mb-2">
        <h3 class="text-amber-600 font-bold text-xl">Upcoming Appointments</h3>
        <Link
          :href="route('patient.doctors')"
          class="px-3 py-2 bg-amber-600 text-white rounded hover:bg-amber-700 text-sm"
        >
          Book an appointment
        </Link>
      </div>

      <div v-if="appointments.length" class="overflow-x-auto">
        <table class="min-w-full bg-white rounded-lg shadow-md">
          <thead>
            <tr class="text-left border-b border-stone-200">
              <th class="px-4 py-2">Doctor</th>
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
              <td class="px-4 py-2">{{ appointment.doctor?.name }}</td>
              <td class="px-4 py-2">{{ formatDate(appointment.appointment_date) }}</td>
              <td class="px-4 py-2">{{ formatRange(appointment.start_time, appointment.end_time) }}</td>
              <td class="px-4 py-2 text-stone-600">{{ appointment.reason || '—' }}</td>
              <td class="px-4 py-2"><StatusBadge :status="appointment.status" /></td>
              <td class="px-4 py-2 whitespace-nowrap">
                <Link
                  :href="route('patient.editAppointment', appointment.id)"
                  class="px-3 py-1 bg-stone-200 rounded hover:bg-stone-300 mr-2"
                >
                  Reschedule
                </Link>
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
        You have no upcoming appointments.
      </p>
    </section>
  </AppLayout>
</template>

<script setup lang="js">
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import StatusBadge from '@/Components/StatusBadge.vue'
import { formatDate, formatRange } from '@/utils/appointments'

defineProps({
  appointments: { type: Array, default: () => [] },
  filters: Object,
})

const route = window.route

const cancelAppointment = (appointment) => {
  if (!confirm(`Cancel your appointment with ${appointment.doctor?.name} on ${formatDate(appointment.appointment_date)}?`)) {
    return
  }
  router.patch(route('patient.cancelAppointment', appointment.id), {}, { preserveScroll: true })
}
</script>
