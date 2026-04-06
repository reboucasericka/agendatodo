<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import { usePage } from '@inertiajs/vue3'
import { computed } from 'vue'
import UserDropdown from '@/components/UserDropdown.vue'

defineProps({
  tasks: {
    type: Array,
    default: () => [],
  },
})

const page = usePage()
const user = computed(() => page.props.auth?.user ?? null)
const isAuth = computed(() => !!page.props.auth?.user)

function dueSlice(d) {
  if (!d) {
return null
}

  return typeof d === 'string' ? d.slice(0, 10) : null
}
</script>

<template>
  <Head title="Tarefas concluídas" />

  <div class="min-h-screen bg-zinc-950 text-zinc-100">
    <header class="border-b border-zinc-800 bg-zinc-900/80 px-4 py-3">
      <div class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-3">
        <div class="flex flex-wrap items-center gap-4">
          <Link href="/app" class="text-sm font-semibold text-white">
            Tarefas
          </Link>
          <span class="text-zinc-600">|</span>
          <Link href="/reunioes" class="text-sm font-semibold text-white">
            Reuniões
          </Link>
        </div>
        <UserDropdown :auth="isAuth" :user="user" />
      </div>
    </header>

    <main class="mx-auto max-w-6xl space-y-6 px-4 py-8">
      <section class="rounded-xl border border-zinc-800 bg-zinc-900/70 p-6">
        <h1 class="text-2xl font-semibold text-white">
          Tarefas concluídas
        </h1>
        <p class="mt-1 text-sm text-zinc-400">
          Histórico de tarefas finalizadas.
        </p>
      </section>

      <section class="rounded-xl border border-zinc-800 bg-zinc-900/70 p-4">
        <ul v-if="tasks.length" class="space-y-3">
          <li
            v-for="task in tasks"
            :key="task.id"
            class="rounded-lg border border-zinc-800 bg-zinc-950/60 p-4"
          >
            <p class="font-medium text-zinc-100">
              {{ task.title }}
            </p>
            <p v-if="task.description" class="mt-2 line-clamp-3 text-sm text-zinc-400">
              {{ task.description }}
            </p>
            <p class="mt-3 text-xs text-zinc-500">
              Vencimento: {{ dueSlice(task.due_date) || 'Sem data' }}
            </p>
          </li>
        </ul>
        <p v-else class="py-8 text-center text-sm text-zinc-500">
          Ainda não tens tarefas concluídas.
        </p>
      </section>
    </main>
  </div>
</template>
