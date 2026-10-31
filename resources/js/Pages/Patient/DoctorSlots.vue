<template>
  <AppLayout>
    <section class="mt-6 max-w-4xl">
      <Link :href="route('patient.doctors')" class="text-sm text-amber-600 hover:text-amber-500">
        &larr; Back to doctors
      </Link>
      <h3 class="text-amber-600 font-bold text-xl mt-2 mb-4">Book with {{ doctor.name }}</h3>

      <div v-if="slots.length">
        <!-- Créneaux groupés par jour -->
        <div v-for="(daySlots, date) in slotsByDay" :key="date" class="mb-4">
          <p class="font-semibold text-stone-700 mb-2">{{ formatDate(date) }}</p>
          <div class="flex flex-wrap gap-2">
            <button
              v-for="slot in daySlots"
              :key="slot.id"
              type="button"
              :class="[
                'px-3 py-2 rounded border text-sm',
                form.slot_id === slot.id
                  ? 'bg-amber-600 text-white border-amber-600'
                  : 'bg-white border-stone-300 hover:border-amber-500',
              ]"
              @click="form.slot_id = slot.id"
            >
              {{ formatRange(slot.start_time, slot.end_time) }}
              <span class="block text-xs capitalize opacity-75">{{ slot.slot_type }}</span>
            </button>
          </div>
        </div>

        <form class="mt-6 p-4 bg-white rounded-lg shadow-md space-y-4" @submit.prevent="submit">
          <div>
            <label for="reason" class="block text-sm font-medium text-gray-700">Reason (optional)</label>
            <textarea
              id="reason"
              v-model="form.reason"
              rows="3"
              maxlength="1000"
              class="mt-1 block w-full rounded-md border border-gray-300 px-2 py-1 focus:ring-amber-500 focus:border-amber-500"
            />
            <p v-if="form.errors.reason" class="text-red-600 text-sm mt-1">{{ form.errors.reason }}</p>
          </div>

          <p v-if="form.errors.slot_id" class="text-red-600 text-sm">{{ form.errors.slot_id }}</p>

          <button
            type="submit"
            :disabled="!form.slot_id || form.processing"
            class="py-2 px-4 bg-amber-600 hover:bg-amber-700 text-white rounded-md disabled:opacity-50"
          >
            <span v-if="form.processing">Booking...</span>
            <span v-else>Book this slot</span>
          </button>
        </form>
      </div>

      <p v-else class="p-6 bg-white rounded-lg shadow-md text-stone-600">
        This doctor has no available slot at the moment.
      </p>
    </section>
  </AppLayout>
</template>

<script setup lang="js">
import { computed } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { formatDate, formatRange } from '@/utils/appointments'

const props = defineProps({
  doctor: Object,
  slots: { type: Array, default: () => [] },
})

const route = window.route

const slotsByDay = computed(() =>
  props.slots.reduce((days, slot) => {
    ;(days[slot.date] ??= []).push(slot)
    return days
  }, {}),
)

const form = useForm({
  slot_id: null,
  reason: '',
})

const submit = () => {
  form.post(route('patient.bookAppointment'))
}
</script>
