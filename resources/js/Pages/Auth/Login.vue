<script setup>
import { useForm } from '@inertiajs/vue3'
import FormInput from '@/Components/FormInput.vue'

const form = useForm({
  email: '',
  password: '',
  remember: false,
})

const submit = () => {
  form.post('/login', {
    onFinish: () => form.reset('password')
  })
}

// logout function
const logout = () => {
  form.post('/logout')
}
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Transparent Header -->
    <header class="w-full absolute top-0 left-0 flex justify-between items-center p-6 bg-transparent z-50">
      <a href="/" class="text-3xl font-extrabold text-amber-600 bg-clip-text text-transparent bg-gradient-to-r from-amber-500 via-amber-600 to-amber-700 drop-shadow-lg cursor-pointer">
        Felyora
      </a>
    </header>

    <!-- Login Form -->
    <div class="flex items-center justify-center min-h-screen">
      <div class="w-full max-w-md mt-24"> 
        <h2 class="text-center text-3xl font-extrabold text-gray-900 mb-8">
          Welcome Back
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
            label="Password"
            type="password"
            v-model="form.password"
            :error="form.errors.password"
            autocomplete="current-password"
            required
          />

          <div class="flex items-center justify-between">
            <div class="flex items-center">
              <input
                id="remember"
                type="checkbox"
                v-model="form.remember"
                class="h-4 w-4 text-amber-600 focus:ring-amber-500 border-gray-300 rounded"
              />
              <label for="remember" class="ml-2 block text-sm text-gray-900">
                Remember me
              </label>
            </div>

            <div class="text-sm">
              <a
                href="/forgot-password"
                class="font-medium text-amber-600 hover:text-amber-500"
              >
                Forgot your password?
              </a>
            </div>
          </div>

          <button
            type="submit"
            :disabled="form.processing"
            class="w-full py-2 px-4 bg-amber-600 hover:bg-amber-700 text-white rounded-md disabled:opacity-50"
          >
            <span v-if="form.processing">Logging in...</span>
            <span v-else>Login</span>
          </button>
        </form>
      </div>
    </div>
  </div>
      <!-- Footer -->
    <footer class="text-center text-xs text-stone-500 py-3 border-t">
      © 2025 Felyora Health Platform. All rights reserved.
    </footer>
</template>
