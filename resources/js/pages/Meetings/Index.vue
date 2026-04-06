<script setup lang="ts">
import { router, Link, usePage } from '@inertiajs/vue3'
import { computed, reactive, ref, watch } from 'vue'
import UserDropdown from '@/components/UserDropdown.vue'

const props = defineProps({
  meetings: { type: Array, default: () => [] },
  upcoming: { type: Array, default: () => [] },
  tab: { type: String, default: 'all' },
})

const page = usePage()
const user = computed(() => page.props.auth?.user ?? null)
const isAuth = computed(() => !!page.props.auth?.user)

const loading = ref(false)
const formRoot = ref(null)
const editingId = ref(null)

const form = reactive({
  title: '',
  meeting_date: '',
  meeting_time: '',
  status: 'scheduled',
  duration: '',
  platform: '',
  participants_text: '',
})

const statusLabel = {
  planning: 'Em planeamento',
  scheduled: 'Agendada',
  completed: 'Concluída',
}

const badgeColors = [
  'bg-orange-500/25 text-orange-200 border-orange-500/30',
  'bg-sky-500/25 text-sky-200 border-sky-500/30',
  'bg-rose-500/25 text-rose-200 border-rose-500/30',
  'bg-violet-500/25 text-violet-200 border-violet-500/30',
  'bg-emerald-500/25 text-emerald-200 border-emerald-500/30',
]

function participantClass(i) {
  return badgeColors[i % badgeColors.length]
}

function statusPillClass(s) {
  if (s === 'completed') {
return 'bg-emerald-500/20 text-emerald-300 ring-1 ring-emerald-500/40'
}

  if (s === 'scheduled') {
return 'bg-sky-500/20 text-sky-300 ring-1 ring-sky-500/40'
}

  return 'bg-zinc-500/25 text-zinc-300 ring-1 ring-zinc-500/40'
}

function formatPtDate(iso) {
  if (!iso) {
return '—'
}

  const s = typeof iso === 'string' ? iso.slice(0, 10) : iso
  const [y, m, d] = s.split('-')

  if (!y || !m || !d) {
return s
}

  return `${d}/${m}/${y}`
}

const displayedMeetings = computed(() => {
  if (props.tab === 'completed') {
    return props.meetings.filter((m) => m.status === 'completed')
  }

  return props.meetings
})

function meetingsByStatus(status) {
  return props.meetings.filter((m) => m.status === status)
}

const calendarGroups = computed(() => {
  const map = new Map()

  for (const m of props.meetings) {
    const key = (m.meeting_date || '').slice(0, 7)

    if (!key) {
continue
}

    if (!map.has(key)) {
map.set(key, [])
}

    map.get(key).push(m)
  }

  return [...map.entries()].sort(([a], [b]) => a.localeCompare(b))
})

function tabHref(t) {
  return `/reunioes?tab=${encodeURIComponent(t)}`
}

function tabClass(t) {
  return [
    'rounded-md px-3 py-1.5 text-sm font-medium transition',
    props.tab === t
      ? 'bg-sky-600 text-white'
      : 'text-zinc-400 hover:bg-zinc-800 hover:text-zinc-200',
  ]
}

function resetForm() {
  editingId.value = null
  form.title = ''
  form.meeting_date = ''
  form.meeting_time = ''
  form.status = 'scheduled'
  form.duration = ''
  form.platform = ''
  form.participants_text = ''
}

function scrollToForm() {
  formRoot.value?.scrollIntoView({ behavior: 'smooth', block: 'start' })
}

function startCreate() {
  resetForm()
  scrollToForm()
}

function startEdit(m) {
  editingId.value = m.id
  form.title = m.title
  form.meeting_date = (m.meeting_date || '').slice(0, 10)
  form.meeting_time = m.meeting_time || ''
  form.status = m.status
  form.duration = m.duration || ''
  form.platform = m.platform || ''
  form.participants_text = Array.isArray(m.participants) ? m.participants.join(', ') : ''
  scrollToForm()
}

function submit() {
  loading.value = true
  const payload = {
    ...form,
    redirect_tab: props.tab,
  }
  const done = { onFinish: () => {
 loading.value = false 
} }

  if (editingId.value) {
    router.put(`/reunioes/${editingId.value}`, payload, {
      ...done,
      onSuccess: () => resetForm(),
    })
  } else {
    router.post('/reunioes', payload, {
      ...done,
      onSuccess: () => resetForm(),
    })
  }
}

function removeMeeting(m) {
  if (!window.confirm(`Eliminar "${m.title}"?`)) {
return
}

  const url = `/reunioes/${m.id}?${new URLSearchParams({ redirect_tab: props.tab }).toString()}`
  router.delete(url)
}

watch(
  () => props.meetings,
  () => {
    if (editingId.value && !props.meetings.some((m) => m.id === editingId.value)) {
      resetForm()
    }
  },
)
</script>

<template>
  <div class="min-h-screen bg-zinc-950 text-zinc-100">
    <header class="border-b border-zinc-800 bg-zinc-900/80 px-4 py-3">
      <div class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-3">

        <div class="flex flex-wrap items-center gap-4">
          <Link href="/app" class="text-sm font-semibold text-white">
             Tarefas
          </Link>

          <span class="text-zinc-600">|</span>

          <span class="text-sm font-semibold text-white">Reuniões</span>
        </div><UserDropdown :auth="isAuth" :user="user" /></div>
    </header>

    <main class="mx-auto max-w-6xl space-y-6 px-4 py-8">
      <h1 class="text-2xl font-semibold tracking-tight text-white">
        Cadastro de reuniões
      </h1>

      <div class="grid gap-6 lg:grid-cols-3">
        <div class="rounded-xl border border-zinc-800 bg-zinc-900/60 p-4 shadow-sm">
          <h2 class="flex items-center gap-2 text-sm font-medium text-zinc-300">
            <span aria-hidden="true">📋</span> Navegação
          </h2>
          <div class="mt-3 space-y-2">
            <button
              type="button"
              class="w-full rounded-lg bg-sky-600 px-3 py-2 text-left text-sm font-medium text-white hover:bg-sky-500"
              @click="startCreate"
            >
              + Nova reunião
            </button>
            <Link
              href="/app"
              class="flex items-center gap-2 rounded-lg border border-zinc-700 px-3 py-2 text-sm text-zinc-300 hover:bg-zinc-800"
            >
              <span aria-hidden="true">💬</span> Voltar às tarefas
            </Link>
          </div>
        </div>

        <div class="rounded-xl border border-zinc-800 bg-zinc-900/60 p-4 shadow-sm lg:col-span-2">
          <h2 class="flex items-center gap-2 text-sm font-medium text-zinc-300">
            <span aria-hidden="true">🕐</span> Próximas reuniões
          </h2>
          <div class="mt-2 flex flex-wrap items-center gap-2">
            <span class="rounded-full bg-sky-500/20 px-2 py-0.5 text-xs font-medium text-sky-300">
              Próximas
            </span>
          </div>
          <ul v-if="upcoming.length" class="mt-4 space-y-2">
            <li
              v-for="m in upcoming"
              :key="m.id"
              class="flex flex-wrap items-center justify-between gap-2 rounded-lg border border-zinc-800 bg-zinc-950/50 px-3 py-2 text-sm"
            >
              <span class="font-medium text-zinc-200">{{ m.title }}</span>
              <span class="text-zinc-500">{{ formatPtDate(m.meeting_date) }}</span>
            </li>
          </ul>
          <p v-else class="mt-4 text-sm text-zinc-500">
            Nenhuma reunião futura agendada.
          </p>
        </div>
      </div>

      <div class="rounded-xl border border-zinc-800 bg-zinc-900/40 p-4">
        <div class="flex flex-wrap gap-2 border-b border-zinc-800 pb-4">
          <Link :href="tabHref('all')" :class="tabClass('all')">
            Todas
          </Link>
          <Link :href="tabHref('kanban')" :class="tabClass('kanban')">
            Kanban
          </Link>
          <Link :href="tabHref('calendar')" :class="tabClass('calendar')">
            Calendário
          </Link>
          <Link :href="tabHref('list')" :class="tabClass('list')">
            Todas (lista)
          </Link>
          <Link :href="tabHref('completed')" :class="tabClass('completed')">
            Concluídas
          </Link>
        </div>

        <!-- Kanban -->
        <div v-if="tab === 'kanban'" class="mt-6 grid gap-4 md:grid-cols-3">
          <div
            v-for="col in [
              { key: 'planning', label: 'Em planeamento' },
              { key: 'scheduled', label: 'Agendadas' },
              { key: 'completed', label: 'Concluídas' },
            ]"
            :key="col.key"
            class="rounded-lg border border-zinc-800 bg-zinc-950/40 p-3"
          >
            <h3 class="text-xs font-semibold uppercase tracking-wide text-zinc-500">
              {{ col.label }}
            </h3>
            <div class="mt-3 space-y-2">
              <div
                v-for="m in meetingsByStatus(col.key)"
                :key="m.id"
                class="cursor-pointer rounded-lg border border-zinc-800 bg-zinc-900/80 p-3 text-sm hover:border-zinc-600"
                @click="startEdit(m)"
              >
                <p class="font-medium text-zinc-100">
                  {{ m.title }}
                </p>
                <p class="mt-1 text-xs text-zinc-500">
                  {{ formatPtDate(m.meeting_date) }}
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Calendário (simples: por mês) -->
        <div v-else-if="tab === 'calendar'" class="mt-6 space-y-6">
          <p class="text-sm text-zinc-500">
            Vista mensal resumida — em breve podes ligar isto a um calendário visual completo.
          </p>
          <div v-for="[ym, list] in calendarGroups" :key="ym" class="space-y-2">
            <h3 class="text-sm font-semibold text-zinc-300">
              {{ ym }}
            </h3>
            <div class="grid gap-2 sm:grid-cols-2">
              <div
                v-for="m in list"
                :key="m.id"
                class="cursor-pointer rounded-lg border border-zinc-800 bg-zinc-900/60 p-3 text-sm hover:border-zinc-600"
                @click="startEdit(m)"
              >
                <span class="font-medium">{{ m.title }}</span>
                <span class="ml-2 text-zinc-500">{{ formatPtDate(m.meeting_date) }}</span>
              </div>
            </div>
          </div>
          <p v-if="!calendarGroups.length" class="text-sm text-zinc-500">
            Ainda não há reuniões registadas.
          </p>
        </div>

        <!-- Lista / Todas / Concluídas: grelha de cartões -->
        <div
          v-else
          class="mt-6 grid gap-4"
          :class="tab === 'list' ? 'max-w-2xl grid-cols-1' : 'sm:grid-cols-2 lg:grid-cols-3'"
        >
          <article
            v-for="m in displayedMeetings"
            :key="m.id"
            class="flex flex-col rounded-xl border border-zinc-800 bg-zinc-900/70 p-4 shadow-sm transition hover:border-zinc-600"
          >
            <h3 class="text-base font-semibold text-white">
              {{ m.title }}
            </h3>
            <p class="mt-2 text-sm text-zinc-400">
              {{ formatPtDate(m.meeting_date) }}
            </p>
            <span
              class="mt-2 inline-flex w-fit rounded-full px-2 py-0.5 text-xs font-medium"
              :class="statusPillClass(m.status)"
            >
              {{ statusLabel[m.status] ?? m.status }}
            </span>
            <div v-if="m.participants?.length" class="mt-3 flex flex-wrap gap-1">
              <span
                v-for="(p, i) in m.participants"
                :key="i"
                class="rounded-full border px-2 py-0.5 text-[11px] font-medium"
                :class="participantClass(i)"
              >
                {{ p }}
              </span>
            </div>
            <p v-if="m.duration" class="mt-2 text-xs text-zinc-500">
              {{ m.duration }}
            </p>
            <p v-if="m.platform" class="mt-1 text-xs text-zinc-500">
              {{ m.platform }}
            </p>
            <div class="mt-auto flex flex-wrap gap-2 pt-4">
              <button
                type="button"
                class="rounded-md bg-zinc-800 px-2 py-1 text-xs text-zinc-200 hover:bg-zinc-700"
                @click="startEdit(m)"
              >
                Editar
              </button>
              <button
                type="button"
                class="rounded-md text-xs text-red-400 hover:text-red-300"
                @click="removeMeeting(m)"
              >
                Eliminar
              </button>
            </div>
          </article>
        </div>

        <p
          v-if="['all', 'list', 'completed'].includes(tab) && !displayedMeetings.length"
          class="mt-6 text-center text-sm text-zinc-500"
        >
          Nenhuma reunião nesta vista.
        </p>
      </div>

      <section
        ref="formRoot"
        class="rounded-xl border border-zinc-800 bg-zinc-900/60 p-6 shadow-sm"
      >
        <h2 class="text-lg font-semibold text-white">
          {{ editingId ? 'Editar reunião' : 'Nova reunião' }}
        </h2>
        <form class="mt-4 grid gap-4 sm:grid-cols-2" @submit.prevent="submit">
          <div class="sm:col-span-2">
            <label class="block text-xs font-medium text-zinc-500">Título</label>
            <input
              v-model="form.title"
              required
              class="mt-1 w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-white"
            />
          </div>
          <div>
            <label class="block text-xs font-medium text-zinc-500">Data</label>
            <input
              v-model="form.meeting_date"
              type="date"
              required
              class="mt-1 w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-white"
            />
          </div>
          <div>
            <label class="block text-xs font-medium text-zinc-500">Hora (opcional)</label>
            <input
              v-model="form.meeting_time"
              type="time"
              class="mt-1 w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-white"
            />
          </div>
          <div>
            <label class="block text-xs font-medium text-zinc-500">Estado</label>
            <select
              v-model="form.status"
              class="mt-1 w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-white"
            >
              <option value="planning">
                Em planeamento
              </option>
              <option value="scheduled">
                Agendada
              </option>
              <option value="completed">
                Concluída
              </option>
            </select>
          </div>
          <div>
            <label class="block text-xs font-medium text-zinc-500">Duração (ex.: 30min – 1h)</label>
            <input
              v-model="form.duration"
              class="mt-1 w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-white"
              placeholder="30min – 1 hora"
            />
          </div>
          <div>
            <label class="block text-xs font-medium text-zinc-500">Plataforma</label>
            <input
              v-model="form.platform"
              class="mt-1 w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-white"
              placeholder="Zoom, Meet…"
            />
          </div>
          <div class="sm:col-span-2">
            <label class="block text-xs font-medium text-zinc-500">Participantes (separados por vírgula)</label>
            <textarea
              v-model="form.participants_text"
              rows="2"
              class="mt-1 w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-white"
              placeholder="Ana, Bruno, Carlos"
            />
          </div>
          <div class="flex flex-wrap gap-2 sm:col-span-2">
            <button
              type="submit"
              :disabled="loading"
              class="rounded-lg bg-sky-600 px-4 py-2 text-sm font-medium text-white hover:bg-sky-500 disabled:opacity-50"
            >
              {{ loading ? 'A guardar…' : editingId ? 'Atualizar' : 'Criar reunião' }}
            </button>
            <button
              v-if="editingId"
              type="button"
              class="rounded-lg border border-zinc-600 px-4 py-2 text-sm text-zinc-300 hover:bg-zinc-800"
              @click="resetForm"
            >
              Cancelar edição
            </button>
          </div>
        </form>
      </section>
    </main>
  </div>
</template>
