<template>
    <div v-if="showShell" class="d-flex min-vh-100 bg-light">
        <aside
            v-if="sidebarVisible"
            class="bg-dark text-white p-3"
            style="width: 280px;"
        >
            <div class="mb-4">
                <h1 class="h5 mb-0">{{ nombreSistema }}</h1>
            </div>

            <nav class="nav nav-pills flex-column gap-1">
                <template v-for="item in visibleNavigationItems" :key="item.name">
                    <!-- Grupo con sub-items -->
                    <template v-if="item.children">
                        <button
                            type="button"
                            class="nav-group-btn nav-link d-flex align-items-center gap-2 text-white w-100"
                            @click="toggleGroup(item.name)"
                        >
                            <FontAwesomeIcon :icon="item.icon" fixed-width />
                            <span class="flex-grow-1">{{ item.label }}</span>
                            <FontAwesomeIcon
                                :icon="isGroupOpen(item.name) ? 'fa-solid fa-chevron-down' : 'fa-solid fa-chevron-right'"
                                class="small opacity-75"
                            />
                        </button>
                        <template v-if="isGroupOpen(item.name)">
                            <router-link
                                v-for="child in visibleChildren(item)"
                                :key="child.name"
                                :to="{ name: child.name }"
                                class="nav-link d-flex align-items-center gap-2 ps-4"
                                :class="route.name === child.name ? 'active' : 'text-white-50'"
                            >
                                <FontAwesomeIcon :icon="child.icon" fixed-width />
                                <span>{{ child.label }}</span>
                            </router-link>
                        </template>
                    </template>

                    <!-- Item regular -->
                    <router-link
                        v-else
                        :to="item.disabled ? '#' : { name: item.name }"
                        class="nav-link d-flex align-items-center gap-2"
                        :class="[
                            route.name === item.name ? 'active' : 'text-white',
                            { 'opacity-50 pe-none': item.disabled },
                        ]"
                        :aria-disabled="item.disabled"
                    >
                        <FontAwesomeIcon :icon="item.icon" fixed-width />
                        <span>{{ item.label }}</span>
                        <span v-if="item.disabled" class="badge text-bg-secondary ms-auto">Pronto</span>
                    </router-link>
                </template>
            </nav>
        </aside>

        <div class="flex-grow-1 d-flex flex-column" :class="sidebarVisible ? '' : 'w-100'">
            <header class="bg-white border-bottom px-3 px-md-4 py-3 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-3">
                    <button type="button" class="btn btn-outline-brand" @click="toggleSidebar">
                        <FontAwesomeIcon :icon="sidebarVisible ? 'fa-solid fa-bars-staggered' : 'fa-solid fa-bars'" />
                    </button>

                    <div>
                        <p class="small text-body-secondary mb-1">Sesion activa</p>
                        <strong>{{ authStore.user?.name }}</strong>
                    </div>
                </div>

                <button type="button" class="btn btn-outline-brand" @click="logout">
                    <FontAwesomeIcon icon="fa-solid fa-right-from-bracket" class="me-2" />
                    Cerrar sesion
                </button>
            </header>

            <main class="flex-grow-1 p-3 p-md-4">
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

<script setup>
import { computed, ref, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';

import axios from '@/bootstrap';
import { useAuthStore } from '@/stores/auth';
import { navigationItems } from '@/utils/navigation';

const authStore = useAuthStore();
const route = useRoute();
const router = useRouter();
const nombreSistema = ref('Sistema POS e Inventario');
const configuracionCargada = ref(false);
const sidebarVisible = ref(true);

const showShell = computed(() => authStore.isAuthenticated && route.name !== 'login');

const openGroups = ref([]);

const visibleNavigationItems = computed(() =>
    navigationItems.filter((item) => {
        if (item.children) {
            return item.children.some((child) => authStore.hasAnyPermission(child.permissions));
        }
        return authStore.hasAnyPermission(item.permissions);
    })
);

function toggleGroup(name) {
    const idx = openGroups.value.indexOf(name);
    if (idx >= 0) {
        openGroups.value.splice(idx, 1);
    } else {
        openGroups.value.push(name);
    }
}

function isGroupOpen(name) {
    return openGroups.value.includes(name);
}

function visibleChildren(item) {
    return item.children.filter((child) => authStore.hasAnyPermission(child.permissions));
}

// Auto-expandir grupo cuando la ruta activa es un hijo
watch(
    () => route.name,
    (routeName) => {
        for (const item of navigationItems) {
            if (item.children?.some((child) => child.name === routeName)) {
                if (!openGroups.value.includes(item.name)) {
                    openGroups.value.push(item.name);
                }
            }
        }
    },
    { immediate: true }
);

watch(
    showShell,
    async (isVisible) => {
        if (!isVisible || configuracionCargada.value) {
            return;
        }

        try {
            const { data } = await axios.get('/configuraciones/publicas');
            nombreSistema.value = data?.nombre_empresa ?? nombreSistema.value;
            configuracionCargada.value = true;
        } catch {
            // Usa valor por defecto si falla la carga de configuraciones.
        }
    }
);

function toggleSidebar() {
    sidebarVisible.value = !sidebarVisible.value;
}

async function logout() {
    await authStore.logout();
    await router.push({ name: 'login' });
}
</script>
