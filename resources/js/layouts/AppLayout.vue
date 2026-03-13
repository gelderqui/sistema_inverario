<script setup>
import { computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';

import { useAuthStore } from '@/stores/auth';
import { navigationItems } from '@/utils/navigation';

const authStore = useAuthStore();
const route = useRoute();
const router = useRouter();

const showShell = computed(() => authStore.isAuthenticated && route.name !== 'login');

const visibleNavigationItems = computed(() => navigationItems.filter((item) => authStore.hasAnyPermission(item.permissions)));

async function logout() {
    await authStore.logout();
    await router.push({ name: 'login' });
}
</script>

<template>
    <div v-if="showShell" class="d-flex min-vh-100">
        <aside class="bg-dark text-white p-3" style="width: 280px;">
            <div class="mb-4">
                <p class="text-uppercase small text-white-50 mb-2">Sistema POS</p>
                <h1 class="h4 mb-1">Panel Operativo</h1>
                <p class="small text-white-50 mb-0">Laravel + Vue + CoreUI</p>
            </div>

            <nav class="nav nav-pills flex-column gap-2">
                <router-link
                    v-for="item in visibleNavigationItems"
                    :key="item.name"
                    :to="item.disabled ? '#' : { name: item.name }"
                    class="nav-link d-flex align-items-center gap-2"
                    :class="route.name === item.name ? 'active' : 'text-white'"
                    :aria-disabled="item.disabled"
                    @click.prevent="item.disabled && null"
                >
                    <FontAwesomeIcon :icon="item.icon" fixed-width />
                    <span>{{ item.label }}</span>
                    <span v-if="item.disabled" class="badge text-bg-secondary ms-auto">Pronto</span>
                </router-link>
            </nav>
        </aside>

        <div class="flex-grow-1 d-flex flex-column">
            <header class="bg-white border-bottom px-4 py-3 d-flex justify-content-between align-items-center shadow-sm">
                <div>
                    <p class="small text-body-secondary mb-1">Sesión activa</p>
                    <strong>{{ authStore.user?.name }}</strong>
                </div>

                <button type="button" class="btn btn-outline-danger" @click="logout">
                    <FontAwesomeIcon icon="fa-solid fa-right-from-bracket" class="me-2" />
                    Cerrar sesión
                </button>
            </header>

            <main class="flex-grow-1 p-4">
                <router-view />
            </main>
        </div>
    </div>

    <main v-else class="min-vh-100 d-flex align-items-center justify-content-center p-4">
        <div class="w-100" style="max-width: 480px;">
            <router-view />
        </div>
    </main>
</template>