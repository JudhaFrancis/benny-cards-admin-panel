# Order Management Flow Documentation

This document describes the end-to-end lifecycle of an order in the Benny Cards Admin Panel, from initial creation to final delivery and payment processing.

## 1. Overview of Order Lifecycle

The order lifecycle is divided into several high-level statuses and detailed tracking stages.

### Order Statuses
Located in `app/Enums/OrderStatus.php`:
- `Pending`: Initial state after creation.
- `Processing`: Order is being actively worked on.
- `Designing Process`: Currently in the design phase.
- `Printing Process`: Currently in the printing phase.
- `Packaging Process`: Currently in the packaging/logistics phase.
- `Completed`: All operations finished and order delivered.
- `Cancelled`: Order was aborted.
- `Refunded`: Payment returned to the customer.

---

## 2. Tracking Stages & Sequential Flow

The "Order Tracking" system provides granular control over the production pipeline. It is divided into 12 sections across 5 major backend relations.

### Sequential Stage Triggering
The system enforces a sequential flow handled by `OrderService::updateTracking`. When a stage is marked as **'Completed'**, the next stage is automatically initialized in **'Pending'** status.

1.  **Client Information** (`clientInformation`)
    - Sections: `Order Details`, `Client info`, `Card Specs`.
    - *Trigger*: Completing this stage initializes **Designing**.
2.  **Designing** (`designing`)
    - Sections: `Work Assign`, `Design Print`.
    - *Trigger*: Completing this stage initializes **Printing**.
3.  **Printing** (`printing`)
    - Sections: `Order Printing`.
    - *Trigger*: Completing this stage initializes **Packaging**.
4.  **Packaging** (`packaging`)
    - Sections: `Logistics`, `Status`.
    - *Trigger*: Completing this stage initializes **Dispatch & Delivery**.
5.  **Dispatch & Delivery** (`dispatchDelivery`)
    - Sections: `Location`, `Mode`, `Details`.

### Final Stage: Payment
- **Payment** (`payments` relation)
  - Unlocked only after **Dispatch Details** is completed.
  - Handles multiple payment entries, transaction IDs, and payment methods.

---

## 3. Backend Architecture

### Core Files
- **Controller**: `app/Http/Controllers/Admin/OrderController.php`
- **Service**: `app/Services/OrderService.php` (Contains all business logic)
- **Model**: `app/Models/Order.php`
- **Enums**: `app/Enums/OrderStatus.php`

### Data Persistence
Tracking data is stored as **JSON objects** within specific relational tables. This allows for flexible data structures without extensive schema migrations.

| Relation | Model | Primary JSON Fields |
| :--- | :--- | :--- |
| `clientInformation` | `OrderClientInformation` | `order_details`, `client_info`, `card_specs` |
| `designing` | `OrderDesigning` | `work_assign`, `design_print` |
| `printing` | `OrderPrinting` | `printing_status` |
| `packaging` | `OrderPackaging` | `packaging_logistics`, `packaging_status` |
| `dispatchDelivery` | `OrderDispatchDelivery` | `delivery_location`, `dispatch_mode`, `dispatch_details` |

---

## 4. Frontend Architecture (Vue.js)

### Core Components
- **Order List**: `resources/js/admin/views/orders/OrderList.vue`
- **Order Edit Dialog**: `resources/js/admin/components/orders/OrderEditDialog.vue` (General Info)
- **Tracking Editor**: `resources/js/admin/components/orders/edit-tabs/EditOrderTracking.vue`
- **Validation Composable**: `resources/js/admin/composables/useOrderValidation.js`

### UI Logic
- **Section Locking**: Sections are disabled unless the previous section is completed or it already has data in the database.
- **Smart Unlocking**: The Payment section specifically unlocks only when the preceding "Dispatch Details" is finished.
- **Validation**: Enforced via `useOrderValidation.js`. Mandatory fields (marked with `*` in UI) must be filled before a section can be saved or a stage marked as "Completed".
- **Real-time Totals**: Calculations for Subtotal, Discount, Extra Charges, and Grand Total are handled via computed properties in `OrderEditDialog.vue`.

---

## 5. Key Interactions

### Creating an Order
1.  Admin opens `OrderCreateDialog`.
2.  Selects products (via `Combobox` with search).
3.  Fills customer contact details.
4.  On save, `OrderService::createOrder` runs, generating a unique `ORD-ID-DATE` number and initializing the first tracking stage.

### Tracking an Order
1.  Admin selects "Track" from the Order List.
2.  Opens `EditOrderTracking.vue`.
3.  Users progress through tabs (Details -> Client -> Specs ...).
4.  Each `Save & Next` call updates the specific JSON field in the backend via `OrderController@updateTracking`.
5.  Changing status to "Completed" triggers the next major stage (e.g., Printing starts after Designing ends).
