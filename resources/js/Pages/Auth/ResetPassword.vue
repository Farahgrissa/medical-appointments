<script setup lang="js">
import { useForm } from '@inertiajs/vue3'
import FormInput from '@/Components/FormInput.vue'

const props = defineProps({
  email: String,
  token: String,
})

const form = useForm({
  token: props.token,
  email: props.email ?? '',
  password: '',
  password_confirmation: '',
})

const submit = () => {
  form.post('/reset-password', {
    onFinish: () => form.reset('password', 'password_confirmation'),
  })
}
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <header class="w-full absolute top-0 left-0 flex justify-between items-center p-6 bg-transparent z-50">
      <a href="/" class="text-3xl font-extrabold text-amber-600 bg-clip-text text-transparent bg-gradient-to-r from-amber-500 via-amber-600 to-amber-700 drop-shadow-lg cursor-pointer">
        Felyora
      </a>
    </header>

    <div class="flex items-center justify-center min-h-screen">
      <div class="w-full max-w-md mt-24">
        <h2 class="text-center text-3xl font-extrabold text-gray-900 mb-8">
          Reset your password
        </h2>

        <form @submit.prevent="submit" class="space-y-6">
          <FormInput
            id="email"
            label="Email Address"
            type="email"
            v-model="form.email"
            :error="form.errors.email"
            autocomplete="email"
            required
          />
          <FormInput
            id="password"
            label="New Password"
            type="password"
            v-model="form.password"
            :error="form.errors.password"
            autocomplete="new-password"
            required
          />
          <FormInput
            id="password_confirmation"
            label="Confirm New Password"
            type="password"
            v-model="form.password_confirmation"
            :error="form.errors.password_confirmation"
            autocomplete="new-password"
            required
          />

          <button
            type="submit"
            :disabled="form.processing"
            class="w-full py-2 px-4 bg-amber-600 hover:bg-amber-700 text-white rounded-md disabled:opacity-50"
          >
            <span v-if="form.processing">Resetting...</span>
            <span v-else>Reset password</span>
          </button>
        </form>
      </div>
    </div>
  </div>
</template>
