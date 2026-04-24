<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;

class WhatsAppController extends Controller
{
    protected $whatsAppService;

    public function __construct(WhatsAppService $whatsAppService)
    {
        $this->whatsAppService = $whatsAppService;
    }

    /**
     * Send WhatsApp using Template (via Postman)
     */
    public function sendMessage(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'event' => 'required|string', // Name of event in sms_templates
            'data' => 'nullable|array',   // Placeholders to replace
        ]);

        $result = $this->whatsAppService->sendFromTemplate(
            $request->event,
            $request->phone,
            $request->data ?? []
        );

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => 'Message template fetched, sent and logged.',
                'log_id' => $result['log_id']
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Process failed.',
                'error' => $result['error'] ?? null,
                'log_id' => $result['log_id'] ?? null
            ], 400);
        }
    }
}
