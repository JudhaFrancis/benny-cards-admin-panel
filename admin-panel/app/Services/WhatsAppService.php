<?php

namespace App\Services;

use App\Models\SmsTemplate;
use App\Models\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    /**
     * Send WhatsApp using Template from sms_templates table
     */
    public function sendFromTemplate($eventName, $recipientNo, $dynamicData = [])
    {
        // 1. Fetch Template
        $template = SmsTemplate::where('event_name', $eventName)
            ->where('status', 1)
            ->first();

        if (!$template) {
            return ['success' => false, 'message' => "Template '{$eventName}' not found in DB."];
        }

        // 2. Prepare Message Content
        $messageContent = $template->whatsapp_content ?: $template->template_content;

        // 3. Replace Placeholders (Supports {key} and {{key}})
        foreach ($dynamicData as $key => $value) {
            $messageContent = str_replace('{' . $key . '}', $value, $messageContent);
            $messageContent = str_replace('{{' . $key . '}}', $value, $messageContent);
        }

        // 4. Normalize Mobile Number (Adding 91)
        $mobileNo = preg_replace('/\D/', '', $recipientNo);
        if (!str_starts_with($mobileNo, '91')) {
            $mobileNo = '91' . $mobileNo;
        }

        // 5. Create Log in notifications table (Initial Log)
        $notification = Notification::create([
            'message' => $messageContent,
            'message_type' => 'whatsapp',
            'status' => 'not_sent',
            'resend' => 'no',
            'recipient_mobile_no' => $mobileNo,
            'created_by' => auth()->id(),
        ]);

        return $this->processApiCall($notification, $mobileNo, $messageContent);
    }

    /**
     * Resend an existing notification record (Matches Pranav Flow)
     */
    public function resend(Notification $notification)
    {
        // Mark as resent
        $notification->update(['resend' => 'yes']);

        return $this->processApiCall(
            $notification, 
            $notification->recipient_mobile_no, 
            $notification->message
        );
    }

    protected function processApiCall($notification, $mobileNo, $message)
    {
        try {
            $payload = [
                'appkey' => config('services.whatsapp.appkey'),
                'authkey' => config('services.whatsapp.authkey'),
                'to' => $mobileNo,
                'message' => $message,
            ];

            $url = config('services.whatsapp.api_url');

            if (empty($url)) {
                throw new \Exception("WhatsApp API URL is not configured in .env");
            }

            $response = Http::withHeaders([
                'Accept' => '*/*',
                'Content-Type' => 'application/json',
            ])->post($url, $payload);

            $body = trim($response->body());
            $decoded = json_decode($body, true);
            
            // Checking for Success (Matching "message_status" in response)
            $isSuccess = (isset($decoded['message_status']) && strtolower($decoded['message_status']) === 'success') || 
                         str_contains(strtolower($body), 'true');

            if ($isSuccess) {
                // Extracting Sender Number from the response JSON
                $senderNumber = $decoded['data']['from'] ?? null;

                $notification->update([
                    'status' => 'sent', 
                    'response' => $body,
                    'sender_mobile_no' => $senderNumber // This MUST update now
                ]);

                return ['success' => true, 'log_id' => $notification->id, 'message' => 'Sent successfully!'];
            } else {
                throw new \Exception($body ?: "API returned no response");
            }

        } catch (\Exception $e) {
            Log::error("WhatsApp Sending Failed: " . $e->getMessage());
            $notification->update(['status' => 'failed', 'response' => $e->getMessage()]);

            return [
                'success' => false,
                'log_id' => $notification->id,
                'message' => 'Logged in DB but API error: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ];
        }
    }
}
