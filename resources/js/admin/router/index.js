import { createRouter, createWebHistory } from 'vue-router';
const Login = () => import('../views/auth/Login.vue');
const UserList = () => import('../views/users/UserList.vue');
const OrderList = () => import('../views/orders/OrderList.vue');
const Dashboard = () => import('../views/dashboard/Dashboard.vue');
const RoleList = () => import('../views/roles/RoleList.vue');
const RoleForm = () => import('../views/roles/RoleForm.vue');
const Settings = () => import('../views/settings/CompanySettings.vue');
const Profile = () => import('../views/profile/Profile.vue');
const AdminLayout = () => import('../layouts/AdminLayout.vue');
const PlaceholderView = () => import('../views/PlaceholderView.vue');

const routes = [
    {
        path: '/login',
        name: 'Login',
        component: Login,
        meta: { guest: true }
    },
    {
        path: '/live-operations',
        name: 'LiveOperations',
        component: () => import('../views/dashboard/LiveOperations.vue'),
        meta: { requiresAuth: true }
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
                path: 'reports',
                name: 'ReportsOverview',
                component: () => import('../views/reports/ReportsOverview.vue'),
                meta: { module: 'Order' }
            },
            {
                path: 'reports/orders',
                name: 'OrderReports',
                component: () => import('../views/reports/OrderReports.vue'),
                meta: { module: 'Order' }
            },
            {
                path: 'reports/invoices',
                name: 'InvoiceReports',
                component: () => import('../views/reports/InvoiceReports.vue'),
                meta: { module: 'Order' }
            },
            {
                path: 'reports/profit-loss',
                name: 'ProfitLossReports',
                component: () => import('../views/reports/ProfitLossReports.vue'),
                meta: { module: 'Order' }
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
