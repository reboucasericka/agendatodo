<script setup lang="ts">
import { router } from '@inertiajs/vue3'
import { ref, computed, onMounted, onUnmounted } from 'vue'

// PROPS vindas do Inertia
const props = defineProps({
  auth: { type: Boolean, default: false },
  user: { type: Object, default: null },
  loginUrl: { type: String, default: '/login' },
  registerUrl: { type: String, default: '/register' },
  profileUrl: { type: String, default: '/settings/profile' },
  hasRegister: { type: Boolean, default: true },
  /** true = abre para cima (ex.: canto inferior da sidebar); false = abre para baixo (ex.: header topo) */
  dropUp: { type: Boolean, default: false },
})

// DADOS DO USER
const userName = computed(() => props.user?.name ?? '')
const userEmail = computed(() => props.user?.email ?? '')
const userPhoto = computed(() => props.user?.profile_photo_url ?? '')

// ESTADO
const open = ref(false)
const dropdownRoot = ref(null)

// LOGOUT (AGORA CORRETO PARA INERTIA)
function logout() {
  router.post('/logout')
}

// FECHAR AO CLICAR FORA
function onClickOutside(e) {
  if (!dropdownRoot.value || !open.value) {
return
}

  if (dropdownRoot.value.contains(e.target)) {
return
}

  open.value = false
}

onMounted(() => {
  document.addEventListener('click', onClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', onClickOutside)
})
</script>

<template>
  <div ref="dropdownRoot" class="contents">

    <!-- =============================== -->
    <!-- NÃO LOGADO -->
    <!-- =============================== -->
    <div v-if="!auth" class="flex items-center gap-4">
      <a :href="loginUrl" class="hover:underline">Login</a>
      <a v-if="hasRegister" :href="registerUrl" class="hover:underline">Criar Conta</a>
    </div>

    <!-- =============================== -->
    <!-- LOGADO -->
    <!-- =============================== -->
    <div v-else class="relative">

      <!-- BOTÃO -->
      <button
        type="button"
        class="flex items-center gap-2 px-3 py-2 hover:bg-gray-800 rounded"
        @click="open = !open"
      >
        <img
          v-if="userPhoto"
          class="size-8 rounded-full object-cover"
          :src="userPhoto"
          :alt="userName"
        />

        <span v-else>{{ userName || 'Conta' }}</span>
      </button>

      <!-- DROPDOWN -->
      <Transition
        enter-active-class="transition duration-200"
        enter-from-class="opacity-0 scale-95"
        enter-to-class="opacity-100 scale-100"
        leave-active-class="transition duration-100"
        leave-from-class="opacity-100 scale-100"
        leave-to-class="opacity-0 scale-95"
      >
        <div
          v-show="open"
          :class="[
            'absolute z-50 w-65 border-b border-zinc-600 rounded-lg  text-zinc-100 shadow-xl',
            props.dropUp
              ? 'bottom-full left-0 mb-2 w-full min-w-[12rem]' : 'top-full right-0 mt-2',
          ]"
        >
          <!-- USER INFO -->
          <div class="border-b border-zinc-700 px-4 py-3">
            <div class="font-medium">{{ userName }}</div>
            <div class="text-sm text-zinc-400">{{ userEmail }}</div>
          </div>

          <!-- PERFIL -->
          <a
            :href="profileUrl"
            class="block px-4 py-2 hover:bg-zinc-800"
            @click="open = false"
          >
            Perfil
          </a>

          <!-- LOGOUT -->
          <button
            type="button"
            @click="logout"
            class="w-full px-4 py-2 text-left text-red-400 hover:bg-zinc-800"
          >
            Sair
          </button>
        </div>
      </Transition>
    </div>
  </div>
</template>