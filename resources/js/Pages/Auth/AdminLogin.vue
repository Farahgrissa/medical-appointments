<script setup>
import { useForm } from '@inertiajs/vue3'
import FormInput from '@/Components/FormInput.vue'

const form = useForm({
  email: '',
  password: '',
  remember: false,
})

const submit = () => {
  form.post('/admin/login', { // <-- ici, route admin
    onFinish: () => form.reset('password')
  })
}
</script>

<template>
  <div class="min-h-screen bg-gray-50 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 bg-white p-8 rounded-xl shadow border border-blue-200">
      <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">Admin Login</h2>
      <form @submit.prevent="submit" class="mt-8 space-y-6">
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
          label="Password"
          type="password"
          v-model="form.password"
          :error="form.errors.password"
          autocomplete="current-password"
          required
        />
        <div class="flex items-center">
          <input id="remember" type="checkbox" v-model="form.remember"
                 class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"/>
          <label for="remember" class="ml-2 block text-sm text-gray-900">Remember me</label>
        </div>
        <button type="submit" :disabled="form.processing"
                class="w-full py-2 px-4 bg-blue-600 hover:bg-blue-700 text-white rounded-md disabled:opacity-50">
          <span v-if="form.processing">Logging in...</span>
          <span v-else>Login</span>
        </button>
      </form>
    </div>
  </div>
</template>
