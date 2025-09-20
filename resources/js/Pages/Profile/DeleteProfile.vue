<template>
  <AppLayout >
    <section class="max-w-2xl mx-auto py-6">
      <h2 class="text-600 font-bold text-2xl mb-6">Delete Profile</h2>

      <p class="mb-6 text-stone-700">
        Are you sure you want to delete your profile? This action <strong>cannot</strong> be undone.
      </p>

      <!-- Confirm Checkbox -->
      <label class="inline-flex items-center mb-6">
        <input type="checkbox" v-model="confirmDelete" class="h-4 w-4 text-red-600 border-gray-300 rounded"/>
        <span class="ml-2 text-stone-800">I confirm I want to delete my account</span>
      </label>

      <!-- Button -->
      <div class="mt-4">
        <button
          @click="deleteProfile"
          :disabled="processing"
          class="bg-amber-600 text-white px-4 py-2 rounded"
        >
          Delete profile
        </button>
      </div>

      <!-- Toast -->
      <transition name="fade">
        <div
          v-if="showToast"
          class="fixed top-5 right-5 bg-green-500 text-white px-4 py-2 rounded shadow"
        >
          {{ toastMessage }}
        </div>
      </transition>
    </section>
  </AppLayout>
</template>

<script>
import { ref } from 'vue';
import { Inertia } from '@inertiajs/inertia';
import { route } from 'ziggy-js';
import AppLayout from '@/Layouts/AppLayout.vue';

export default {
  props: {
    role: String // "patient" ou "doctor"
  },
  components: { AppLayout },
  setup(props) {
    const confirmDelete = ref(false);
    const showToast = ref(false);
    const toastMessage = ref('');
    const processing = ref(false);

    const deleteProfile = () => {
      if (!confirmDelete.value) {
        alert('You must confirm deletion by checking the box.');
        return;
      }

      if (!confirm('Are you absolutely sure you want to delete your profile?')) {
        return;
      }

      processing.value = true;

      const routeUrl = props.role === 'doctor'
        ? route('doctor.destroyProfile')
        : route('patient.destroyProfile');

      Inertia.delete(routeUrl, {
        onSuccess: () => {
          toastMessage.value = 'Your profile has been successfully deleted';
          showToast.value = true;
          setTimeout(() => showToast.value = false, 3000);

          setTimeout(() => {
            window.location.href = '/welcome';
          }, 1500);
        },
        onFinish: () => {
          processing.value = false;
        }
      });
    };

    return { confirmDelete, deleteProfile, showToast, toastMessage, processing };
  }
};
</script>

<style>
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}
</style>
