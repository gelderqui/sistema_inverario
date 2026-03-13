<script setup>
import { ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';

import { useAuthStore } from '@/stores/auth';

const authStore = useAuthStore();
const route = useRoute();
const router = useRouter();

const form = ref({
    email: 'admin@pos.local',
    password: 'password',
    remember: true,
});

const errorMessage = ref('');

async function submit() {
    errorMessage.value = '';

    try {
        await authStore.login(form.value);
        await router.push(route.query.redirect || { name: 'dashboard' });
    } catch (error) {
        errorMessage.value = error.response?.data?.message
            || error.response?.data?.errors?.email?.[0]
            || 'No fue posible iniciar sesión.';
    }
}
</script>

<template>
    <div class="card border-0 shadow-lg">
        <div class="card-body p-4 p-lg-5">
            <div class="text-center mb-4">
                <span class="badge text-bg-dark mb-3">Inicio de la implementación</span>
                <h1 class="h3 mb-2">Sistema POS e Inventario</h1>
                <p class="text-body-secondary mb-0">Autenticación SPA con Sanctum y roles base.</p>
            </div>

            <div v-if="errorMessage" class="alert alert-danger" role="alert">
                {{ errorMessage }}
            </div>

            <form class="d-grid gap-3" @submit.prevent="submit">
                <div>
                    <label class="form-label" for="email">Correo</label>
                    <input id="email" v-model="form.email" type="email" class="form-control form-control-lg" required>
                </div>

                <div>
                    <label class="form-label" for="password">Contraseña</label>
                    <input id="password" v-model="form.password" type="password" class="form-control form-control-lg" required>
                </div>

                <label class="form-check">
                    <input v-model="form.remember" class="form-check-input" type="checkbox">
                    <span class="form-check-label">Mantener sesión</span>
                </label>

                <button type="submit" class="btn btn-dark btn-lg" :disabled="authStore.loading">
                    <span v-if="authStore.loading" class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>
                    Ingresar
                </button>
            </form>
        </div>
    </div>
</template>