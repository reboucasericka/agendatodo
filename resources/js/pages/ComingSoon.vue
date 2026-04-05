<script setup>
import { Link } from '@inertiajs/vue3'

defineProps({
  title: { type: String, required: true },
  body: { type: String, required: true },
  links: {
    type: Array,
    default: () => [],
  },
})
</script>

<template>
  <div class="min-h-screen bg-zinc-950 text-zinc-100">
    <div class="mx-auto max-w-lg px-6 py-16">
      <p class="text-xs font-medium uppercase tracking-wide text-zinc-500">
        BossFlowX
      </p>
      <h1 class="mt-2 text-2xl font-semibold text-white">
        {{ title }}
      </h1>
      <p class="mt-4 text-sm leading-relaxed text-zinc-400">
        {{ body }}
      </p>

      <div
        v-if="links.length"
        class="mt-8 border-t border-zinc-800 pt-8"
      >
        <p class="text-xs font-medium uppercase tracking-wide text-zinc-500">
          Atalhos com o que já existe
        </p>
        <ul class="mt-3 space-y-2">
          <li v-for="(item, index) in links" :key="index">
            <Link
              :href="item.href"
              class="text-sm text-indigo-400 underline decoration-indigo-400/40 underline-offset-2 hover:text-indigo-300"
            >
              {{ item.label }}
            </Link>
          </li>
        </ul>
      </div>

      <Link
        href="/app"
        class="mt-8 inline-flex rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500"
      >
        Voltar às tarefas
      </Link>
    </div>
  </div>
</template>
