<template>
  <div class="h-screen w-screen flex flex-col bg-gradient-to-b from-stone-50 to-white px-6 py-4 overflow-hidden">

    <!-- Top Bar -->
    <div class="flex justify-between items-center mb-6">
      <!-- Logo / Stylish Felyora -->
      <div class="text-3xl sm:text-4xl font-extrabold text-amber-600 bg-clip-text text-transparent bg-gradient-to-r from-amber-500 via-amber-600 to-amber-700 drop-shadow-lg">
        Felyora
      </div>

      <!-- Login/Register Buttons -->
      <div class="flex gap-4">
        <Link
          v-if="canLogin"
          href="/login"
          class="text-amber-700 hover:text-amber-800 px-5 py-2 text-sm font-medium rounded-lg transition-all duration-300 border border-amber-600 hover:scale-105 shadow-sm"
        >
          Login
        </Link>
        <Link
          v-if="canRegister"
          href="/register"
          class="bg-amber-600 hover:bg-amber-700 text-white px-5 py-2 rounded-lg text-sm font-medium shadow-md transition-all duration-300 hover:scale-105"
        >
          Register
        </Link>
      </div>
    </div>

    <!-- Main Content -->
    <main class="flex-1 flex flex-col justify-center items-center gap-10 overflow-hidden">

      <!-- Hero Section -->
      <section class="text-center">
        <h1 class="text-3xl sm:text-4xl font-extrabold text-stone-900 mb-3">
          Revolutionizing <span class="text-amber-600">Healthcare</span>
        </h1>
        <p class="text-sm sm:text-base text-stone-600 max-w-xl mx-auto">
          Bridging patients and providers through intelligent care solutions.
        </p>
      </section>

      <!-- About Section -->
      <section
        class="bg-white/70 rounded-2xl p-8 max-w-3xl mx-auto text-center shadow-lg backdrop-blur-md transition transform hover:scale-105 duration-300"
      >
        <h2 class="text-3xl sm:text-4xl font-extrabold text-amber-600 tracking-wide mb-6 text-center">About Felyora</h2>
        <p class="text-stone-700 text-sm sm:text-base">
          Felyora Health Platform connects patients with doctors seamlessly, providing easy appointment booking, secure medical records, and intelligent healthcare management for a modern, interactive experience.
        </p>
      </section>

      <!-- Cards Section: Patient & Doctor -->
      <section class="flex justify-center items-center gap-6 mt-10 w-full px-4">
        <!-- Patients -->
        <Link href="/login">
          <div
            class="bg-white rounded-xl shadow-md border-l-4 border-amber-500 hover:shadow-xl transition cursor-pointer flex flex-col items-center justify-center p-6 h-20 w-300 sm:w-300 hover:-translate-y-1 duration-300"
          >
            <h3 class="text-lg font-semibold text-stone-800 mb-2">Patients</h3>
            <p class="text-xs text-stone-600 text-center">Book appointments & access medical records</p>
          </div>
        </Link>

        <!-- Doctors -->
        <Link href="/login">
          <div
            class="bg-white rounded-xl shadow-md border-l-4 border-amber-500 hover:shadow-xl transition cursor-pointer flex flex-col items-center justify-center p-6 h-20 w-300 sm:w-300 hover:-translate-y-1 duration-300"
          >
            <h3 class="text-lg font-semibold text-stone-800 mb-2">Doctors</h3>
            <p class="text-xs text-stone-600 text-center">Manage appointments & patient records</p>
          </div>
        </Link>
      </section>

    </main>

    <!-- Footer -->
    <footer class="text-center text-xs text-stone-500 py-3 border-t">
      © 2025 Felyora Health Platform. All rights reserved.
    </footer>

  </div>
</template>

<script setup>
import { Link } from "@inertiajs/vue3";

defineProps({
  canLogin: Boolean,
  canRegister: Boolean,
});
</script>

<style scoped>
.hover\:scale-105:hover {
  transform: scale(1.05);
}
.hover\:-translate-y-1:hover {
  transform: translateY(-4px);
}

/* Prevent any scrolling */
html, body {
  overflow: hidden;
}
</style>
