<script setup>
import { ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
  tasks: { type: Array, default: () => [] },
  filters: {
    type: Object,
    default: () => ({
      status: 'all',
      priority: 'all',
      due_from: '',
      due_to: '',
      search: '',
      view: 'all',
    }),
  },
})

const emit = defineEmits(['edit'])

const selectedId = ref(null)
const searchLocal = ref(props.filters.search ?? '')

watch(
  () => props.filters.search,
  (v) => {
    searchLocal.value = v ?? ''
  },
)

function navigate(patch) {
  router.get(
    '/app',
    { ...props.filters, ...patch },
    { preserveState: true, replace: true },
  )
}

function applySearch() {
  navigate({ search: searchLocal.value })
}

const priorityLabel = { low: 'Baixa', medium: 'Média', high: 'Alta' }

function priorityClass(p) {
  if (p === 'high') return 'bg-red-500/20 text-red-300'
  if (p === 'low') return 'bg-zinc-500/20 text-zinc-400'
  return 'bg-amber-500/20 text-amber-200'
}

function dueSlice(d) {
  if (!d) return null
  return typeof d === 'string' ? d.slice(0, 10) : null
}

function localYmd(d) {
  const y = d.getFullYear()
  const m = String(d.getMonth() + 1).padStart(2, '0')
  const day = String(d.getDate()).padStart(2, '0')
  return `${y}-${m}-${day}`
}

function todayYmd() {
  return localYmd(new Date())
}

function tomorrowYmd() {
  const t = new Date()
  t.setDate(t.getDate() + 1)
  return localYmd(t)
}

/** Destaque para vencimento hoje / amanhã (lista, incl. vista semanal). */
function urgencyRowClass(task) {
  const slice = dueSlice(task.due_date)
  if (!slice) return ''
  const t = todayYmd()
  const tm = tomorrowYmd()
  if (slice === t) {
    return 'border-l-2 border-l-red-500/80 bg-red-500/10'
  }
  if (slice === tm) {
    return 'border-l-2 border-l-amber-500/70 bg-amber-500/10'
  }
  return ''
}

function urgencyLabel(task) {
  const slice = dueSlice(task.due_date)
  if (!slice) return null
  if (slice === todayYmd()) return 'Hoje'
  if (slice === tomorrowYmd()) return 'Amanhã'
  return null
}

function toggleTask(task, ev) {
  ev?.stopPropagation?.()
  router.post(`/tasks/${task.id}/toggle`, {}, { preserveScroll: true })
}

function deleteTask(task) {
  if (!window.confirm(`Eliminar a tarefa "${task.title}"?`)) return
  router.delete(`/tasks/${task.id}`, { preserveScroll: true })
  if (selectedId.value === task.id) selectedId.value = null
}

function selectTask(task) {
  selectedId.value = selectedId.value === task.id ? null : task.id
}

function startEdit(task) {
  emit('edit', task)
  selectedId.value = task.id
}
</script>

<template>
  <section class="rounded-lg border border-zinc-800 bg-zinc-900/90 p-4 text-zinc-200 shadow-sm">
    <div class="flex flex-col gap-3 border-b border-zinc-800 pb-4">
      <div class="flex flex-wrap items-end gap-2">
        <label class="flex min-w-[8rem] flex-1 flex-col gap-1 text-xs text-zinc-500">
          Estado
          <select
            :value="filters.status"
            class="rounded-md border border-zinc-700 bg-zinc-950 px-2 py-2 text-sm text-zinc-100"
            @change="navigate({ status: ($event.target).value })"
          >
            <option value="all">Todas</option>
            <option value="pending">Pendentes</option>
            <option value="completed">Concluídas</option>
          </select>
        </label>
        <label class="flex min-w-[8rem] flex-1 flex-col gap-1 text-xs text-zinc-500">
          Prioridade
          <select
            :value="filters.priority"
            class="rounded-md border border-zinc-700 bg-zinc-950 px-2 py-2 text-sm text-zinc-100"
            @change="navigate({ priority: ($event.target).value })"
          >
            <option value="all">Todas</option>
            <option value="low">Baixa</option>
            <option value="medium">Média</option>
            <option value="high">Alta</option>
          </select>
        </label>
      </div>
      <div class="flex flex-wrap items-end gap-2">
        <label class="flex min-w-[9rem] flex-1 flex-col gap-1 text-xs text-zinc-500">
          Vencimento de
          <input
            :value="filters.due_from"
            type="date"
            class="rounded-md border border-zinc-700 bg-zinc-950 px-2 py-2 text-sm text-zinc-100"
            @change="navigate({ due_from: ($event.target).value })"
          />
        </label>
        <label class="flex min-w-[9rem] flex-1 flex-col gap-1 text-xs text-zinc-500">
          até
          <input
            :value="filters.due_to"
            type="date"
            class="rounded-md border border-zinc-700 bg-zinc-950 px-2 py-2 text-sm text-zinc-100"
            @change="navigate({ due_to: ($event.target).value })"
          />
        </label>
        <button
          type="button"
          class="rounded-md border border-zinc-600 px-3 py-2 text-sm text-zinc-300 hover:bg-zinc-800"
          @click="navigate({ due_from: '', due_to: '' })"
        >
          Limpar datas
        </button>
      </div>
      <div class="flex gap-2">
        <input
          v-model="searchLocal"
          type="search"
          placeholder="Pesquisar no título ou descrição…"
          class="min-w-0 flex-1 rounded-md border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-zinc-100 placeholder:text-zinc-600"
          @keydown.enter.prevent="applySearch"
        />
        <button
          type="button"
          class="rounded-md bg-zinc-800 px-3 py-2 text-sm text-white hover:bg-zinc-700"
          @click="applySearch"
        >
          Filtrar
        </button>
      </div>
      <p v-if="filters.view === 'week'" class="text-xs text-emerald-400/90">
        Vista: tarefas com vencimento nesta semana (seg.–dom.). Vencimento
        <span class="text-red-300/90">hoje</span> ou
        <span class="text-amber-200/90">amanhã</span> fica destacado na lista.
      </p>
    </div>

    <ul class="mt-4 divide-y divide-zinc-800" role="list">
      <li v-for="task in tasks" :key="task.id" class="py-2">
        <div
          class="flex cursor-pointer items-start gap-3 rounded-md px-2 py-2 hover:bg-zinc-800/60"
          :class="urgencyRowClass(task)"
          role="button"
          tabindex="0"
          @click="selectTask(task)"
          @keydown.enter.prevent="selectTask(task)"
        >
          <input
            type="checkbox"
            class="mt-1 h-4 w-4 shrink-0 rounded border-zinc-600 bg-zinc-900 text-indigo-600"
            :checked="task.status === 'completed'"
            :aria-label="task.status === 'completed' ? 'Marcar como pendente' : 'Marcar como concluída'"
            @click.stop
            @change="toggleTask(task, $event)"
          />
          <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-center gap-2">
              <span
                class="font-medium text-zinc-100"
                :class="{ 'text-zinc-500 line-through': task.status === 'completed' }"
              >
                {{ task.title }}
              </span>
              <span
                class="rounded px-2 py-0.5 text-[10px] font-medium uppercase tracking-wide"
                :class="priorityClass(task.priority)"
              >
                {{ priorityLabel[task.priority] ?? task.priority }}
              </span>
            </div>
            <p v-if="dueSlice(task.due_date)" class="mt-0.5 flex flex-wrap items-center gap-2 text-xs text-zinc-500">
              <span>Vence: {{ dueSlice(task.due_date) }}</span>
              <span
                v-if="urgencyLabel(task)"
                class="rounded px-1.5 py-0.5 font-medium"
                :class="
                  urgencyLabel(task) === 'Hoje'
                    ? 'bg-red-500/25 text-red-200'
                    : 'bg-amber-500/20 text-amber-100'
                "
              >
                {{ urgencyLabel(task) }}
              </span>
            </p>
          </div>
          <span class="shrink-0 text-xs text-zinc-600">
            {{ selectedId === task.id ? '▼' : '▶' }}
          </span>
        </div>

        <div
          v-if="selectedId === task.id"
          class="mt-2 space-y-3 rounded-md border border-zinc-700/80 bg-zinc-950/80 px-3 py-3 text-sm"
        >
          <div>
            <p class="text-xs font-medium uppercase tracking-wide text-zinc-500">
              Descrição
            </p>
            <p class="mt-1 whitespace-pre-wrap text-zinc-300">
              {{ task.description || '— sem descrição —' }}
            </p>
          </div>
          <div class="flex flex-wrap gap-2">
            <button
              type="button"
              class="rounded-md bg-indigo-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-indigo-500"
              @click="startEdit(task)"
            >
              Editar
            </button>
            <button
              type="button"
              class="rounded-md border border-zinc-600 px-3 py-1.5 text-xs text-zinc-200 hover:bg-zinc-800"
              @click="toggleTask(task)"
            >
              {{ task.status === 'completed' ? 'Marcar pendente' : 'Marcar concluída' }}
            </button>
            <button
              type="button"
              class="rounded-md border border-red-900/60 px-3 py-1.5 text-xs text-red-400 hover:bg-red-950/40"
              @click="deleteTask(task)"
            >
              Eliminar
            </button>
          </div>
        </div>
      </li>
    </ul>

    <p v-if="tasks.length === 0" class="py-8 text-center text-sm text-zinc-500">
      Nenhuma tarefa com estes filtros.
    </p>
  </section>
</template>
