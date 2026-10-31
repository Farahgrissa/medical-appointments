<template>
  <AppLayout>
    <section class="max-w-4xl mx-auto py-6 space-y-8">
      <!-- Informations -->
      <div>
        <h2 class="text-amber-600 font-bold text-2xl mb-4">My profile</h2>

        <form class="grid grid-cols-1 sm:grid-cols-2 gap-y-4 gap-x-6 p-4 bg-white rounded-lg shadow-md" @submit.prevent="updateProfile">
          <div>
            <label class="block text-stone-600 font-medium">Full Name</label>
            <input v-model="profile.name" type="text" required class="mt-1 w-full border rounded px-2 py-1" />
            <p v-if="profile.errors.name" class="text-red-600 text-sm mt-1">{{ profile.errors.name }}</p>
          </div>

          <div>
            <label class="block text-stone-600 font-medium">Email</label>
            <input :value="user.email" type="email" disabled class="mt-1 w-full border rounded px-2 py-1 bg-stone-100 text-stone-500" />
          </div>

          <div>
            <label class="block text-stone-600 font-medium">Phone</label>
            <input v-model="profile.phone" type="text" class="mt-1 w-full border rounded px-2 py-1" />
            <p v-if="profile.errors.phone" class="text-red-600 text-sm mt-1">{{ profile.errors.phone }}</p>
          </div>

          <div>
            <label class="block text-stone-600 font-medium">Date of Birth</label>
            <input v-model="profile.date_of_birth" type="date" class="mt-1 w-full border rounded px-2 py-1" />
            <p v-if="profile.errors.date_of_birth" class="text-red-600 text-sm mt-1">{{ profile.errors.date_of_birth }}</p>
          </div>

          <div class="sm:col-span-2">
            <label class="block text-stone-600 font-medium">Specialty</label>
            <input :value="user.specialty || '—'" type="text" disabled class="mt-1 w-full border rounded px-2 py-1 bg-stone-100 text-stone-500" />
            <p class="text-xs text-stone-500 mt-1">Your specialty is managed by the clinic administrator.</p>
          </div>

          <div class="sm:col-span-2">
            <button type="submit" :disabled="profile.processing" class="bg-amber-600 text-white px-4 py-2 rounded disabled:opacity-50">
              Update Profile
            </button>
          </div>
        </form>
      </div>

      <!-- Mot de passe -->
      <div>
        <h3 class="text-amber-600 font-bold text-xl mb-4">Change password</h3>

        <form class="grid grid-cols-1 sm:grid-cols-3 gap-4 p-4 bg-white rounded-lg shadow-md" @submit.prevent="updatePassword">
          <div>
            <label class="block text-stone-600 font-medium">Current Password</label>
            <input v-model="password.current_password" type="password" autocomplete="current-password" required class="mt-1 w-full border rounded px-2 py-1" />
            <p v-if="password.errors.current_password" class="text-red-600 text-sm mt-1">{{ password.errors.current_password }}</p>
          </div>
          <div>
            <label class="block text-stone-600 font-medium">New Password</label>
            <input v-model="password.password" type="password" autocomplete="new-password" required class="mt-1 w-full border rounded px-2 py-1" />
            <p v-if="password.errors.password" class="text-red-600 text-sm mt-1">{{ password.errors.password }}</p>
          </div>
          <div>
            <label class="block text-stone-600 font-medium">Confirm New Password</label>
            <input v-model="password.password_confirmation" type="password" autocomplete="new-password" required class="mt-1 w-full border rounded px-2 py-1" />
          </div>
          <div class="sm:col-span-3">
            <button type="submit" :disabled="password.processing" class="bg-amber-600 text-white px-4 py-2 rounded disabled:opacity-50">
              Change password
            </button>
            <span v-if="password.recentlySuccessful" class="ml-3 text-sm text-green-700">Password changed.</span>
          </div>
        </form>
      </div>

      <!-- Suppression -->
      <div class="p-4 bg-white rounded-lg shadow-md border border-red-200">
        <h3 class="text-red-600 font-bold text-lg mb-2">Delete my account</h3>
        <p class="text-sm text-stone-600 mb-3">
          Your upcoming appointments will be cancelled and your free slots withdrawn.
          Past appointments are kept in the patients' history.
        </p>
        <button class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600" @click="deleteAccount">
          Delete my account
        </button>
      </div>
    </section>
  </AppLayout>
</template>

<script setup lang="js">
import { router, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
  user: Object,
})

const route = window.route

const profile = useForm({
  name: props.user.name ?? '',
  phone: props.user.phone ?? '',
  date_of_birth: props.user.date_of_birth ?? '',
})

const password = useForm({
  current_password: '',
  password: '',
  password_confirmation: '',
})

const updateProfile = () => {
  profile.put(route('doctor.updateProfile'), { preserveScroll: true })
}

const updatePassword = () => {
  password.put(route('password.update'), {
    preserveScroll: true,
    onFinish: () => password.reset(),
  })
}

const deleteAccount = () => {
  if (!confirm('Delete your account? This cannot be undone.')) {
    return
  }
  router.delete(route('doctor.destroyProfile'))
}
</script>
