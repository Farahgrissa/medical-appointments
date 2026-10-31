<template>
  <AppLayout>
    <section class="max-w-2xl py-6">
      <Link :href="route('admin.users')" class="text-sm text-amber-600 hover:text-amber-500">&larr; Back to users</Link>
      <h2 class="text-amber-600 font-bold text-2xl mt-2 mb-2">Add a doctor</h2>
      <p class="text-sm text-stone-600 mb-6">
        The doctor will receive an email with a link to choose their password.
      </p>

      <form class="p-4 bg-white rounded-lg shadow-md" @submit.prevent="submit">
        <FormInput id="name" label="Full name" v-model="form.name" :error="form.errors.name" required />
        <FormInput id="email" label="Email" type="email" v-model="form.email" :error="form.errors.email" required />
        <FormInput id="specialty" label="Specialty" v-model="form.specialty" :error="form.errors.specialty" required />
        <FormInput id="phone" label="Phone (optional)" v-model="form.phone" :error="form.errors.phone" />

        <button
          type="submit"
          :disabled="form.processing"
          class="py-2 px-4 bg-amber-600 hover:bg-amber-700 text-white rounded-md disabled:opacity-50"
        >
          <span v-if="form.processing">Creating...</span>
          <span v-else>Create doctor account</span>
        </button>
      </form>
    </section>
  </AppLayout>
</template>

<script setup lang="js">
import { Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import FormInput from '@/Components/FormInput.vue'

const route = window.route

const form = useForm({
  name: '',
  email: '',
  specialty: '',
  phone: '',
})

const submit = () => {
  form.post(route('admin.storeDoctor'))
}
</script>
