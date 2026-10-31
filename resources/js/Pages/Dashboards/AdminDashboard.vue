<template>
  <AppLayout>
    <section class="mt-6 space-y-6">
      <div class="flex justify-between items-center">
        <h2 class="text-amber-600 font-bold text-2xl">Admin dashboard</h2>
        <Link
          :href="route('admin.createDoctor')"
          class="px-3 py-2 bg-amber-600 text-white rounded hover:bg-amber-700 text-sm"
        >
          Add a doctor
        </Link>
      </div>

      <!-- Indicateurs -->
      <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
        <div v-for="tile in tiles" :key="tile.label" class="bg-white p-4 rounded-lg shadow">
          <p class="text-sm text-stone-500">{{ tile.label }}</p>
          <p class="text-3xl font-bold text-stone-800 mt-1 tabular-nums">{{ tile.value }}</p>
          <p v-if="tile.hint" class="text-xs text-stone-500 mt-1">{{ tile.hint }}</p>
        </div>
      </div>

      <!-- Rendez-vous par statut -->
      <div class="bg-white p-6 rounded-lg shadow">
        <h3 class="text-lg font-semibold text-stone-700 mb-4">Appointments by status</h3>

        <ul v-if="stats.appointments" class="space-y-3">
          <li
            v-for="row in stats.byStatus"
            :key="row.status"
            class="grid grid-cols-[7rem_1fr_5rem] items-center gap-3"
            :title="`${row.label}: ${row.total} (${share(row.total)}%)`"
          >
            <span class="text-sm text-stone-700">{{ row.label }}</span>
            <div class="h-3 bg-stone-100 rounded">
              <div class="h-3 bg-amber-600 rounded" :style="{ width: barWidth(row.total) }" />
            </div>
            <span class="text-sm text-stone-600 text-right tabular-nums">
              {{ row.total }} <span class="text-stone-400">· {{ share(row.total) }}%</span>
            </span>
          </li>
        </ul>
        <p v-else class="text-stone-500">No appointments yet.</p>
      </div>

      <!-- Derniers inscrits -->
      <div class="bg-white p-6 rounded-lg shadow">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-lg font-semibold text-stone-700">Latest registered users</h3>
          <Link :href="route('admin.users')" class="text-sm text-amber-600 hover:text-amber-500">All users &rarr;</Link>
        </div>
        <table class="min-w-full divide-y divide-gray-200">
          <thead>
            <tr class="bg-gray-50 text-left">
              <th class="px-4 py-2 text-sm font-medium text-gray-600">Name</th>
              <th class="px-4 py-2 text-sm font-medium text-gray-600">Email</th>
              <th class="px-4 py-2 text-sm font-medium text-gray-600">Role</th>
              <th class="px-4 py-2 text-sm font-medium text-gray-600">Registered</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <tr v-for="user in latestUsers" :key="user.id">
              <td class="px-4 py-2">
                {{ user.name }}
                <span v-if="user.deactivated_at" class="ml-1 text-xs text-red-600">(deactivated)</span>
              </td>
              <td class="px-4 py-2">{{ user.email }}</td>
              <td class="px-4 py-2 capitalize">{{ user.roles.map((role) => role.name).join(', ') }}</td>
              <td class="px-4 py-2">{{ new Date(user.created_at).toLocaleDateString() }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </section>
  </AppLayout>
</template>

<script setup lang="js">
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
  stats: Object,
  latestUsers: { type: Array, default: () => [] },
})

const route = window.route

const rate = (value) => (value === null ? '—' : `${value}%`)

const tiles = computed(() => [
  { label: 'Patients', value: props.stats.patients },
  { label: 'Doctors', value: props.stats.doctors },
  { label: 'Appointments', value: props.stats.appointments },
  { label: 'Upcoming', value: props.stats.upcomingAppointments, hint: 'Pending or confirmed' },
  { label: 'Cancellation rate', value: rate(props.stats.cancellationRate), hint: 'Of all appointments' },
  { label: 'No-show rate', value: rate(props.stats.noShowRate), hint: 'Of past appointments' },
])

const max = computed(() => Math.max(1, ...props.stats.byStatus.map((row) => row.total)))

// Une barre vide garde une largeur minimale pour rester visible
const barWidth = (total) => (total ? `${Math.max(2, (total / max.value) * 100)}%` : '0')

const share = (total) => (props.stats.appointments ? Math.round((total / props.stats.appointments) * 100) : 0)
</script>
