# Branches

Branches are the shops the business runs (NGL, MTM, TVL, Chennai, Online, Madurai, …).
They live in their own `branches` table. Orders and enquiries link to a branch by ID.

Before this change the list was a JSON array in `settings.branches`.

---

## 1. Branch vs. Order Placed In — two separate fields

| | **Branch** (`branch_id`) | **Order Placed In** (`order_placed_in`) |
|---|---|---|
| Stored in | `orders.branch_id` and `order_enquiries.branch_id` (foreign keys to `branches.id`, `ON DELETE SET NULL`) | `order_client_information.order_details.order_placed_in`, as **text only** |
| Chosen in | **Create Order** and **Create / Edit Enquiry** forms (required **Branch** dropdown) | Order edit → Client Information → "Order Placed In" radio buttons (and the mobile app) |
| Options | Active branches | Active branch names, saved as plain text |
| Used by | Monitor / Dashboard branch filter, branch order and enquiry counts, the branch delete guard | Client Information table column and filter, free-text order search |

The two are **not linked**: changing Order Placed In never changes `branch_id`, and renaming a branch never rewrites order_placed_in text.

Existing orders got their `branch_id` once, from their order_placed_in name, when the branches table was created (section 6). After that the two are set independently.

### Flow
```
Create Enquiry (sales) ── Branch ──► order_enquiries.branch_id
        │ admin approves
        ▼
Order created ─────────── branch_id copied ──► orders.branch_id
Create Order (admin) ──── Branch ──► orders.branch_id
Direct order's auto-created enquiry ◄── copies orders.branch_id

Order edit → Client Information:
  Order Placed In  ──► order_details.order_placed_in   (text)
  Order Taken By   ──► order_details.order_taken_by    (text: pick a staff name or type any name)
```

### Order Taken By
`OrderDetailsSection.vue` uses `SearchableDropdown` with `allow-custom`. You can search and pick a staff member, or type any name and choose **Use "…"**. Either way the name is saved as **text** in `order_details.order_taken_by`. `allow-custom` is off by default, so other dropdowns are unchanged.

---

## 2. Data model

| Table / column | Purpose |
|---|---|
| `branches` | `id`, `name` (unique), `is_active`, `sort_order`, `added_by`, `modified_by`, timestamps, soft delete |
| `orders.branch_id` | The order's branch |
| `order_enquiries.branch_id` | The enquiry's branch, copied to the order on approval |

---

## 3. Managing branches (Administration → Branches)

A module of its own at `/branches`, in the sidebar under **Administration** (Users, Roles, **Branches**, Settings).

| File | Purpose |
|---|---|
| `resources/js/admin/views/branches/BranchList.vue` | Table: S.No, Branch, Status, Orders, Created, Modified, Actions. Filters by branch name and status. |
| `resources/js/admin/views/branches/BranchModal.vue` | Add / Edit form: name and status (Active / Inactive) |
| Info modal (in `BranchList.vue`) | Name, status, order and enquiry counts, display order, created / modified by and at |

| Action | Behaviour |
|---|---|
| Add Branch | Creates the branch. Re-adding the name of a deleted branch restores it. Duplicate names are rejected. |
| Edit | Rename and/or set Active / Inactive. Orders and enquiries link by ID, so a rename shows everywhere. Inactive branches are hidden from pickers but keep their orders. |
| View | Info modal with audit details |
| Delete | Only allowed when **no** orders or enquiries (including soft-deleted ones) use the branch. Otherwise the button is disabled; set the branch to Inactive instead. |

After any change the shared branch list is reloaded, so pickers and filters show it straight away.

### Permissions
Module **Branch** with actions List, View, Create, Edit and Delete. These are granted to Admin and super-admin; give other roles access in role settings.
- **The sidebar link and route** need `branch-list`. The buttons need View, Create, Edit or Delete.
- **On the server**, `POST`, `PUT` and `DELETE /branches` check Create, Edit and Delete (403 otherwise). `GET /branches` stays open to every admin-panel user, because the order forms and filters need it.

---

## 4. Where branches are used

| Screen | File | Field |
|---|---|---|
| Administration → Branches | `views/branches/BranchList.vue`, `BranchModal.vue` | Branch list |
| Create Order / Create & Edit Enquiry → **Branch** (required) | `components/orders/OrderFormDialog.vue` | `branch_id` |
| Enquiry list (under the enquiry number) and enquiry info modal | `views/enquiries/EnquiryList.vue`, `components/enquiries/EnquiryInfoDialog.vue` | `branch.name` |
| Monitor → Selected Branch | `views/monitor/MonitorView.vue` | `branch_id` on `/orders` and `/dashboard/stats` |
| Order edit → Client Information → Order Placed In | `.../client-information/OrderDetailsSection.vue` | `order_placed_in` text, using active branch names as options |
| Client Information table → Order Placed In column and filter | `components/order-management/OrderStageTable.vue` | `order_placed_in` text (+ "Not Assigned" = `n/a`) |

The branch list is loaded once through `resources/js/admin/composables/useBranches.js` (`GET /api/v1/branches`).

---

## 5. API

All routes are under `/api/v1`, behind `auth:sanctum` + `is_admin`.

| Method | Path | Purpose |
|---|---|---|
| GET | `/branches` | All branches with `orders_count`, `enquiries_count`, `added_by` and `modified_by`. Filters: `?active=1`, `?name=`, `?status=active\|inactive`. |
| GET | `/branches/{id}` | One branch with the same details |
| POST | `/branches` | `{ name, is_active? }`. Needs Branch → Create. |
| PUT | `/branches/{id}` | `{ name?, is_active?, sort_order? }`. Needs Branch → Edit. |
| DELETE | `/branches/{id}` | Soft delete (422 if used by orders or enquiries). Needs Branch → Delete. |

`branch_id` is required on `POST /orders`, `POST /enquiries` and `PUT /enquiries/{id}` when sent. The public website enquiry form (`POST /enquiries/public`) doesn't take a branch.

### Filters
- **Branch:** `GET /orders?branch_id=3`, `GET /dashboard/stats?branch_id=3`. `branch_id=n/a` means orders with no branch. `branch=NGL` (a name) still works for older callers.
- **Order Placed In:** `GET /orders?client_information_order_details_order_placed_in=TVL` matches the text exactly. `n/a` means empty.

---

## 6. Migrations

`2026_11_05_190000_create_branches_table.php`:
1. Creates `branches` and copies the list from `settings.branches`, keeping its order and active flags. Any other name found in order_placed_in is added as inactive.
2. Adds `orders.branch_id` and fills it **once** from each order's order_placed_in name. On the local database that linked 1,363 orders; 48 had no name.
3. Drops `settings.branches`.
4. Adds the **Branch** permission module for Admin and super-admin.

`2026_11_05_200000_add_branch_id_to_order_enquiries_table.php`: adds `order_enquiries.branch_id`. Enquiries already converted to an order take that order's branch.

---

## 7. Mobile app (separate project)

`benny-cards-mobile-app/src/pages/ManagementEdit.tsx` sets **Order Placed In** (text) from a **hard-coded** list (`NGL, MTM, TVL, Chennai, Online`), so Madurai can't be picked there. That still saves correctly, because order_placed_in is plain text. To offer every branch, it should load `GET /api/v1/branches?active=1`.
