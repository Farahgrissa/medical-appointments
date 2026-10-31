<template>
  <AppLayout>
    <section class="mt-6 max-w-4xl">
      <Link :href="route('patient.dashboard')" class="text-sm text-amber-600 hover:text-amber-500">
        &larr; Back to dashboard
      </Link>
      <h3 class="text-amber-600 font-bold text-xl mt-2 mb-4">Reschedule appointment</h3>

      <div class="p-4 bg-white rounded-lg shadow-md mb-6">
        <p class="text-sm text-stone-600">Current appointment</p>
        <p class="font-semibold">
          {{ appointment.doctor?.name }} —
          {{ formatDate(appointment.appointment_date) }},
          {{ formatRange(appointment.start_time, appointment.end_time) }}
        </p>
        <StatusBadge :status="appointment.status" class="mt-1" />
      </div>

      <div v-if="slots.length">
        <p class="font-semibold text-stone-700 mb-2">Choose a new slot</p>
        <div v-for="(daySlots, date) in slotsByDay" :key="date" class="mb-4">
          <p class="text-sm text-stone-600 mb-2">{{ formatDate(date) }}</p>
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
            </button>
          </div>
        </div>

        <p class="text-sm text-stone-500 mb-2">The doctor will have to confirm the new time again.</p>
        <p v-if="form.errors.slot_id" class="text-red-600 text-sm mb-2">{{ form.errors.slot_id }}</p>

        <button
          type="button"
          :disabled="!form.slot_id || form.processing"
          class="py-2 px-4 bg-amber-600 hover:bg-amber-700 text-white rounded-md disabled:opacity-50"
          @click="submit"
        >
          <span v-if="form.processing">Saving...</span>
          <span v-else>Move my appointment</span>
        </button>
      </div>

      <p v-else class="p-6 bg-white rounded-lg shadow-md text-stone-600">
        This doctor has no other available slot at the moment.
      </p>
    </section>
  </AppLayout>
</template>

<script setup lang="js">
import { computed } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import StatusBadge from '@/Components/StatusBadge.vue'
import { formatDate, formatRange } from '@/utils/appointments'

const props = defineProps({
  appointment: Object,
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
})

const submit = () => {
  form.put(route('patient.updateAppointment', props.appointment.id))
}
</script>
