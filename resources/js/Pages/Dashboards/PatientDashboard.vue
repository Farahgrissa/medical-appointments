<template>
  <AppLayout>
    <!-- Upcoming Appointments -->
    <section class="mt-6">
      <h3 class="text-amber-600 font-bold text-xl mb-2">Upcoming Appointments</h3>
      <div class="overflow-x-auto">
        <table class="min-w-full bg-white rounded-lg shadow-md">
          <thead>
            <tr class="text-left border-b border-stone-200">
              <th class="px-4 py-2">Doctor</th>
              <th class="px-4 py-2">Date</th>
              <th class="px-4 py-2">Status</th>
              <th class="px-4 py-2">Action</th>
            </tr>
          </thead>
          <tbody>
            <tr class="border-b border-stone-100">
              <td class="px-4 py-2">Dr.John</td>
              <td class="px-4 py-2">2025-09-01</td>
              <td class="px-4 py-2">Confirmed</td>
              <td class="px-4 py-2">
                <button class="px-3 py-1 bg-red-500 text-white rounded hover:bg-red-600">
                  Cancel
                </button>
              </td>
            </tr>

            <tr class="border-b border-stone-100">
              <td class="px-4 py-2">Dr.Eline</td>
              <td class="px-4 py-2">2025-09-10</td>
              <td class="px-4 py-2">Pending</td>
              <td class="px-4 py-2">
                <button class="px-3 py-1 bg-red-500 text-white rounded hover:bg-red-600">
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
import AppLayout from '@/Layouts/AppLayout.vue';
</script>
