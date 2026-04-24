<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
 
class WhatsAppLogController extends Controller
{
    /**
     * Display a listing of whatsapp logs.
     */
    public function index(Request $request): JsonResponse
    {
        $limit = $request->get('limit', 15);
        $search = $request->get('search');

        $query = Notification::where('message_type', 'whatsapp')->latest();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('recipient_mobile_no', 'like', "%{$search}%")
                    ->orWhere('message', 'like', "%{$search}%");
            });
        }

        return response()->json([
            'success' => true,
            'data' => $query->paginate($limit),
        ]);
    }

    /**
     * Resend a failed message
     */
    public function resend($id, WhatsAppService $whatsAppService): JsonResponse
    {
        $log = Notification::find($id);
        if (!$log) {
            return response()->json(['success' => false, 'message' => 'Log not found'], 404);
        }

        // Re-process the API call logic using the SAME record (Pranav style)
        $result = $whatsAppService->resend($log);

        return response()->json(['success' => $result['success'], 'message' => $result['message']]);
    }
}
