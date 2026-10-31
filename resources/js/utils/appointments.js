// Helpers d'affichage partagés par les pages de rendez-vous et de créneaux

// "2026-11-02" -> "Mon, Nov 2, 2026"
export function formatDate(date) {
  if (!date) return ''
  const [year, month, day] = date.slice(0, 10).split('-').map(Number)
  return new Date(year, month - 1, day).toLocaleDateString('en-GB', {
    weekday: 'short',
    day: 'numeric',
    month: 'short',
    year: 'numeric',
  })
}

// "09:30:00" -> "09:30"
export function formatTime(time) {
  return time ? time.slice(0, 5) : ''
}

export function formatRange(start, end) {
  return `${formatTime(start)} - ${formatTime(end)}`
}

const STATUS_CLASSES = {
  pending: 'bg-amber-100 text-amber-800',
  confirmed: 'bg-green-100 text-green-800',
  cancelled: 'bg-stone-200 text-stone-600',
  available: 'bg-green-100 text-green-800',
  booked: 'bg-blue-100 text-blue-800',
}

export function statusClasses(status) {
  return STATUS_CLASSES[status] ?? 'bg-stone-100 text-stone-700'
}

export function statusLabel(status) {
  return status ? status.charAt(0).toUpperCase() + status.slice(1) : ''
}
