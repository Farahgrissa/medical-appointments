<template>
  <AppLayout >
    <section class="max-w-4xl mx-auto py-6">
      <h2 class="text-amber-600 font-bold text-2xl mb-6">Edit Profile</h2>

      <form @submit.prevent="updateProfile" class="grid grid-cols-1 sm:grid-cols-2 gap-y-4 gap-x-6">
        <!-- Name -->
        <div>
          <label class="block text-stone-600 font-medium">Full Name</label>
          <input v-model="form.name" type="text" class="mt-1 w-full border rounded px-2 py-1" />
        </div>

        <!-- Phone -->
        <div>
          <label class="block text-stone-600 font-medium">Phone</label>
          <input v-model="form.phone" type="text" class="mt-1 w-full border rounded px-2 py-1" />
        </div>

        <!-- Gender -->
        <div>
          <label class="block text-stone-600 font-medium">Gender</label>
          <select v-model="form.gender" class="mt-1 w-full border rounded px-2 py-1">
            <option value="">Select Gender</option>
            <option value="male">Male</option>
            <option value="female">Female</option>
          </select>
        </div>

        <!-- Date of Birth -->
        <div>
          <label class="block text-stone-600 font-medium">Date of Birth</label>
          <input v-model="form.date_of_birth" type="date" class="mt-1 w-full border rounded px-2 py-1" />
        </div>

        <!-- Address -->
        <div class="sm:col-span-2">
          <label class="block text-stone-600 font-medium">Address</label>
          <input v-model="form.address" type="text" class="mt-1 w-full border rounded px-2 py-1" />
        </div>

        <!-- Password -->
        <div class="sm:col-span-2">
          <label class="block text-stone-600 font-medium">Password</label>
          <input v-model="form.password" type="password" placeholder="Leave empty to keep current" class="mt-1 w-full border rounded px-2 py-1" />
        </div>

        <div class="sm:col-span-2">
          <button type="submit" class="bg-amber-600 text-white px-4 py-2 rounded">Update Profile</button>
        </div>
      </form>
    </section>
  </AppLayout>
</template>

<script lang="js">
import { ref } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

export default {
  components: { AppLayout },
  setup() {
    const page = usePage();
    const user = ref(page.props.value?.auth?.user || {});

    // Initialiser le formulaire avec les valeurs actuelles
    const form = ref({
      name: user.value.name || '',
      phone: user.value.phone || '',
      gender: user.value.gender || '',
      date_of_birth: user.value.date_of_birth || '',
      address: user.value.address || '',
      password: ''
    });


    const updateProfile = () => {
  router.put(route('patient.updateProfile'), form.value); 
};


    return { form, updateProfile };
  }
};
</script>
