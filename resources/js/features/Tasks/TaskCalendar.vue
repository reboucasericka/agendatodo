<script setup lang="ts">
import { computed, ref } from 'vue'

const props = defineProps({
  tasks: {
    type: Array,
    default: () => [],
  },
  meetings: {
    type: Array,
    default: () => [],
  },
})

const weekdayLabels = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb']

const monthNames = [
  'Janeiro',
  'Fevereiro',
  'Março',
  'Abril',
  'Maio',
  'Junho',
  'Julho',
  'Agosto',
  'Setembro',
  'Outubro',
  'Novembro',
  'Dezembro',
]

const now = new Date()
const viewYear = ref(now.getFullYear())
const viewMonth = ref(now.getMonth())

function toLocalIsoDate(d) {
  const y = d.getFullYear()
  const m = String(d.getMonth() + 1).padStart(2, '0')
  const day = String(d.getDate()).padStart(2, '0')

  return `${y}-${m}-${day}`
}

const todayIso = computed(() => toLocalIsoDate(new Date()))

function taskDueIso(task) {
  if (!task?.due_date) {
return null
}

  const raw = task.due_date

  if (typeof raw === 'string') {
return raw.slice(0, 10)
}

  return null
}

const titleLabel = computed(() => {
  return `${monthNames[viewMonth.value]} de ${viewYear.value}`
})

const weeks = computed(() => {
  const y = viewYear.value
  const m = viewMonth.value
  const firstDow = new Date(y, m, 1).getDay()
  const daysInMonth = new Date(y, m + 1, 0).getDate()
  const prevMonthLast = new Date(y, m, 0).getDate()

  const cells = []

  for (let i = 0; i < firstDow; i++) {
    const dayNum = prevMonthLast - firstDow + i + 1
    const d = new Date(y, m - 1, dayNum)
    cells.push({
      inMonth: false,
      label: dayNum,
      iso: toLocalIsoDate(d),
    })
  }

  for (let day = 1; day <= daysInMonth; day++) {
    const d = new Date(y, m, day)
    cells.push({
      inMonth: true,
      label: day,
      iso: toLocalIsoDate(d),
    })
  }

  const total = 42
  let next = 1

  while (cells.length < total) {
    const d = new Date(y, m + 1, next)
    cells.push({
      inMonth: false,
      label: next,
      iso: toLocalIsoDate(d),
    })
    next++
  }

  const result = []

  for (let i = 0; i < cells.length; i += 7) {
    result.push(cells.slice(i, i + 7))
  }

  return result
})

function tasksOnDay(iso) {
  return props.tasks.filter((t) => taskDueIso(t) === iso)
}

function meetingDateIso(m) {
  if (!m?.meeting_date) {
return null
}

  const raw = m.meeting_date

  if (typeof raw === 'string') {
return raw.slice(0, 10)
}

  return null
}

function meetingsOnDay(iso) {
  return props.meetings.filter((m) => meetingDateIso(m) === iso)
}

function meetingPillClass(m) {
  if (m.status === 'completed') {
return 'bg-violet-950/90 text-violet-200 ring-1 ring-violet-500/40'
}

  if (m.status === 'planning') {
return 'bg-fuchsia-950/80 text-fuchsia-200 ring-1 ring-fuchsia-500/35'
}

  return 'bg-violet-600/90 text-white ring-1 ring-violet-400/50'
}

function goToday() {
  const d = new Date()
  viewYear.value = d.getFullYear()
  viewMonth.value = d.getMonth()
}

function prevMonth() {
  if (viewMonth.value === 0) {
    viewMonth.value = 11
    viewYear.value -= 1
  } else {
    viewMonth.value -= 1
  }
}

function nextMonth() {
  if (viewMonth.value === 11) {
    viewMonth.value = 0
    viewYear.value += 1
  } else {
    viewMonth.value += 1
  }
}
</script>

<template>
  <div class="flex h-full min-h-[520px] flex-col text-zinc-200">
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3 border-b border-zinc-700/80 pb-3">
      <div class="flex items-center gap-2">
        <button
          type="button"
          class="rounded-md border border-zinc-600 bg-zinc-800 px-3 py-1.5 text-sm hover:bg-zinc-700"
          @click="goToday"
        >
          Hoje
        </button>
        <button
          type="button"
          class="rounded-md p-2 text-zinc-300 hover:bg-zinc-800"
          aria-label="Mês anterior"
          @click="prevMonth"
        >
          ‹
        </button>
        <button
          type="button"
          class="rounded-md p-2 text-zinc-300 hover:bg-zinc-800"
          aria-label="Próximo mês"
          @click="nextMonth"
        >
          ›
        </button>
      </div>
      <h2 class="text-lg font-semibold text-white">
        {{ titleLabel }}
      </h2>
    </div>

    <div class="mb-3 flex flex-wrap items-center gap-4 text-[11px] text-zinc-500">
      <span class="inline-flex items-center gap-1.5">
        <span class="h-2 w-4 rounded-sm bg-emerald-600/90" /> Tarefas
      </span>
      <span class="inline-flex items-center gap-1.5">
        <span class="h-2 w-4 rounded-sm bg-violet-600/90" /> Reuniões
      </span>
    </div>

    <div class="grid grid-cols-7 gap-px rounded-lg border border-zinc-700/80 bg-zinc-700/80 overflow-hidden">
      <div
        v-for="w in weekdayLabels"
        :key="w"
        class="bg-zinc-900 px-2 py-2 text-center text-xs font-medium uppercase tracking-wide text-zinc-500"
      >
        {{ w }}
      </div>

      <template v-for="(week, wi) in weeks" :key="wi">
        <div
          v-for="cell in week"
          :key="cell.iso"
          class="min-h-[72px] bg-zinc-950 p-1.5"
          :class="cell.inMonth ? 'bg-zinc-950' : 'bg-zinc-950/60'"
        >
          <div class="flex items-start justify-between gap-1">
            <span
              class="inline-flex h-7 min-w-[1.75rem] items-center justify-center rounded-full text-sm tabular-nums"
              :class="[
                cell.iso === todayIso ? 'bg-sky-600 text-white font-medium' : '',
                cell.inMonth ? 'text-zinc-200' : 'text-zinc-600',
              ]"
            >
              {{ cell.label }}
            </span>
          </div>
          <div class="mt-1 space-y-0.5">
            <div
              v-for="m in meetingsOnDay(cell.iso)"
              :key="'m-' + m.id"
              class="truncate rounded px-1.5 py-0.5 text-[11px] font-medium leading-tight"
              :class="meetingPillClass(m)"
              :title="m.meeting_time ? `${m.title} (${m.meeting_time})` : m.title"
            >
              {{ m.title }}
            </div>
            <div
              v-for="task in tasksOnDay(cell.iso)"
              :key="'t-' + task.id"
              class="truncate rounded px-1.5 py-0.5 text-[11px] font-medium leading-tight bg-emerald-600/90 text-white"
              :title="task.title"
            >
              {{ task.title }}
            </div>
          </div>
        </div>
      </template>
    </div>
  </div>
</template>
