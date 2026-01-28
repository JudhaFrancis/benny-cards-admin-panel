import { createRouter, createWebHistory } from 'vue-router';
import Login from '../views/auth/Login.vue';
import OrderList from '../views/orders/OrderList.vue';

const routes = [
    {
        path: '/login',
        name: 'Login',
        component: Login,
        meta: { guest: true }
    },
    {
        path: '/orders',
        name: 'Orders',
        component: OrderList,
        meta: { requiresAuth: true }
    },
    {
        // Default redirect
        path: '/:pathMatch(.*)*',
        redirect: '/orders'
    }
];

const router = createRouter({
    history: createWebHistory(),
    routes
});

// Navigation Guard
router.beforeEach((to, from, next) => {
    const token = localStorage.getItem('auth_token');

    if (to.matched.some(record => record.meta.requiresAuth)) {
        if (!token) {
            next({ name: 'Login' });
        } else {
            next();
        }
    } else if (to.matched.some(record => record.meta.guest)) {
        if (token) {
            next({ name: 'Orders' });
        } else {
            next();
        }
    } else {
        next();
    }
});

export default router;
