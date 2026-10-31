<script setup lang="js">
import { useForm } from '@inertiajs/vue3'
import FormInput from '@/Components/FormInput.vue'

defineProps({
  status: String,
})

const form = useForm({
  email: '',
})

const submit = () => {
  form.post('/forgot-password')
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
        <h2 class="text-center text-3xl font-extrabold text-gray-900 mb-4">
          Forgot your password?
        </h2>
        <p class="text-center text-sm text-gray-600 mb-8">
          Enter your email address and we will send you a link to reset your password.
        </p>

        <div v-if="status" class="mb-4 p-3 rounded bg-green-50 border border-green-200 text-green-700 text-sm">
          {{ status }}
        </div>

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

          <button
            type="submit"
            :disabled="form.processing"
            class="w-full py-2 px-4 bg-amber-600 hover:bg-amber-700 text-white rounded-md disabled:opacity-50"
          >
            <span v-if="form.processing">Sending...</span>
            <span v-else>Send reset link</span>
          </button>

          <p class="text-center text-sm">
            <a href="/login" class="font-medium text-amber-600 hover:text-amber-500">Back to login</a>
          </p>
        </form>
      </div>
    </div>
  </div>
</template>
