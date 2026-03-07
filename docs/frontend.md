# Frontend Implementation

The frontend is a modern SPA built with Vue 3, Vite, and Tailwind CSS. It provides a responsive and intuitive interface for managing the Scan Center.

## Frontend Project Structure

Located in `resources/js/admin`:
- `/components`: Reusable UI elements (Buttons, Modals, Tables).
- `/composables`: Shared logic using Vue 3 Composition API.
- `/layouts`: Site shell (e.g., `AdminLayout.vue`).
- `/router`: Vue Router configuration and navigation guards.
- `/views`: Page-level components.
- `/data`: Static configuration and menu items.

## Component Architecture

The UI is built using a component-first approach.
- **Shared Components:** Components like `Pagination.vue` and `SearchInput.vue` are reused across lists.
- **Headless UI & Heroicons:** Used for accessible and styled interactive elements.
- **ApexCharts:** Integrated for the dashboard and reports overview.

## Page Views & Routing

Navigation is managed in `resources/js/admin/router/index.js`.
- **Protected Routes:** Routes requiring authentication are marked with `meta: { requiresAuth: true }`.
- **Permission Guards:** The navigation guard checks `localStorage` for user permissions and redirects if the user lacks access to a specific module.

```javascript
// Navigation Guard Snippet
router.beforeEach((to, from, next) => {
    const permissions = user.permissions;
    if (to.meta.module && !permissions.includes(`${to.meta.module}-list`)) {
        next({ name: 'Dashboard' });
    }
    // ...
});
```

## State Management

While the project may use Pinia or Vuex for complex state, much of the data fetching is handled locally within components or shared via **Composables** for lightweight state management.

## UI & Styling (Tailwind CSS)

Styling is strictly utility-first using **Tailwind CSS**.
- **Responsive Design:** Mobile-first approach for accessibility on tablets and phones.
- **Configuration:** Custom themes and colors are defined in `tailwind.config.js`.

## Data Fetching

**Axios** is used for API communication.
- Global interceptors (can be found in `bootstrap.js`) attach the Sanctum token to every request.
- Centralized error handling for unauthorized (401) or forbidden (403) responses.

---
[Return to Architecture](architecture.md) | [Next: Deployment Guide](deployment.md)
