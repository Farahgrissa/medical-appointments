<!-- resources/js/Pages/Patients/Profile.vue -->
<template>
  <AppLayout>
    <!-- Page Content -->
    <section class="max-w-4xl mx-auto py-6">
      <h2 class="text-amber-600 font-bold text-2xl mb-6">Patient Profile</h2>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-6 gap-x-10">
        <!-- Full Name -->
        <div>
          <span class="block text-stone-600 font-medium">Full Name</span>
          <p class="text-stone-900">{{ patient.fullName || 'Patient X' }}</p>
        </div>

        <!-- Email -->
        <div>
          <span class="block text-stone-600 font-medium">Email</span>
          <p class="text-stone-900">{{ patient.email || 'patient@test.com' }}</p>
        </div>

        <!-- Address (sous Email) -->
        <div class="sm:col-span-2">
          <span class="block text-stone-600 font-medium">Address</span>
          <p class="text-stone-900">{{ patient.address || 'Not specified' }}</p>
        </div>

        <!-- Gender -->
        <div>
          <span class="block text-stone-600 font-medium">Gender</span>
          <p class="text-stone-900">{{ patient.gender || 'Not specified' }}</p>
        </div>

        <!-- Password -->
        <div>
          <span class="block text-stone-600 font-medium">Password</span>
          <div class="flex items-center">
            <input
              :type="showPassword ? 'text' : 'password'"
              class="border rounded px-2 py-1 w-full"
              :value="patient.password || '********'"
              readonly
            />
            <button
              type="button"
              class="ml-2 px-2 py-1 text-white bg-amber-600 rounded"
              @click="showPassword = !showPassword"
            >
              {{ showPassword ? 'Hide' : 'Show' }}
            </button>
          </div>
        </div>

        <!-- Phone -->
        <div>
          <span class="block text-stone-600 font-medium">Phone</span>
          <p class="text-stone-900">{{ patient.phone || '+216 99 000 000' }}</p>
        </div>
      </div>
    </section>
  </AppLayout>
</template>

<script>
import { ref } from 'vue';
import { usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

export default {
  components: { AppLayout },
  setup() {
    const page = usePage();
    const patient = ref(page.props.value?.auth?.user ?? {});
    
    patient.value.address = patient.value.address || '';
    patient.value.gender = patient.value.gender || '';
    patient.value.password = patient.value.password || ''; // juste pour affichage, ne jamais stocker en clair en vrai
    
    const showPassword = ref(false);

    return { patient, showPassword };
  }
};
</script>

<style scoped>
/* Si tu veux, tu peux ajouter du style supplémentaire ici */
</style>
