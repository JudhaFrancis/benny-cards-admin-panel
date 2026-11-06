<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Order;
use App\Models\OrderEnquiry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Administration → Branches, plus the branch list used by every branch picker / filter.
 *
 * Reading the list is open to every admin-panel user (pickers need it).
 * Creating, editing and deleting need the Branch → Create / Edit / Delete permission.
 */
class BranchController extends Controller
{
    protected function denyUnless(Request $request, string $action): ?JsonResponse
    {
        $user = $request->user();
        $user?->loadMissing(['role.permissions.module', 'role.permissions.action']);

        $allowed = $user && (
            strtolower($user->role?->name ?? '') === 'super-admin'
            || collect($user->permission_names)->contains("branch-{$action}")
        );

        return $allowed ? null : response()->json([
            'success' => false,
            'message' => 'You do not have permission to ' . $action . ' branches.',
        ], 403);
    }

    /**
     * GET /branches                 all branches (Branches page; pickers)
     * GET /branches?active=1        active only
     * GET /branches?name=ngl        filter by name
     * GET /branches?status=active   active | inactive
     */
    public function index(Request $request): JsonResponse
    {
        $branches = Branch::query()
            ->when($request->boolean('active'), fn ($q) => $q->active())
            ->when($request->filled('name'), fn ($q) => $q->where('name', 'like', '%' . trim($request->input('name')) . '%'))
            ->when(in_array($request->input('status'), ['active', 'inactive'], true),
                fn ($q) => $q->where('is_active', $request->input('status') === 'active'))
            ->with(['addedBy:id,name', 'modifiedBy:id,name'])
            ->withCount(['orders', 'enquiries'])
            ->ordered()
            ->get();

        return response()->json(['success' => true, 'data' => $branches]);
    }

    public function show(int $id): JsonResponse
    {
        $branch = Branch::with(['addedBy:id,name', 'modifiedBy:id,name'])->withCount(['orders', 'enquiries'])->findOrFail($id);

        return response()->json(['success' => true, 'data' => $branch]);
    }

    public function store(Request $request): JsonResponse
    {
        if ($denied = $this->denyUnless($request, 'create')) {
            return $denied;
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'is_active' => 'nullable|boolean',
        ]);
        $name = trim($data['name']);

        // Names are unique: re-use a previously deleted branch with the same name
        $branch = Branch::withTrashed()->where('name', $name)->first();
        if ($branch && !$branch->trashed()) {
            return response()->json(['success' => false, 'message' => "Branch \"{$name}\" already exists."], 422);
        }

        if ($branch) {
            $branch->restore();
            $branch->update(['is_active' => $data['is_active'] ?? true, 'modified_by' => $request->user()->id]);
        } else {
            $branch = Branch::create([
                'name' => $name,
                'is_active' => $data['is_active'] ?? true,
                'sort_order' => (int) Branch::withTrashed()->max('sort_order') + 1,
                'added_by' => $request->user()->id,
                'modified_by' => $request->user()->id,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => "Branch \"{$name}\" added.",
            'data' => $branch->load(['addedBy:id,name', 'modifiedBy:id,name'])->loadCount(['orders', 'enquiries']),
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        if ($denied = $this->denyUnless($request, 'edit')) {
            return $denied;
        }

        $branch = Branch::findOrFail($id);
        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:100', Rule::unique('branches', 'name')->ignore($branch->id)],
            'is_active' => 'sometimes|boolean',
            'sort_order' => 'sometimes|integer|min:0',
        ]);

        // Orders and enquiries link by branch_id, so a rename shows everywhere automatically.
        // order_details.order_placed_in is separate free text and is not touched.
        if (isset($data['name'])) {
            $data['name'] = trim($data['name']);
        }
        $branch->update($data + ['modified_by' => $request->user()->id]);

        return response()->json([
            'success' => true,
            'message' => 'Branch updated.',
            'data' => $branch->fresh(['addedBy:id,name', 'modifiedBy:id,name'])->loadCount(['orders', 'enquiries']),
        ]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        if ($denied = $this->denyUnless($request, 'delete')) {
            return $denied;
        }

        $branch = Branch::findOrFail($id);

        // Count soft-deleted records too, so a restored order/enquiry never points at a deleted branch
        $orderCount = Order::withTrashed()->where('branch_id', $branch->id)->count();
        $enquiryCount = OrderEnquiry::withTrashed()->where('branch_id', $branch->id)->count();
        if ($orderCount + $enquiryCount > 0) {
            return response()->json([
                'success' => false,
                'message' => "\"{$branch->name}\" is used by {$orderCount} orders and {$enquiryCount} enquiries. Deactivate it instead of deleting.",
            ], 422);
        }

        $branch->update(['modified_by' => $request->user()->id]);
        $branch->delete();

        return response()->json(['success' => true, 'message' => "Branch \"{$branch->name}\" deleted."]);
    }
}
