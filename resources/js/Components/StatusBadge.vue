<template>
  <span :class="['inline-block px-2 py-0.5 rounded-full text-xs font-medium', statusClasses(status)]">
    {{ statusLabel(status) }}
  </span>
</template>

<script setup lang="js">
import { statusClasses, statusLabel } from '@/utils/appointments'

defineProps({
  status: String,
})
</script>
