<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <Head title="AgendaToDo" />
    <div class="flex min-h-screen flex-col bg-gray-100">
        <!-- HEADER -->
        <header class="flex items-center justify-between px-6 py-4">
             <!-- Logo -->
            <div class="flex items-center gap-2">
                <img src="/images/logo.png" alt="AgendaToDo Logo" class="h-8 w-auto" />
                <h1 class="text-xl font-semibold text-gray-800">AgendaToDo</h1>
            </div>

            <!-- Menu 
            <nav class="hidden items-center gap-8 text-sm md:flex">
                <Link href="#" class="font-semibold text-gray-800 hover:text-blue-500">Funcionalidades</Link>
                <Link href="#" class="font-semibold text-gray-800 hover:text-blue-500">Templates</Link>
                <Link href="#" class="font-semibold text-gray-800 hover:text-blue-500">Recursos</Link>
                <Link href="#" class="font-semibold text-gray-800 hover:text-blue-500">Para Equipas</Link>
                <Link href="#" class="font-semibold text-gray-800 hover:text-blue-500">Preços</Link>
                <Link href="#" class="font-semibold text-gray-800 hover:text-blue-500">Demonstração</Link>
            </nav>-->


            <!-- Botões -->
            <div class="flex items-center gap-4">
                <Link href="/login" class="font-medium text-gray-800 hover:text-blue-500">Entrar</Link>
                <Link href="/register" class="rounded-lg bg-blue-600 px-5 py-2.5 font-medium text-white hover:bg-blue-700">Criar conta</Link>
            </div>
        </header>

        <!-- HERO -->
        <main class="flex flex-1 items-center justify-center px-12 py-24">
            <div class="w-full max-w-6xl space-y-12 text-center">
                <!-- TEXTO -->
                <div class="space-y-8">
                    <h2
                        class="max-w-5xl mx-auto font-sans text-[70px] font-extrabold leading-[1.1] tracking-tight text-gray-900"
                    >
                        Organize suas tarefas com clareza e foco.<br />
                    </h2>

                    <p
                        class="mx-auto max-w-4xl text-2xl text-gray-700 md:text-3xl"
                    >
                     O AgendaToDo combina simplicidade e automação para te
                        ajudar a executar mais, com menos esforço.
                    </p>
                </div>

                <!-- IMAGEM -->
                <div class="mt-12">
                    <img
                        src="/images/img2.svg"
                        alt="App preview"
                        class="mx-auto h-auto w-full max-w-4xl"
                    />
                </div>
            </div>
        </main>

        <!-- FOOTER -->
        <footer class="py-6 text-center text-sm text-gray-500">
            © {{ new Date().getFullYear() }} AgendaToDo. Todos os direitos
            reservados.
        </footer>
    </div>
</template>
