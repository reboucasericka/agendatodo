<script setup>
import InputError from '@/components/InputError.vue';
import InputLabel from '@/components/InputLabel.vue';
import PrimaryButton from '@/components/PrimaryButton.vue';
import TextInput from '@/components/TextInput.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed, onMounted, ref, watch } from 'vue';

defineOptions({
    layout: AppLayout,
});

const page = usePage();
const user = page.props.auth?.user;

const props = defineProps({
    mustVerifyEmail: {
        type: Boolean,
        default: false,
    },
    canManageTwoFactor: {
        type: Boolean,
        default: false,
    },
    twoFactorEnabled: {
        type: Boolean,
        default: false,
    },
    requiresConfirmation: {
        type: Boolean,
        default: false,
    },
    pendingTwoFactorConfirmation: {
        type: Boolean,
        default: false,
    },
});

const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const submitPassword = () => {
    passwordForm.put('/user/password', {
        preserveScroll: true,
        errorBag: 'updatePassword',
        onSuccess: () => passwordForm.reset(),
    });
};

const enableForm = useForm({});
const disableForm = useForm({});
const confirmForm = useForm({ code: '' });

const enableTwoFactor = () => {
    enableForm.post('/user/two-factor-authentication', {
        preserveScroll: true,
    });
};

const disableTwoFactor = () => {
    if (!confirm('Desativar a autenticação de dois fatores?')) {
        return;
    }
    disableForm.delete('/user/two-factor-authentication', {
        preserveScroll: true,
    });
};

const submitConfirm = () => {
    confirmForm.post('/user/confirmed-two-factor-authentication', {
        preserveScroll: true,
        onSuccess: () => {
            confirmForm.reset();
            qrSvg.value = null;
            recoveryCodes.value = [];
        },
    });
};

function xsrfToken() {
    const row = document.cookie.split('; ').find((c) => c.startsWith('XSRF-TOKEN='));
    return row ? decodeURIComponent(row.split('=').slice(1).join('=')) : '';
}

async function fetchJson(url) {
    const res = await fetch(url, {
        headers: {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-XSRF-TOKEN': xsrfToken(),
        },
        credentials: 'same-origin',
    });
    if (!res.ok) {
        throw new Error(`HTTP ${res.status}`);
    }
    const text = await res.text();
    return text ? JSON.parse(text) : null;
}

const qrSvg = ref(null);
const recoveryCodes = ref([]);

async function loadQrCode() {
    if (!props.canManageTwoFactor) {
        return;
    }
    try {
        const data = await fetchJson('/user/two-factor-qr-code');
        qrSvg.value = data?.svg ?? null;
    } catch {
        qrSvg.value = null;
    }
}

async function loadRecoveryCodes() {
    if (
        !props.canManageTwoFactor ||
        (!props.twoFactorEnabled && !props.pendingTwoFactorConfirmation)
    ) {
        recoveryCodes.value = [];
        return;
    }
    try {
        const data = await fetchJson('/user/two-factor-recovery-codes');
        recoveryCodes.value = Array.isArray(data) ? data : [];
    } catch {
        recoveryCodes.value = [];
    }
}

onMounted(() => {
    if (props.pendingTwoFactorConfirmation) {
        loadQrCode();
        loadRecoveryCodes();
    } else if (props.twoFactorEnabled) {
        loadRecoveryCodes();
    }
});

watch(
    () => [props.pendingTwoFactorConfirmation, props.twoFactorEnabled],
    () => {
        if (props.pendingTwoFactorConfirmation) {
            loadQrCode();
            loadRecoveryCodes();
        } else if (props.twoFactorEnabled) {
            qrSvg.value = null;
            loadRecoveryCodes();
        } else {
            qrSvg.value = null;
            recoveryCodes.value = [];
        }
    },
);

const flashStatus = computed(() => page.props.flash?.status ?? page.props.status ?? null);
</script>

<template>
    <Head title="Perfil" />

    <div class="max-w-4xl mx-auto space-y-8">
        <div>
            <h1 class="text-2xl font-semibold text-white">Perfil</h1>
            <p class="text-gray-400 text-sm">
                Informações da conta, palavra-passe e autenticação de dois fatores
            </p>

            <p v-if="flashStatus" class="text-green-400 mt-2">
                {{ flashStatus }}
            </p>
        </div>

        <section class="bg-gray-900 border border-gray-800 rounded-xl p-6 space-y-4">
            <h2 class="text-lg font-medium text-white">Informações</h2>

            <div>
                <p class="text-gray-400 text-sm">Nome</p>
                <p class="text-white">{{ user?.name }}</p>
            </div>

            <div>
                <p class="text-gray-400 text-sm">Email</p>
                <p class="text-white">{{ user?.email }}</p>
            </div>
        </section>

        <section class="bg-gray-900 border border-gray-800 rounded-xl p-6 space-y-4">
            <h2 class="text-lg font-medium text-white">Alterar palavra-passe</h2>

            <form class="space-y-4" @submit.prevent="submitPassword">
                <div>
                    <InputLabel value="Palavra-passe atual" />
                    <TextInput
                        v-model="passwordForm.current_password"
                        type="password"
                        class="mt-1 w-full bg-gray-800 border-gray-700 text-white"
                    />
                    <InputError :message="passwordForm.errors.current_password" />
                </div>

                <div>
                    <InputLabel value="Nova palavra-passe" />
                    <TextInput
                        v-model="passwordForm.password"
                        type="password"
                        class="mt-1 w-full bg-gray-800 border-gray-700 text-white"
                    />
                    <InputError :message="passwordForm.errors.password" />
                </div>

                <div>
                    <InputLabel value="Confirmar palavra-passe" />
                    <TextInput
                        v-model="passwordForm.password_confirmation"
                        type="password"
                        class="mt-1 w-full bg-gray-800 border-gray-700 text-white"
                    />
                    <InputError :message="passwordForm.errors.password_confirmation" />
                </div>

                <PrimaryButton> Guardar </PrimaryButton>
            </form>
        </section>

        <section
            v-if="canManageTwoFactor"
            class="bg-gray-900 border border-gray-800 rounded-xl p-6 space-y-4"
        >
            <h2 class="text-lg font-medium text-white">Autenticação 2FA</h2>

            <p class="text-gray-400 text-sm">Segurança extra com Google Authenticator</p>

            <div v-if="!twoFactorEnabled && !pendingTwoFactorConfirmation">
                <PrimaryButton @click="enableTwoFactor"> Ativar 2FA </PrimaryButton>
            </div>

            <div v-if="pendingTwoFactorConfirmation" class="space-y-4">
                <div v-if="qrSvg" v-html="qrSvg" class="bg-white p-3 rounded" />

                <form class="flex gap-3" @submit.prevent="submitConfirm">
                    <TextInput v-model="confirmForm.code" />
                    <PrimaryButton>Confirmar</PrimaryButton>
                </form>
            </div>

            <div v-if="twoFactorEnabled" class="space-y-4">
                <p class="text-green-400">2FA ativo</p>

                <PrimaryButton @click="disableTwoFactor"> Desativar </PrimaryButton>
            </div>
        </section>
    </div>
</template>
