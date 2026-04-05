<script setup>
import { usePage } from '@inertiajs/vue3'
import UserDropdown from '@/components/UserDropdown.vue'

const page = usePage()
const user = page.props.auth?.user ?? null
</script>

<template>
  <div class="flex h-screen bg-black text-white">

    <!-- SIDEBAR -->
    <aside class="w-64 bg-gray-900 flex flex-col border-r border-gray-800 relative">

      <nav class="flex-1 p-4 space-y-2">
        <a href="/app" class="block px-3 py-2 hover:bg-gray-800 rounded">Voltar Painel</a>
        <a href="/settings/profile" class="block px-3 py-2 hover:bg-gray-800 rounded">Perfil</a>
      </nav>

      <!-- USER -->
      <div class="p-3 border-t border-gray-800">
        <UserDropdown :auth="!!user" :user="user" drop-up />
      </div>

    </aside>

    <!-- CONTEÚDO -->
    <main class="flex-1 p-6 overflow-y-auto">
      <slot />
    </main>

  </div>
</template>