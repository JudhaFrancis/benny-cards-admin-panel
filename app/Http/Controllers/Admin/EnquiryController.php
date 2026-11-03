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
     * Display a listing of enquiries.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) ($request->get('per_page') ?: $request->get('limit') ?: 20);
        $result = $this->enquiryService->listEnquiries($request->all(), $perPage);

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }

    /**
     * Store a newly created enquiry.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'customer_name' => 'required|string|max:191',
            'customer_phone' => 'required|string|max:50',
            'customer_email' => 'nullable|email|max:191',
            'customer_address' => 'nullable|string',
            'occasion' => 'nullable|string|max:191',
            'source' => 'nullable|string|max:50',
            'priority' => 'nullable|string|in:P1,P2,P3,P4',
            'card_type' => 'nullable|string',
            'expected_delivery_date' => 'nullable|date',
            'items.*.product_id' => 'nullable|integer',
            'items.*.product_name' => 'required_with:items|string',
            'items.*.product_image' => 'nullable|string',
            'items.*.quantity' => 'nullable|integer|min:1',
            'items.*.unit_price' => 'nullable|numeric|min:0',
        ]);

        $enquiry = $this->enquiryService->createEnquiry($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Enquiry logged successfully.',
            'data' => $enquiry,
        ], 201);
    }

    /**
     * Display the specified enquiry.
     */
    public function show(int $id): JsonResponse
    {
        $enquiry = OrderEnquiry::with([
            'items.product',
            'activities.creator',
            'addedBy',
            'modifiedBy',
            'convertedOrder.customerDetails'
        ])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $enquiry,
        ]);
    }

    /**
     * Update the specified enquiry.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $enquiry = OrderEnquiry::findOrFail($id);

        $request->validate([
            'customer_name' => 'nullable|string|max:191',
            'customer_phone' => 'nullable|string|max:50',
            'customer_email' => 'nullable|email|max:191',
            'priority' => 'nullable|string|in:P1,P2,P3,P4',
            'items' => 'nullable|array',
        ]);

        $updated = $this->enquiryService->updateEnquiry($enquiry, $request->all());

        return response()->json([
            'success' => true,
            'message' => 'Enquiry updated successfully.',
            'data' => $updated,
        ]);
    }

    /**
     * Remove the specified enquiry.
     */
    public function destroy(int $id): JsonResponse
    {
        $enquiry = OrderEnquiry::findOrFail($id);
        $enquiry->delete();

        return response()->json([
            'success' => true,
            'message' => 'Enquiry deleted successfully.',
        ]);
    }

    /**
     * Change enquiry status.
     */
    public function changeStatus(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'status' => 'required|string|in:new,under_review,quoted,approved,confirmed,not_approved',
            'note' => 'nullable|string',
            'quoted_amount' => 'nullable|numeric|min:0',
            'lost_reason' => 'nullable|string',
        ]);

        $enquiry = OrderEnquiry::findOrFail($id);

        $updated = $this->enquiryService->changeStatus(
            $enquiry,
            $request->input('status'),
            $request->input('note'),
            $request->input('quoted_amount'),
            $request->input('lost_reason')
        );

        return response()->json([
            'success' => true,
            'message' => 'Enquiry status updated successfully.',
            'data' => $updated,
        ]);
    }

    /**
     * Add activity (call, WhatsApp, note).
     */
    public function addActivity(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'type' => 'required|string|in:call,whatsapp,note,quote',
            'note' => 'required|string',
            'next_follow_up_at' => 'nullable|date',
            'to_status' => 'nullable|string',
            'quoted_amount' => 'nullable|numeric|min:0',
        ]);

        $enquiry = OrderEnquiry::findOrFail($id);
        $activity = $this->enquiryService->addActivity($enquiry, $request->all());

        return response()->json([
            'success' => true,
            'message' => 'Follow-up activity recorded.',
            'data' => $activity,
        ]);
    }

    /**
     * Convert enquiry to an active order.
     */
    public function convert(Request $request, int $id): JsonResponse
    {
        $enquiry = OrderEnquiry::with('items')->findOrFail($id);

        try {
            $order = $this->enquiryService->convertToOrder($enquiry, $request->all());

            return response()->json([
                'success' => true,
                'message' => "Enquiry {$enquiry->enquiry_number} successfully converted to Order #{$order->order_number}.",
                'data' => [
                    'order' => $order,
                    'enquiry' => $enquiry->fresh(['convertedOrder', 'activities']),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Public endpoint for website enquiry form submissions.
     */
    public function publicStore(Request $request): JsonResponse
    {
        // Simple honeypot check to prevent bot spam
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
            'occasion' => 'nullable|string|max:191',
            'card_type' => 'nullable|string',
            'quantity' => 'nullable|integer|min:1',
            'message' => 'nullable|string|max:1000',
            'product_id' => 'nullable|exists:products,id',
            'product_name' => 'nullable|string|max:191',
        ]);

        $data = [
            'customer_name' => $request->input('customer_name'),
            'customer_phone' => $request->input('customer_phone'),
            'customer_email' => $request->input('customer_email'),
            'occasion' => $request->input('occasion', 'wedding'),
            'card_type' => $request->input('card_type', 'customize'),
            'message' => $request->input('message'),
            'source' => 'website',
            'status' => 'new',
            'priority' => 'P4',
            'user_id' => auth('sanctum')->id() ?: $request->input('user_id'),
        ];

        // Attach item if from a product page
        if ($request->filled('product_name') || $request->filled('product_id')) {
            $data['items'] = [
                [
                    'product_id' => $request->input('product_id'),
                    'product_name' => $request->input('product_name') ?: 'Selected Wedding Card',
                    'quantity' => (int) ($request->input('quantity') ?: 1),
                    'unit_price' => (float) ($request->input('unit_price') ?: 0),
                ]
            ];
        }

        $enquiry = $this->enquiryService->createEnquiry($data);

        return response()->json([
            'success' => true,
            'message' => 'Thank you! Your enquiry has been received.',
            'enquiry_number' => $enquiry->enquiry_number,
        ], 201);
    }
}
