<template>
    <div class="min-vh-100 d-flex align-items-center justify-content-center bg-light px-3 py-5">
        <div class="w-100" style="max-width: 440px;">
            <div class="card border-0 shadow-lg rounded-4">
                <div class="card-body p-4 p-sm-5">

                    <div class="text-center mb-4">
                        <div class="bg-dark text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width:56px;height:56px;">
                            <FontAwesomeIcon icon="fa-solid fa-store" size="lg" />
                        </div>
                        <h1 class="h4 fw-bold mb-1">{{ nombreSistema }}</h1>
                    </div>

                    <div v-if="errorMessage" class="alert alert-danger d-flex align-items-center gap-2" role="alert">
                        <FontAwesomeIcon icon="fa-solid fa-circle-exclamation" />
                        <span>{{ errorMessage }}</span>
                    </div>

                    <form class="d-grid gap-3" novalidate @submit.prevent="submit">

                        <div>
                            <label class="form-label fw-semibold" for="username">Usuario</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <FontAwesomeIcon icon="fa-solid fa-user" />
                                </span>
                                <input
                                    id="username"
                                    v-model="form.username"
                                    type="text"
                                    class="form-control form-control-lg"
                                    autocomplete="username"
                                    placeholder=""
                                    required
                                >
                            </div>
                        </div>

                        <div>
                            <label class="form-label fw-semibold" for="password">Contraseña</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <FontAwesomeIcon icon="fa-solid fa-lock" />
                                </span>
                                <input
                                    id="password"
                                    v-model="form.password"
                                    :type="showPassword ? 'text' : 'password'"
                                    class="form-control form-control-lg"
                                    autocomplete="current-password"
                                    placeholder=""
                                    required
                                >
                                <button
                                    type="button"
                                    class="btn btn-outline-secondary"
                                    :title="showPassword ? 'Ocultar contraseña' : 'Mostrar contraseña'"
                                    @click="showPassword = !showPassword"
                                >
                                    <FontAwesomeIcon :icon="showPassword ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye'" />
                                </button>
                            </div>
                        </div>

                        <div class="form-check">
                            <input id="remember" v-model="form.remember" class="form-check-input" type="checkbox">
                            <label class="form-check-label" for="remember">Mantener sesión iniciada</label>
                        </div>

                        <button type="submit" class="btn btn-dark btn-lg w-100 mt-1" :disabled="authStore.loading">
                            <span v-if="authStore.loading" class="spinner-border spinner-border-sm me-2" aria-hidden="true" />
                            <FontAwesomeIcon v-else icon="fa-solid fa-right-to-bracket" class="me-2" />
                            Ingresar
                        </button>

                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';

import axios from '@/bootstrap';
import { useAuthStore } from '@/stores/auth';

const authStore = useAuthStore();
const route = useRoute();
const router = useRouter();

const form = ref({
    username: '',
    password: '',
    remember: false,
});

const errorMessage = ref('');
const showPassword = ref(false);
const nombreSistema = ref('Sistema POS e Inventario');

onMounted(async () => {
    try {
        const { data } = await axios.get('/configuraciones/publicas');
        nombreSistema.value = data?.nombre_empresa ?? nombreSistema.value;
    } catch {
        // Usa el nombre de respaldo cuando las configuraciones aún no están disponibles.
    }
});

async function submit() {
    errorMessage.value = '';

    try {
        await authStore.login(form.value);
        await router.push(route.query.redirect ?? { name: 'dashboard' });
    } catch (error) {
        errorMessage.value =
            error.response?.data?.message ??
            error.response?.data?.errors?.username?.[0] ??
            'No fue posible iniciar sesión.';
    }
}
</script>