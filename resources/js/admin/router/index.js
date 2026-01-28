import { createRouter, createWebHistory } from 'vue-router';
import Login from '../views/auth/Login.vue';
import OrderList from '../views/orders/OrderList.vue';
import Dashboard from '../views/dashboard/Dashboard.vue';
import Profile from '../views/profile/Profile.vue';
import AdminLayout from '../layouts/AdminLayout.vue';
import PlaceholderView from '../views/PlaceholderView.vue';

const routes = [
    {
        path: '/login',
        name: 'Login',
        component: Login,
        meta: { guest: true }
    },
    {
        path: '/',
        component: AdminLayout,
        meta: { requiresAuth: true },
        children: [
            {
                path: '',
                name: 'Dashboard',
                component: Dashboard
            },
            {
                path: 'profile',
                name: 'Profile',
                component: Profile
            },
            {
                path: 'orders',
                name: 'Orders',
                component: OrderList
            },
            {
                path: 'invoices',
                name: 'Invoices',
                component: PlaceholderView
            },
            {
                path: 'payments',
                name: 'Payments',
                component: PlaceholderView
            },
            {
                path: 'reports/payments',
                name: 'PaymentReports',
                component: PlaceholderView
            },
            {
                path: 'reports/invoices',
                name: 'InvoiceReports',
                component: PlaceholderView
            },
            {
                path: 'reports/orders',
                name: 'OrderReports',
                component: PlaceholderView
            }
        ]
    },
    {
        // Default redirect
        path: '/:pathMatch(.*)*',
        redirect: '/'
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
            next({ name: 'Dashboard' });
        } else {
            next();
        }
    } else {
        next();
    }
});

export default router;
