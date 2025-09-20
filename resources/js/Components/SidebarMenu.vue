<template>
  <aside class="flex flex-col w-64 h-screen bg-white shadow-md">
    <!-- Menu Header / Logo -->
    <div 
      class="text-3xl font-extrabold text-amber-600 bg-clip-text text-transparent bg-gradient-to-r from-amber-500 via-amber-600 to-amber-700 drop-shadow-lg cursor-pointer p-6"
      @click="$router.push('/')"
    >
      Felyora
    </div>

    <!-- Menu Items -->
    <nav class="flex-1 flex flex-col px-4">
      <button
        
         class="py-1 text-left bg-red-500 text-white rounded-md px-2" 
  
        @click="$router.push('/dashboard')"
      >
        Dashboard
      </button>

      <!-- Profile avec sous-menu -->
      <div class="mb-1">
        <button
          class="py-2 text-left hover:bg-stone-100 rounded-md px-2 w-full flex justify-between items-center"
          @click="toggleProfileMenu"
        >
          Profile
          <span>{{ showProfileMenu ? '▲' : '▼' }}</span>
        </button>
        <div v-if="showProfileMenu" class="flex flex-col ml-4 mt-1">
          <button
        
         class="py-2 text-left hover:bg-stone-100 rounded-md px-2 mb-1"
                     
            @click="$router.push('/patient/profile/view')"
          >
            View
          </button>
          <button
          


          class="py-2 text-left hover:bg-stone-100 rounded-md px-2 "

            @click="$router.push('/patient/profile/edit')"
          >
            Edit / Update
          </button>
          <button

                  
              class="py-1 text-left hover:bg-stone-100 rounded-md px-2"   
         
            @click="$router.push('/patient/profile/delete')"
          >
            Delete
          </button>
        </div>
      </div>


            <button
        class="py-2 text-left hover:bg-stone-100 rounded-md px-2 mb-1"
        @click="$router.push('/Patient file')"
      >
        Patient files
      </button>



      <button
        class="py-2 text-left hover:bg-stone-100 rounded-md px-2 mb-1"
        @click="$router.push('/statistics')"
      >
        Statistics
      </button>
      <button
        class="py-2 text-left hover:bg-stone-100 rounded-md px-2 mb-1"
        @click="$router.push('/statistics')"
      >
        Historics
      </button>
    </nav>
  </aside>
</template>

<script setup>
import { ref } from 'vue'

const showProfileMenu = ref(false)
const toggleProfileMenu = () => {
  showProfileMenu.value = !showProfileMenu.value
}
</script>
