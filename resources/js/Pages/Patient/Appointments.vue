<template>
  <AppLayout>
    <section class="mt-6">
      <h3 class="text-amber-600 font-bold text-xl mb-4">My appointments</h3>

      <AppointmentFilters
        :url="route('patient.appointments')"
        :filters="filters"
        :statuses="statuses"
        search-placeholder="Search by doctor..."
      />

      <div v-if="appointments.data.length" class="overflow-x-auto">
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
            <tr v-for="appointment in appointments.data" :key="appointment.id" class="border-b border-stone-100">
              <td class="px-4 py-2">
                {{ appointment.doctor?.name }}
                <span v-if="appointment.doctor?.specialty" class="block text-xs text-stone-500">{{ appointment.doctor.specialty }}</span>
              </td>
              <td class="px-4 py-2">{{ formatDate(appointment.appointment_date) }}</td>
              <td class="px-4 py-2">{{ formatRange(appointment.start_time, appointment.end_time) }}</td>
              <td class="px-4 py-2 text-stone-600">{{ appointment.reason || '—' }}</td>
              <td class="px-4 py-2"><StatusBadge :status="appointment.status" /></td>
              <td class="px-4 py-2 whitespace-nowrap">
                <!-- Actions possibles seulement avant le début du rendez-vous -->
                <template v-if="isOpen(appointment) && !appointment.has_started">
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
                </template>
              </td>
            </tr>
          </tbody>
        </table>
        <Pagination :links="appointments.links" />
      </div>

      <p v-else class="p-6 bg-white rounded-lg shadow-md text-stone-600">No appointment found.</p>
    </section>
  </AppLayout>
</template>

<script setup lang="js">
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import AppointmentFilters from '@/Components/AppointmentFilters.vue'
import Pagination from '@/Components/Pagination.vue'
import StatusBadge from '@/Components/StatusBadge.vue'
import { formatDate, formatRange, isOpen } from '@/utils/appointments'

defineProps({
  appointments: Object, // paginator Laravel : { data, links, ... }
  filters: Object,
  statuses: { type: Array, default: () => [] },
})

const route = window.route

const cancelAppointment = (appointment) => {
  if (!confirm(`Cancel your appointment with ${appointment.doctor?.name} on ${formatDate(appointment.appointment_date)}?`)) {
    return
  }
  router.patch(route('patient.cancelAppointment', appointment.id), {}, { preserveScroll: true })
}
</script>
