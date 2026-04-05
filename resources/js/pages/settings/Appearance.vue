<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Aparência" />

    <div class="space-y-4 p-6">
        <h1 class="text-xl font-semibold">Aparência</h1>
        <p class="text-sm text-neutral-600">Tema claro / escuro pode ser ligado ao composable existente.</p>
        <Link class="text-sm text-blue-600" href="/settings/profile">Voltar ao perfil</Link>
    </div>
</template>
