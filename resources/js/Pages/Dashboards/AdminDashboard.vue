<template>
  <div class="min-h-screen bg-gray-100 p-6">
    <!-- Header -->
    <header class="flex justify-between items-center bg-white shadow p-4 rounded-lg">
      <h1 class="text-2xl font-bold text-gray-800">Admin Dashboard</h1>
      <button
        class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition"
      >
        Logout
      </button>
    </header>

    <!-- Content -->
    <main class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-6">
      <!-- Card: Users -->
      <div class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition">
        <h2 class="text-xl font-semibold text-gray-700 mb-2">Total Users</h2>
        <p class="text-4xl font-bold text-blue-600">{{ stats.totalUsers }}</p>
      </div>

      <!-- Card: Doctors -->
      <div class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition">
        <h2 class="text-xl font-semibold text-gray-700 mb-2">Total Doctors</h2>
        <p class="text-4xl font-bold text-green-600">{{ stats.totalDoctors }}</p>
      </div>

      <!-- Card: Patients -->
      <div class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition">
        <h2 class="text-xl font-semibold text-gray-700 mb-2">Total Patients</h2>
        <p class="text-4xl font-bold text-purple-600">{{ stats.totalPatients }}</p>
      </div>
    </main>

    <!-- Table: Latest Users -->
    <section class="mt-10 bg-white p-6 rounded-lg shadow">
      <h2 class="text-xl font-semibold text-gray-700 mb-4">Latest Registered Users</h2>
      <table class="min-w-full divide-y divide-gray-200">
        <thead>
          <tr class="bg-gray-50 text-left">
            <th class="px-4 py-2 text-sm font-medium text-gray-600">Name</th>
            <th class="px-4 py-2 text-sm font-medium text-gray-600">Email</th>
            <th class="px-4 py-2 text-sm font-medium text-gray-600">Role</th>
            <th class="px-4 py-2 text-sm font-medium text-gray-600">Date</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
          <tr v-for="(user, index) in latestUsers" :key="index">
            <td class="px-4 py-2">{{ user.name }}</td>
            <td class="px-4 py-2">{{ user.email }}</td>
            <td class="px-4 py-2">{{ user.id_role }}</td>
            <td class="px-4 py-2">{{ new Date(user.created_at).toLocaleDateString() }}</td>
          </tr>
        </tbody>
      </table>
    </section>
  </div>
</template>

<script lang="js">
export default {
  name: "AdminDashboard",
  props: {
    stats: Object,
    latestUsers: Array,
  },
};
</script>
