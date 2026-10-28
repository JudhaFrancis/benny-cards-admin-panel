<?php

namespace App\Models;

/**
 * Class SmsTemplate
 * Kept for backwards compatibility. Use NotificationTemplate instead.
 * 
 * @package App\Models
 */
class SmsTemplate extends NotificationTemplate
{
    protected $table = 'notification_templates';
}