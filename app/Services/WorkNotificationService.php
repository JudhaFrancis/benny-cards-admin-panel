<?php

namespace App\Services;

use App\Models\NotificationTemplate;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class WorkNotificationService
{
    /**
     * Get work assignment notifications for the given user.
     */
    public function getNotifications(User $user, int $limit = 20, bool $unreadOnly = false): array
    {
        $isSuperAdmin = $this->isSuperAdmin($user);
        $stages = config('order_notifications.stages', []);
        
        // Fetch custom template if configured in DB, else use config default
        $template = NotificationTemplate::where('event_name', 'WORK_ASSIGNED')->value('template_content')
            ?: config('order_notifications.default_template', 'Order #{order_number} assigned to you for {stage_name} by {assigned_by}');

        $allNotifications = [];
        $unreadCount = 0;

        foreach ($stages as $key => $config) {
            $modelClass = $config['model'] ?? null;
            if (!$modelClass || !class_exists($modelClass)) {
                continue;
            }

            // Build base query with eager loads
            $query = $modelClass::with(['order:id,order_number,delivery_date,status', 'modifiedBy:id,name']);

            // Exclude Completed and Cancelled stages (work is finished/cancelled)
            $query->whereNotIn('status', ['Completed', 'Cancelled']);

            // Ensure parent order is active (not delivered or cancelled)
            $query->whereHas('order', function ($q) {
                $q->whereNotIn('status', ['delivered', 'cancelled']);
            });

            // Filter for regular staff (only assignments containing their user ID)
            if (!$isSuperAdmin) {
                $query->where(function ($q) use ($user) {
                    $q->whereJsonContains('assigned_user_ids', (int) $user->id)
                      ->orWhereJsonContains('assigned_user_ids', (string) $user->id);
                });
            }

            // Count unread for this stage
            $stageUnreadQuery = clone $query;
            $unreadCount += $stageUnreadQuery->where('notification_status', 'unread')->count();

            if ($unreadOnly) {
                $query->where('notification_status', 'unread');
            }

            $records = $query->latest('updated_at')->take($limit)->get();

            foreach ($records as $record) {
                $orderNumber = $record->order?->order_number ?? ("#" . $record->order_id);
                // Clean order number to match UI format (e.g. OD-APR26121 -> OD-APR121)
                $cleanOrderNumber = preg_replace('/-([A-Z]{3})\d{2}/', '-$1', $orderNumber);
                $assignedBy = $this->getAssignedByName($key, $record);
                $stageName = $config['name'] ?? ucfirst($key);
                $dateFormatted = $record->updated_at ? $record->updated_at->format('d M Y, h:i A') : 'N/A';

                // Build message
                $message = "Order #{$cleanOrderNumber} assigned to you for {$stageName} by {$assignedBy}";

                $allNotifications[] = [
                    'id' => $record->id,
                    'stage_key' => $key,
                    'stage_name' => $stageName,
                    'order_id' => $record->order_id,
                    'order_number' => $cleanOrderNumber,
                    'assigned_by' => $assignedBy,
                    'assigned_date' => $dateFormatted,
                    'notification_status' => $record->notification_status ?: 'unread',
                    'stage_status' => $record->status ?? 'Pending',
                    'message' => $message,
                    'route' => $config['route'] ?? "/order-management/{$key}",
                    'updated_at' => $record->updated_at?->toIso8601String(),
                    'timestamp' => $record->updated_at?->timestamp ?? 0,
                ];
            }
        }

        // Sort descending by updated_at timestamp
        usort($allNotifications, fn($a, $b) => $b['timestamp'] <=> $a['timestamp']);

        return [
            'unread_count' => $unreadCount,
            'notifications' => array_slice($allNotifications, 0, $limit),
        ];
    }

    /**
     * Mark a specific stage record's notification as read.
     */
    public function markAsRead(string $stageKey, int $recordId): bool
    {
        $stages = config('order_notifications.stages', []);
        if (!isset($stages[$stageKey])) {
            return false;
        }

        $modelClass = $stages[$stageKey]['model'] ?? null;
        if (!$modelClass || !class_exists($modelClass)) {
            return false;
        }

        $record = $modelClass::find($recordId);
        if ($record) {
            $record->update(['notification_status' => 'read']);
            return true;
        }

        return false;
    }

    /**
     * Mark a specific stage record's notification as unread.
     */
    public function markAsUnread(string $stageKey, int $recordId): bool
    {
        $stages = config('order_notifications.stages', []);
        if (!isset($stages[$stageKey])) {
            return false;
        }

        $modelClass = $stages[$stageKey]['model'] ?? null;
        if (!$modelClass || !class_exists($modelClass)) {
            return false;
        }

        $record = $modelClass::find($recordId);
        if ($record) {
            $record->update(['notification_status' => 'unread']);
            return true;
        }

        return false;
    }

    /**
     * Mark all notifications as read for the user.
     */
    public function markAllAsRead(User $user): int
    {
        $isSuperAdmin = $this->isSuperAdmin($user);
        $stages = config('order_notifications.stages', []);
        $updatedCount = 0;

        foreach ($stages as $config) {
            $modelClass = $config['model'] ?? null;
            if (!$modelClass || !class_exists($modelClass)) {
                continue;
            }

            $query = $modelClass::where('notification_status', 'unread')
                ->whereNotIn('status', ['Completed', 'Cancelled']);

            if (!$isSuperAdmin) {
                $query->where(function ($q) use ($user) {
                    $q->whereJsonContains('assigned_user_ids', (int) $user->id)
                      ->orWhereJsonContains('assigned_user_ids', (string) $user->id);
                });
            }

            $updatedCount += $query->update(['notification_status' => 'read']);
        }

        return $updatedCount;
    }

    /**
     * Resolve who assigned this work stage.
     * Prioritizes explicit assigner from stage JSON, falls back to modifiedBy or Admin.
     */
    protected function getAssignedByName(string $stageKey, $record): string
    {
        switch ($stageKey) {
            case 'client_information':
                $takenBy = $record->order_details['order_taken_by'] ?? null;
                if (!empty($takenBy)) return (string) $takenBy;
                break;

            case 'designing':
                $assignedBy = $record->work_assign['assigned_by'] ?? null;
                if (!empty($assignedBy)) return (string) $assignedBy;
                break;

            case 'packaging':
                $log = $record->packaging_logistics;
                if (is_array($log)) {
                    $multi = $log['assigned_by_multiple'] ?? null;
                    if (is_array($multi)) {
                        $filtered = array_values(array_filter($multi, fn($n) => !empty($n) && strtolower($n) !== 'none'));
                        if (!empty($filtered)) {
                            return implode(', ', $filtered);
                        }
                    }
                    if (!empty($log['assigned_by']) && strtolower($log['assigned_by']) !== 'none') {
                        return (string) $log['assigned_by'];
                    }
                }
                break;

            case 'printing':
                if (!empty($record->printing_status['assigned_by'])) {
                    return (string) $record->printing_status['assigned_by'];
                }
                $designingAssignedBy = $record->order?->designing?->work_assign['assigned_by'] ?? null;
                if (!empty($designingAssignedBy)) {
                    return (string) $designingAssignedBy;
                }
                break;

            case 'delivery':
                if (!empty($record->dispatch_mode['assigned_by'])) {
                    return (string) $record->dispatch_mode['assigned_by'];
                }
                if (!empty($record->dispatch_mode['signature_name'])) {
                    return (string) $record->dispatch_mode['signature_name'];
                }
                break;
        }

        return $record->modifiedBy?->name ?? 'Admin';
    }

    /**
     * Get the name of staff to whom the work was assigned for this stage.
     */
    protected function getAssignedToName(string $stageKey, $record): string
    {
        switch ($stageKey) {
            case 'designing':
                return $record->work_assign['assigned_to'] ?? $record->work_assign['content_by'] ?? '';
            case 'printing':
                return $record->printing_status['assigned_to'] ?? $record->order?->designing?->work_assign['assigned_to'] ?? '';
            case 'packaging':
                $log = $record->packaging_logistics;
                $names = [];
                if (is_array($log['crafted_by_multiple'] ?? null)) {
                    $names = array_merge($names, $log['crafted_by_multiple']);
                }
                if (!empty($log['crafted_by'])) {
                    $names[] = $log['crafted_by'];
                }
                return implode(', ', array_unique(array_filter($names)));
            case 'delivery':
                return $record->dispatch_mode['packed_by'] ?? $record->dispatch_mode['signature_name'] ?? '';
            default:
                return '';
        }
    }

    /**
     * Helper to check if user is Super Admin or Admin.
     */
    protected function isSuperAdmin(User $user): bool
    {
        $roleName = strtolower($user->role?->name ?? '');
        return $roleName === 'super-admin' || in_array($user->id, [1, 2]);
    }
}
