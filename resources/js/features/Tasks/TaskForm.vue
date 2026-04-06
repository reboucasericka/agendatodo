<script setup lang="ts">
import { computed, nextTick, reactive, ref, watch } from 'vue';

const props = defineProps({
    modelValue: { type: Object, default: null },
    loading: { type: Boolean, default: false },
});

const emit = defineEmits<{
    (
        e: 'submit',
        payload: {
            title: string;
            description: string | null;
            priority: string;
            due_date: string | null;
        },
    ): void;
    (e: 'cancel'): void;
}>();

const form = reactive({
    title: '',
    description: '',
    priority: 'medium',
    due_date: '',
});

const errors = reactive<{ title?: string }>({});
const titleRef = ref<HTMLInputElement | null>(null);
const isEditing = computed(() => Boolean((props.modelValue as { id?: number } | null)?.id));

function normalizeDueDate(raw: string | null | undefined): string {
    if (raw == null || raw === '') {
        return '';
    }

    if (typeof raw === 'string') {
        return raw.length >= 10 ? raw.slice(0, 10) : raw;
    }

    return '';
}

function applyTask(task: { title?: string; description?: string | null; priority?: string; due_date?: string | null } | null) {
    form.title = task?.title ?? '';
    form.description = task?.description ?? '';
    form.priority = task?.priority ?? 'medium';
    form.due_date = normalizeDueDate(task?.due_date ?? undefined);
    errors.title = undefined;

    nextTick(() => {
        titleRef.value?.focus();
    });
}

watch(
    () => props.modelValue,
    (task) => applyTask(task as { title?: string; description?: string | null; priority?: string; due_date?: string | null } | null),
    { immediate: true },
);

function validate(): boolean {
    errors.title = undefined;

    if (!form.title.trim()) {
        errors.title = 'O titulo e obrigatorio.';
        nextTick(() => titleRef.value?.focus());

        return false;
    }

    return true;
}

function resetForm() {
    form.title = '';
    form.description = '';
    form.priority = 'medium';
    form.due_date = '';
    errors.title = undefined;
    nextTick(() => titleRef.value?.focus());
}

function submitForm() {
    if (props.loading) {
return;
}

    if (!validate()) {
return;
}

    emit('submit', {
        title: form.title.trim(),
        description: form.description.trim() || null,
        priority: form.priority,
        due_date: form.due_date || null,
    });

    if (!isEditing.value) {
        resetForm();
    }
}
</script>

<template>
    <form
        class="space-y-4 rounded-lg border border-zinc-200 bg-white p-4 text-gray-900 shadow-sm"
        @submit.prevent="submitForm"
    >
        <div>
            <label for="title" class="mb-1 block text-sm font-medium text-gray-700">Titulo</label>
            <input
                id="title"
                ref="titleRef"
                v-model="form.title"
                type="text"
                :aria-invalid="Boolean(errors.title)"
                :class="[
                    'w-full rounded-md border bg-white px-3 py-2 text-sm text-gray-900 outline-none transition placeholder:text-gray-400',
                    errors.title ? 'border-red-400 ring-2 ring-red-200' : 'border-gray-300 focus:ring-2 ring-indigo-500',
                ]"
                placeholder="Ex.: Estudar Laravel"
            />
            <p v-if="errors.title" class="mt-1 text-xs text-red-600">
                {{ errors.title }}
            </p>
        </div>

        <div>
            <label for="description" class="mb-1 block text-sm font-medium text-gray-700">Descricao</label>
            <textarea
                id="description"
                v-model="form.description"
                rows="3"
                class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 outline-none ring-indigo-500 transition placeholder:text-gray-400 focus:ring-2"
                placeholder="Opcional"
            />
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label for="priority" class="mb-1 block text-sm font-medium text-gray-700">Prioridade</label>
                <select
                    id="priority"
                    v-model="form.priority"
                    class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 outline-none ring-indigo-500 transition focus:ring-2"
                >
                    <option value="low">Baixa</option>
                    <option value="medium">Media</option>
                    <option value="high">Alta</option>
                </select>
            </div>

            <div>
                <label for="due_date" class="mb-1 block text-sm font-medium text-gray-700">Data de vencimento</label>
                <input
                    id="due_date"
                    v-model="form.due_date"
                    type="date"
                    class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 outline-none ring-indigo-500 transition focus:ring-2"
                />
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button
                type="submit"
                :disabled="loading"
                class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-50"
            >
                {{ loading ? 'A guardar...' : isEditing ? 'Atualizar tarefa' : 'Criar tarefa' }}
            </button>

            <button
                v-if="isEditing"
                type="button"
                :disabled="loading"
                class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-100 disabled:cursor-not-allowed disabled:opacity-50"
                @click="emit('cancel')"
            >
                Cancelar edicao
            </button>
        </div>
    </form>
</template>
