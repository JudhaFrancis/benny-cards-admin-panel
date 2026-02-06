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
                path: 'products',
                name: 'Products',
                component: () => import('../views/products/ProductList.vue')
            },
            {
                path: 'orders',
                name: 'Orders',
                component: OrderList
            },
            {
                path: 'orders/:id/edit',
                name: 'OrderEdit',
                component: () => import('../views/orders/OrderEdit.vue')
            },
            {
                path: 'payments',
                name: 'Payments',
                component: () => import('../views/payments/PaymentList.vue')
            },
            {
                path: 'reports/payments',
                name: 'PaymentReports',
                component: PlaceholderView
            },
            {
                path: 'reports/orders',
                name: 'OrderReports',
                component: PlaceholderView
            },
            {
                path: 'settings',
                name: 'Settings',
                component: () => import('../views/settings/CompanySettings.vue')
            },
            {
                path: 'users',
                name: 'Users',
                component: () => import('../views/users/UserList.vue')
            },
            {
                path: 'catalog/categories',
                name: 'Categories',
                component: () => import('../views/categories/CategoryList.vue')
            },
            {
                path: 'catalog/price-ranges',
                name: 'Price Ranges',
                component: () => import('../views/price-ranges/PriceRangeList.vue')
            },
            {
                path: 'catalog/brands',
                name: 'Brands',
                component: () => import('../views/brands/BrandList.vue')
            },
            {
                path: 'catalog/coupons',
                name: 'Coupons',
                component: () => import('../views/coupons/CouponList.vue')
            },
            {
                path: 'catalog/banners',
                name: 'Banners',
                component: () => import('../views/banners/BannerList.vue')
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
