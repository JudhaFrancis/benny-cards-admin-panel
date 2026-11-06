<?php

namespace App\Models;

/**
 * Class Notification
 * Kept for backwards compatibility. Use NotificationLog instead.
 * 
 * @package App\Models
 */
class Notification extends NotificationLog
{
    protected $table = 'notification_logs';
}