<template>
  <AppLayout>
    <section class="mt-6">
      <h3 class="text-amber-600 font-bold text-xl mb-4">Appointments</h3>

      <AppointmentFilters
        :url="route('doctor.appointments')"
        :filters="filters"
        :statuses="statuses"
        search-placeholder="Search by patient..."
      />

      <div v-if="appointments.data.length" class="overflow-x-auto">
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
            <tr v-for="appointment in appointments.data" :key="appointment.id" class="border-b border-stone-100">
              <td class="px-4 py-2">
                {{ appointment.patient?.name }}
                <span v-if="appointment.patient?.deleted_at" class="block text-xs text-stone-500">Account deleted</span>
              </td>
              <td class="px-4 py-2">{{ formatDate(appointment.appointment_date) }}</td>
              <td class="px-4 py-2">{{ formatRange(appointment.start_time, appointment.end_time) }}</td>
              <td class="px-4 py-2 text-stone-600">{{ appointment.reason || '—' }}</td>
              <td class="px-4 py-2"><StatusBadge :status="appointment.status" /></td>
              <td class="px-4 py-2 whitespace-nowrap">
                <!-- Avant le rendez-vous : accepter / annuler -->
                <template v-if="isOpen(appointment) && !appointment.has_started">
                  <button
                    v-if="appointment.status === 'pending'"
                    class="px-3 py-1 bg-green-500 text-white rounded hover:bg-green-600 mr-2"
                    @click="act('doctor.confirmAppointment', appointment)"
                  >
                    Accept
                  </button>
                  <button
                    class="px-3 py-1 bg-red-500 text-white rounded hover:bg-red-600"
                    @click="cancel(appointment)"
                  >
                    Cancel
                  </button>
                </template>

                <!-- Après le début : clôturer -->
                <template v-else-if="isOpen(appointment)">
                  <button
                    class="px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-600 mr-2"
                    @click="act('doctor.completeAppointment', appointment)"
                  >
                    Completed
                  </button>
                  <button
                    class="px-3 py-1 bg-stone-500 text-white rounded hover:bg-stone-600"
                    @click="act('doctor.noShowAppointment', appointment)"
                  >
                    No-show
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
import { router } from '@inertiajs/vue3'
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

const act = (routeName, appointment) => {
  router.patch(route(routeName, appointment.id), {}, { preserveScroll: true })
}

const cancel = (appointment) => {
  if (!confirm(`Cancel the appointment with ${appointment.patient?.name}?`)) {
    return
  }
  act('doctor.cancelAppointment', appointment)
}
</script>
