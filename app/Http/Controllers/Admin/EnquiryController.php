<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\EnquiryService;
use App\Models\OrderEnquiry;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class EnquiryController extends Controller
{
    public function __construct(
        protected EnquiryService $enquiryService
    ) {}

    /**
     * Same rules as OrderController::store.
     */
    protected function rules(bool $creating): array
    {
        $req = $creating ? 'required' : 'sometimes|required';

        return [
            'customer.name' => "{$req}|string|max:191",
            'customer.phone' => "{$req}|string|max:50",
            'customer.email' => 'nullable|email|max:191',
            'customer.address_1' => "{$req}|string",
            'customer.address_2' => 'nullable|string',
            'branch_id' => "{$req}|integer|exists:branches,id",
            'remarks' => 'nullable|string',
            'budget_tier' => 'nullable|string',
            'discount' => 'nullable|numeric|min:0',
            'extra_charges' => 'nullable|numeric|min:0',
            'items' => "{$req}|array|min:1",
            'items.*.product_id' => 'nullable|exists:products,id',
            'items.*.product_name' => 'required_without:items.*.product_id|nullable|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'nullable|numeric|min:0',
            'items.*.product_image' => 'nullable',
        ];
    }

    /**
     * Find an enquiry the current user is allowed to see.
     */
    protected function findVisible(Request $request, int $id, array $with = []): OrderEnquiry
    {
        return $this->enquiryService->visibleQuery($request->user())->with($with)->findOrFail($id);
    }

    protected function ensureApprover(Request $request): ?JsonResponse
    {
        if (!$this->enquiryService->canApprove($request->user())) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to approve or reject enquiries.',
            ], 403);
        }
        return null;
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = (int) ($request->get('per_page') ?: $request->get('limit') ?: 20);
        $result = $this->enquiryService->listEnquiries($request->all(), $perPage, $request->user());

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate($this->rules(true));

        $enquiry = $this->enquiryService->createEnquiry($request->all());

        return response()->json([
            'success' => true,
            'message' => "Enquiry {$enquiry->enquiry_number} submitted for approval.",
            'data' => $enquiry,
        ], 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $enquiry = $this->findVisible($request, $id, [
            'items.product',
            'branch:id,name',
            'addedBy',
            'modifiedBy',
            'approvedBy',
            'rejectedBy',
            'convertedOrder.customerDetails',
        ]);

        return response()->json([
            'success' => true,
            'data' => $enquiry,
            'can_approve' => $this->enquiryService->canApprove($request->user()),
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $enquiry = $this->findVisible($request, $id);
        $request->validate($this->rules(false));

        try {
            $updated = $this->enquiryService->updateEnquiry($enquiry, $request->all());
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Enquiry updated successfully.',
            'data' => $updated,
        ]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $enquiry = $this->findVisible($request, $id);

        if ($enquiry->status === EnquiryService::STATUS_APPROVED) {
            return response()->json([
                'success' => false,
                'message' => 'Approved enquiries are linked to an order and cannot be deleted.',
            ], 422);
        }

        $enquiry->delete();

        return response()->json([
            'success' => true,
            'message' => 'Enquiry deleted successfully.',
        ]);
    }

    /**
     * Admin approval: creates the order from the enquiry.
     */
    public function approve(Request $request, int $id): JsonResponse
    {
        if ($denied = $this->ensureApprover($request)) {
            return $denied;
        }

        $enquiry = OrderEnquiry::with(['items', 'addedBy'])->findOrFail($id);

        try {
            $order = $this->enquiryService->approve($enquiry);
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        return response()->json([
            'success' => true,
            'message' => "Enquiry {$enquiry->enquiry_number} approved. Order #{$order->order_number} created.",
            'data' => [
                'order' => $order,
                'enquiry' => $enquiry->fresh(['convertedOrder', 'approvedBy']),
            ],
        ]);
    }

    /**
     * Admin rejection with a reason. No order is created.
     */
    public function reject(Request $request, int $id): JsonResponse
    {
        if ($denied = $this->ensureApprover($request)) {
            return $denied;
        }

        $request->validate(['reason' => 'required|string|max:1000']);
        $enquiry = OrderEnquiry::findOrFail($id);

        try {
            $updated = $this->enquiryService->reject($enquiry, $request->input('reason'));
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        return response()->json([
            'success' => true,
            'message' => "Enquiry {$enquiry->enquiry_number} rejected.",
            'data' => $updated,
        ]);
    }

    /**
     * Public endpoint for website enquiry form submissions.
     */
    public function publicStore(Request $request): JsonResponse
    {
        // Honeypot: bots fill this hidden field
        if (!empty($request->input('website_check'))) {
            return response()->json([
                'success' => true,
                'message' => 'Enquiry received.',
            ]);
        }

        $request->validate([
            'customer_name' => 'required|string|max:191',
            'customer_phone' => 'required|string|max:50',
            'customer_email' => 'nullable|email|max:191',
            'customer_address' => 'nullable|string',
            'quantity' => 'nullable|integer|min:1',
            'message' => 'nullable|string|max:1000',
            'product_id' => 'nullable|exists:products,id',
            'product_name' => 'nullable|string|max:191',
        ]);

        $data = [
            'customer' => [
                'name' => $request->input('customer_name'),
                'phone' => $request->input('customer_phone'),
                'email' => $request->input('customer_email'),
                'address_1' => $request->input('customer_address'),
            ],
            'remarks' => $request->input('message'),
            'user_id' => auth('sanctum')->id(),
        ];

        if ($request->filled('product_name') || $request->filled('product_id')) {
            $data['items'] = [[
                'product_id' => $request->input('product_id'),
                'product_name' => $request->input('product_name') ?: 'Selected Wedding Card',
                'quantity' => (int) ($request->input('quantity') ?: 1),
                'unit_price' => (float) ($request->input('unit_price') ?: 0),
            ]];
        }

        $enquiry = $this->enquiryService->createEnquiry($data, null);

        return response()->json([
            'success' => true,
            'message' => 'Thank you! Your enquiry has been received.',
            'enquiry_number' => $enquiry->enquiry_number,
        ], 201);
    }
}
