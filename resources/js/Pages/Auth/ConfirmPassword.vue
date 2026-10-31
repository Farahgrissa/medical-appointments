<script setup lang="js">
import { useForm } from '@inertiajs/vue3'
import FormInput from '@/Components/FormInput.vue'

const form = useForm({
  password: '',
})

const submit = () => {
  form.post('/confirm-password', {
    onFinish: () => form.reset('password'),
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
        <h2 class="text-center text-3xl font-extrabold text-gray-900 mb-4">
          Confirm your password
        </h2>
        <p class="text-center text-sm text-gray-600 mb-8">
          This is a secure area. Please confirm your password before continuing.
        </p>

        <form @submit.prevent="submit" class="space-y-6">
          <FormInput
            id="password"
            label="Password"
            type="password"
            v-model="form.password"
            :error="form.errors.password"
            autocomplete="current-password"
            required
          />

          <button
            type="submit"
            :disabled="form.processing"
            class="w-full py-2 px-4 bg-amber-600 hover:bg-amber-700 text-white rounded-md disabled:opacity-50"
          >
            <span v-if="form.processing">Confirming...</span>
            <span v-else>Confirm</span>
          </button>
        </form>
      </div>
    </div>
  </div>
</template>
