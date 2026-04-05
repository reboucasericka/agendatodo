<script setup>
import { computed, ref, watch } from 'vue'
import { router, Link, usePage } from '@inertiajs/vue3'

import TaskForm from '@/features/Tasks/TaskForm.vue'
import TaskCalendar from '@/features/Tasks/TaskCalendar.vue'
import TaskListPanel from '@/features/Tasks/TaskListPanel.vue'
import UserDropdown from '@/components/UserDropdown.vue'

const props = defineProps({
  tasks: Array,
  meetings: {
    type: Array,
    default: () => [],
  },
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

const page = usePage()

const user = computed(() => page.props.auth?.user ?? null)
const isAuth = computed(() => !!page.props.auth?.user)

const loading = ref(false)
const editingTask = ref(null)
const searchSidebar = ref(props.filters.search ?? '')

watch(
  () => props.filters.search,
  (v) => {
    searchSidebar.value = v ?? ''
  },
)

function applySidebarSearch() {
  router.get(
    '/app',
    { ...props.filters, search: searchSidebar.value },
    { preserveState: true, replace: true },
  )
}

function handleSubmit(data) {
  loading.value = true
  const id = editingTask.value?.id
  if (id) {
    router.put(`/tasks/${id}`, data, {
      onFinish: () => {
        loading.value = false
        editingTask.value = null
      },
    })
  } else {
    router.post('/tasks', data, {
      onFinish: () => {
        loading.value = false
      },
    })
  }
}

function onEditTask(task) {
  editingTask.value = { ...task }
}

function cancelEdit() {
  editingTask.value = null
}

function navClass(active) {
  return [
    'block rounded px-3 py-2 transition',
    active ? 'bg-gray-800 text-white' : 'hover:bg-gray-800',
  ]
}

/** Vista “limpa” da app (sem filtros de lista). */
const isAppHomeActive = computed(() => {
  const f = props.filters
  return (
    f.view !== 'week'
    && f.status === 'all'
    && f.priority === 'all'
    && !(f.due_from || f.due_to || String(f.search || '').trim())
  )
})

const isMeetingsHistoryActive = computed(() => {
  const u = page.url || ''
  if (!u.includes('reunioes')) return false
  return u.includes('tab=completed')
})
</script>

<template>
  <div class="flex h-screen w-full bg-black text-white">

    <aside class="relative flex w-64 flex-col border-r border-gray-800 bg-gray-900">

      <div class="border-b border-gray-800 px-4 py-3">
        <input
          v-model="searchSidebar"
          type="search"
          placeholder="Buscar"
          class="w-full rounded bg-gray-800 px-3 py-2 text-sm text-zinc-100 placeholder:text-zinc-500"
          @keydown.enter.prevent="applySidebarSearch"
        />
      </div>

      <nav class="flex-1 space-y-4 overflow-y-auto px-2 py-4">

        <div>
          <ul class="space-y-1">
            <li>
              <Link href="/app" :class="navClass(isAppHomeActive)">
                Página inicial
              </Link>
            </li>


            <li>
              <Link
                href="/reunioes"
                :class="navClass(page.url.startsWith('/reunioes'))"
              >
                Reuniões
              </Link>
            </li>
          </ul>
        </div>

        <div>
          <p class="px-3 text-xs text-gray-500">
            Particular
          </p>
          <ul class="mt-1 space-y-1">
            <li>
              <Link
                href="/app?view=week"
                :class="navClass(filters.view === 'week')"
              >
                Lista semanal
              </Link>
            </li>
          </ul>
        </div>

        <div>
          <p class="px-3 text-xs text-gray-500">
            Histórico
          </p>
          <ul class="mt-1 space-y-1">
            <li>
              <Link
                href="/app?status=completed"
                :class="navClass(filters.status === 'completed' && filters.view !== 'week')"
              >
                Tarefas concluídas
              </Link>
            </li>
            <li>
              <Link
                href="/reunioes?tab=completed"
                :class="navClass(isMeetingsHistoryActive)"
              >
                Reuniões concluídas
              </Link>
            </li>
          </ul>
        </div>
      </nav>

      <div class="border-t border-gray-800 px-3 py-2">
        <UserDropdown :auth="isAuth" :user="user" drop-up />
      </div>

      <div class="px-4 py-3 text-xs text-gray-500">
        © {{ new Date().getFullYear() }} BossFlowX
      </div>
    </aside>

    <main class="flex-1 overflow-y-auto bg-zinc-950 p-6">
      <div class="flex w-full flex-col gap-6">
        <div class="rounded-lg border border-zinc-800 bg-zinc-900 p-4 shadow-sm">
          <TaskCalendar :tasks="tasks" :meetings="meetings" />
        </div>

        <div class="space-y-4">
          <div>
            <h2 class="mb-2 text-sm font-medium text-zinc-400">
              {{ editingTask ? 'Editar tarefa' : 'Nova tarefa' }}
            </h2>
            <TaskForm
              :model-value="editingTask"
              :loading="loading"
              @submit="handleSubmit"
              @cancel="cancelEdit"
            />
          </div>

          <TaskListPanel :tasks="tasks" :filters="filters" @edit="onEditTask" />
        </div>
      </div>
    </main>

  </div>
</template>
