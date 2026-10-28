<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\WorkNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WorkNotificationController extends Controller
{
    public function __construct(
        protected WorkNotificationService $notificationService
    ) {}

    /**
     * Get assignment notifications for logged in user.
     */
    public function index(Request $request): JsonResponse
    {
        $limit = (int) $request->get('limit', 20);
        $unreadOnly = $request->boolean('unread_only', false);

        $result = $this->notificationService->getNotifications(
            $request->user(),
            $limit,
            $unreadOnly
        );

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }

    /**
     * Mark a single notification as read.
     */
    public function markRead(Request $request, string $stage, int $id): JsonResponse
    {
        $success = $this->notificationService->markAsRead($stage, $id);

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Notification marked as read.' : 'Record not found.',
        ]);
    }

    /**
     * Mark a single notification as unread.
     */
    public function markUnread(Request $request, string $stage, int $id): JsonResponse
    {
        $success = $this->notificationService->markAsUnread($stage, $id);

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Notification marked as unread.' : 'Record not found.',
        ]);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllRead(Request $request): JsonResponse
    {
        $updatedCount = $this->notificationService->markAllAsRead($request->user());

        return response()->json([
            'success' => true,
            'message' => "All notifications marked as read ({$updatedCount} updated).",
            'updated_count' => $updatedCount,
        ]);
    }
}
