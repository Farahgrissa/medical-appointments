<template>
  <AppLayout>
    <section class="mt-6">
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-amber-600 font-bold text-xl">Users</h3>
        <Link
          :href="route('admin.createDoctor')"
          class="px-3 py-2 bg-amber-600 text-white rounded hover:bg-amber-700 text-sm"
        >
          Add a doctor
        </Link>
      </div>

      <!-- Filtres -->
      <form class="flex flex-wrap gap-2 mb-4" @submit.prevent="apply">
        <input
          v-model="form.search"
          type="text"
          placeholder="Search by name or email..."
          class="flex-1 min-w-48 px-4 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-amber-400"
        />
        <select v-model="form.role" class="px-4 py-2 border border-gray-300 rounded" @change="apply">
          <option value="">All roles</option>
          <option value="patient">Patients</option>
          <option value="doctor">Doctors</option>
          <option value="admin">Admins</option>
        </select>
        <select v-model="form.state" class="px-4 py-2 border border-gray-300 rounded" @change="apply">
          <option value="">All accounts</option>
          <option value="active">Active</option>
          <option value="deactivated">Deactivated</option>
        </select>
        <button type="submit" class="px-4 py-2 bg-amber-600 text-white rounded hover:bg-amber-700">Search</button>
      </form>

      <div v-if="users.data.length" class="overflow-x-auto">
        <table class="min-w-full bg-white rounded-lg shadow-md">
          <thead>
            <tr class="text-left border-b border-stone-200">
              <th class="px-4 py-2">Name</th>
              <th class="px-4 py-2">Email</th>
              <th class="px-4 py-2">Role</th>
              <th class="px-4 py-2">Specialty</th>
              <th class="px-4 py-2">Account</th>
              <th class="px-4 py-2">Action</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="user in users.data" :key="user.id" class="border-b border-stone-100">
              <td class="px-4 py-2">{{ user.name }}</td>
              <td class="px-4 py-2">{{ user.email }}</td>
              <td class="px-4 py-2 capitalize">{{ roleNames(user) }}</td>
              <td class="px-4 py-2 text-stone-600">{{ user.specialty || '—' }}</td>
              <td class="px-4 py-2">
                <span
                  :class="[
                    'inline-block px-2 py-0.5 rounded-full text-xs font-medium',
                    user.deactivated_at ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800',
                  ]"
                >
                  {{ user.deactivated_at ? 'Deactivated' : 'Active' }}
                </span>
              </td>
              <td class="px-4 py-2 whitespace-nowrap">
                <!-- Les comptes admin ne sont pas modérables depuis l'interface -->
                <template v-if="!isAdmin(user)">
                  <button
                    v-if="user.deactivated_at"
                    class="px-3 py-1 bg-green-500 text-white rounded hover:bg-green-600"
                    @click="moderate('admin.reactivateUser', user)"
                  >
                    Reactivate
                  </button>
                  <button
                    v-else
                    class="px-3 py-1 bg-red-500 text-white rounded hover:bg-red-600"
                    @click="deactivate(user)"
                  >
                    Deactivate
                  </button>
                </template>
              </td>
            </tr>
          </tbody>
        </table>
        <Pagination :links="users.links" />
      </div>

      <p v-else class="p-6 bg-white rounded-lg shadow-md text-stone-600">No user found.</p>
    </section>
  </AppLayout>
</template>

<script setup lang="js">
import { reactive } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Pagination from '@/Components/Pagination.vue'

const props = defineProps({
  users: Object, // paginator Laravel
  filters: Object,
})

const route = window.route

const form = reactive({
  search: props.filters?.search ?? '',
  role: props.filters?.role ?? '',
  state: props.filters?.state ?? '',
})

const apply = () => {
  const query = Object.fromEntries(Object.entries(form).filter(([, value]) => value))
  router.get(route('admin.users'), query, { preserveState: true, preserveScroll: true })
}

const roleNames = (user) => user.roles.map((role) => role.name).join(', ')
const isAdmin = (user) => user.roles.some((role) => role.name === 'admin')

const moderate = (routeName, user) => {
  router.patch(route(routeName, user.id), {}, { preserveScroll: true })
}

const deactivate = (user) => {
  const warning = user.roles.some((role) => role.name === 'doctor')
    ? ' Patients will no longer be able to book with this doctor. Existing appointments are kept.'
    : ''
  if (!confirm(`Deactivate ${user.name}'s account? They will no longer be able to log in.${warning}`)) {
    return
  }
  moderate('admin.deactivateUser', user)
}
</script>
