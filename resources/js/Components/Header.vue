<template>
  <header class="flex justify-between items-center p-4 bg-transparent sticky top-0 z-50">
    
    <!-- Zone calendrier + recherche + filtre -->
    <div class="flex items-center gap-4">
      <!-- Sélecteur de date -->
      <input
        v-model="selectedDate"
        type="date"
        class="px-4 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-amber-400"
      />

      <!-- Barre de recherche -->
      <input
        v-model="search"
        type="text"
        placeholder="Search..."
        class="px-4 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-amber-400"
      />

      <!-- Liste déroulante de filtre -->
      <select
        v-model="filter"
        class="px-4 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-amber-400"
      >
        <option value="">All</option>
        <option value="profile">Profile</option>
        <option value="appointment">Appointment</option>
        <option value="consultation">Consultation</option>
      </select>

      <button
        @click="applyFilter"
        class="px-4 py-2 bg-amber-600 text-white rounded hover:bg-amber-500 transition"
      >
        Search
      </button>
    </div>

    <!-- Bouton Logout -->
    <button
      @click="logout"
      class="px-4 py-2 bg-amber-600 text-white rounded hover:bg-amber-500 transition"
    >
      Logout
    </button>
  </header>
</template>

<script>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

export default {
  setup() {
    const selectedDate = ref('');
    const search = ref('');
    const filter = ref('');

    const applyFilter = () => {
      console.log('Date:', selectedDate.value);
      console.log('Search:', search.value);
      console.log('Filter:', filter.value);
      // Ici tu peux envoyer ces valeurs au backend via Inertia
    };

    const logout = () => {
      router.post(route('logout'));
    };

    return {
      selectedDate,
      search,
      filter,
      applyFilter,
      logout,
    };
  },
};
</script>

<style scoped>
header {
  position: sticky;
  top: 0;
  z-index: 50;
  /* Supprimer l’ombre si tu veux une transparence totale */
  /* box-shadow: none; */
}
</style>
