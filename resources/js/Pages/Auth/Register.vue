<template>
  <div class="max-w-md mx-auto p-6">
        <!-- Transparent Header -->
    <header class="w-full absolute top-0 left-0 flex justify-between items-center p-6 bg-transparent z-50">
      <a href="/" class="text-3xl font-extrabold text-amber-600 bg-clip-text text-transparent bg-gradient-to-r from-amber-500 via-amber-600 to-amber-700 drop-shadow-lg cursor-pointer">
        Felyora
      </a>

    </header>
    <h2 class="text-2xl font-bold mb-4">Register</h2>

    <form @submit.prevent="submit" class="space-y-4">
      <!-- Name -->
      <FormInput
        id="name"
        label="Name"
        type="text"
        v-model="form.name"
        :error="form.errors.name"
        required
      />

      <!-- Email -->
      <FormInput
        id="email"
        label="Email"
        type="email"
        v-model="form.email"
        :error="form.errors.email"
        required
      />

      <!-- Password -->
      <FormInput
        id="password"
        label="Password"
        type="password"
        v-model="form.password"
        :error="form.errors.password"
        required
      />

      <!-- Confirm Password -->
      <FormInput
        id="password_confirmation"
        label="Confirm Password"
        type="password"
        v-model="form.password_confirmation"
        :error="form.errors.password_confirmation"
        required
      />

      <!-- Role (Doctor / Patient) -->
      <div>
        <label for="role" class="block text-sm font-medium text-gray-700">Role</label>
        <select
          id="role"
          v-model="form.role"
          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-amber-500"
        >
          <option value="patient">Patient</option>
          <option value="doctor">Doctor</option>
        </select>
        <p v-if="form.errors.role" class="text-red-600 text-sm mt-1">{{ form.errors.role }}</p>
      </div>

      <!-- Submit -->
      <button type="submit" :disabled="form.processing"
              class="w-full py-2 px-4 rounded-md text-white bg-amber-600 hover:bg-amber-700 disabled:opacity-50">
        <span v-if="form.processing">Registering...</span>
        <span v-else>Register</span>
      </button>
    </form>

    <!-- Email verification section -->
    <div v-if="emailSent" class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded">
      <p>Please check your email to verify your account.</p>
      <button @click="resendVerificationEmail" :disabled="sending"
              class="mt-2 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 disabled:opacity-50">
        <span v-if="sending">Sending...</span>
        <span v-else>Resend Verification Email</span>
      </button>
    </div>
  </div>
</template>

<script setup lang="js">
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { Inertia } from '@inertiajs/inertia'
import FormInput from '@/Components/FormInput.vue'

const form = useForm({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  role: 'patient', // valeur par défaut
})

const emailSent = ref(false)
const sending = ref(false)

const submit = () => {
  form.post('/register', {
    onSuccess: () => {
      emailSent.value = true
      form.reset('password', 'password_confirmation')
    },
  })
}

function resendVerificationEmail() {
  sending.value = true
  Inertia.post(route('verification.send'), {}, {
    onFinish: () => {
      sending.value = false
      alert('Verification email sent!')
    },
  })
}
</script>
