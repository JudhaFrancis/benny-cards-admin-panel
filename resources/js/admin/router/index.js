import { createRouter, createWebHistory } from 'vue-router';
import Login from '../views/auth/Login.vue';
import UserList from '../views/users/UserList.vue';
import OrderList from '../views/orders/OrderList.vue';
import Dashboard from '../views/dashboard/Dashboard.vue';
import RoleList from '../views/roles/RoleList.vue';
import RoleForm from '../views/roles/RoleForm.vue';
import Settings from '../views/settings/CompanySettings.vue';
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
                component: () => import('../views/products/ProductList.vue'),
                meta: { module: 'Product' }
            },
            {
                path: 'orders',
                name: 'Orders',
                component: OrderList,
                meta: { module: 'Order' }
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
                name: 'users.index',
                component: UserList,
                meta: { title: 'Users', module: 'User' }
            },
            {
                path: 'roles',
                name: 'roles.index',
                component: RoleList,
                meta: { title: 'Roles', module: 'Role' }
            },
            {
                path: 'roles/create',
                name: 'roles.create',
                component: RoleForm,
                meta: { title: 'Create Role', module: 'Role' }
            },
            {
                path: 'roles/:id/edit',
                name: 'roles.edit',
                component: RoleForm,
                meta: { title: 'Edit Role', module: 'Role' }
            },
            {
                path: 'catalog/categories',
                name: 'Categories',
                component: () => import('../views/categories/CategoryList.vue'),
                meta: { module: 'Category' }
            },
            {
                path: 'catalog/price-ranges',
                name: 'Price Ranges',
                component: () => import('../views/price-ranges/PriceRangeList.vue'),
                meta: { module: 'Price Range' }
            },
            {
                path: 'catalog/brands',
                name: 'Brands',
                component: () => import('../views/brands/BrandList.vue'),
                meta: { module: 'Brands' }
            },
            {
                path: 'catalog/coupons',
                name: 'Coupons',
                component: () => import('../views/coupons/CouponList.vue'),
                meta: { module: 'Coupons' }
            },
            {
                path: 'catalog/banners',
                name: 'Banners',
                component: () => import('../views/banners/BannerList.vue'),
                meta: { module: 'Banners' }
            },
            {
                path: 'catalog/reviews',
                name: 'Reviews',
                component: () => import('../views/reviews/ReviewList.vue'),
                meta: { module: 'Review' }
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
    const user = JSON.parse(localStorage.getItem('auth_user') || 'null');
    const permissions = user?.permission_names || user?.permissions || [];
    const roleName = user?.role?.name?.toLowerCase() || '';
    const isSuperAdmin = roleName === 'super-admin';

    if (to.matched.some(record => record.meta.requiresAuth)) {
        if (!token) {
            next({ name: 'Login' });
            return;
        }

        // Check Module Permission
        const requiredModule = to.meta.module;
        if (requiredModule && !isSuperAdmin) {
            const hasPermission = permissions.includes(`${requiredModule.toLowerCase()}-list`);
            if (!hasPermission) {
                console.warn(`Access denied to module: ${requiredModule}`);
                next({ name: 'Dashboard' });
                return;
            }
        }

        next();
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
