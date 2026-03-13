import { createRouter, createWebHistory } from 'vue-router';

import { useAuthStore } from '@/stores/auth';
import DashboardView from '@/views/DashboardView.vue';
import LoginView from '@/views/LoginView.vue';
import UnauthorizedView from '@/views/UnauthorizedView.vue';

const routes = [
    {
        path: '/',
        name: 'dashboard',
        component: DashboardView,
        meta: {
            requiresAuth: true,
            permissions: ['dashboard.view'],
        },
    },
    {
        path: '/login',
        name: 'login',
        component: LoginView,
        meta: {
            guestOnly: true,
        },
    },
    {
        path: '/unauthorized',
        name: 'unauthorized',
        component: UnauthorizedView,
        meta: {
            requiresAuth: true,
        },
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

router.beforeEach(async (to) => {
    const authStore = useAuthStore();

    if (!authStore.initialized) {
        await authStore.initialize();
    }

    if (to.meta.requiresAuth && !authStore.isAuthenticated) {
        return {
            name: 'login',
            query: { redirect: to.fullPath },
        };
    }

    if (to.meta.guestOnly && authStore.isAuthenticated) {
        return { name: 'dashboard' };
    }

    if (to.meta.permissions && !authStore.hasAnyPermission(to.meta.permissions)) {
        return { name: 'unauthorized' };
    }

    return true;
});

export default router;
