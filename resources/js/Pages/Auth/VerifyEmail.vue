<script setup lang="js">
import { useForm, router } from '@inertiajs/vue3'

defineProps({
  status: String,
})

const form = useForm({})

const resend = () => {
  form.post('/email/verification-notification')
}

const logout = () => {
  router.post('/logout')
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
          Verify your email
        </h2>
        <p class="text-center text-sm text-gray-600 mb-8">
          Thanks for signing up! Please verify your email address by clicking the link we just sent you.
        </p>

        <div
          v-if="status === 'verification-link-sent'"
          class="mb-4 p-3 rounded bg-green-50 border border-green-200 text-green-700 text-sm"
        >
          A new verification link has been sent to your email address.
        </div>

        <div class="flex items-center justify-between">
          <button
            type="button"
            :disabled="form.processing"
            class="py-2 px-4 bg-amber-600 hover:bg-amber-700 text-white rounded-md disabled:opacity-50"
            @click="resend"
          >
            <span v-if="form.processing">Sending...</span>
            <span v-else>Resend verification email</span>
          </button>

          <button type="button" class="text-sm font-medium text-amber-600 hover:text-amber-500" @click="logout">
            Log out
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
