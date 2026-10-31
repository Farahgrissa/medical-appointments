<template>
  <aside class="flex flex-col w-64 h-screen bg-white shadow-md">
    <!-- Menu Header / Logo -->
    <Link
      href="/"
      class="text-3xl font-extrabold text-amber-600 bg-clip-text text-transparent bg-gradient-to-r from-amber-500 via-amber-600 to-amber-700 drop-shadow-lg cursor-pointer p-6"
    >
      Felyora
    </Link>

    <!-- Menu Items (selon le rôle) -->
    <nav class="flex-1 flex flex-col px-4 gap-1">
      <Link
        v-for="item in items"
        :key="item.href"
        :href="item.href"
        :class="[
          'py-2 px-2 rounded-md text-left',
          isActive(item.href) ? 'bg-amber-600 text-white' : 'hover:bg-stone-100',
        ]"
      >
        {{ item.label }}
      </Link>

      <!-- Profile avec sous-menu (patient) -->
      <div v-if="hasRole('patient')" class="mb-1">
        <button
          class="py-2 text-left hover:bg-stone-100 rounded-md px-2 w-full flex justify-between items-center"
          @click="toggleProfileMenu"
        >
          Profile
          <span>{{ showProfileMenu ? '▲' : '▼' }}</span>
        </button>
        <div v-if="showProfileMenu" class="flex flex-col ml-4 mt-1">
          <Link href="/patient/profile/show" class="py-2 text-left hover:bg-stone-100 rounded-md px-2 mb-1">
            View
          </Link>
          <Link href="/patient/profile/edit" class="py-2 text-left hover:bg-stone-100 rounded-md px-2">
            Edit / Update
          </Link>
          <Link href="/patient/profile/delete" class="py-1 text-left hover:bg-stone-100 rounded-md px-2">
            Delete
          </Link>
        </div>
      </div>
    </nav>
  </aside>
</template>

<script setup lang="js">
import { computed, ref } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'

const page = usePage()
const roles = computed(() => page.props.auth?.roles ?? [])
const hasRole = (role) => roles.value.includes(role)

const items = computed(() => {
  if (hasRole('admin')) {
    return [{ label: 'Dashboard', href: '/admin/dashboard' }]
  }
  if (hasRole('doctor')) {
    return [
      { label: 'Dashboard', href: '/doctor/dashboard' },
      { label: 'My slots', href: '/doctor/slots' },
    ]
  }
  if (hasRole('patient')) {
    return [
      { label: 'Dashboard', href: '/patient/dashboard' },
      { label: 'Book an appointment', href: '/patient/doctors' },
    ]
  }
  return []
})

const isActive = (href) => page.url.startsWith(href)

const showProfileMenu = ref(false)
const toggleProfileMenu = () => {
  showProfileMenu.value = !showProfileMenu.value
}
</script>
