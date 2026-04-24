<?php

namespace App\Enums;

enum OrderStatus: string
{
    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
    case REFUNDED = 'refunded';
    case DESIGNING_PROCESS = 'designing process';
    case PRINTING_PROCESS = 'printing process';
    case PACKAGING_PROCESS = 'packaging process';

    public function label(): string
    {
        return match($this) {
            self::PENDING => 'Pending',
            self::PROCESSING => 'Processing',
            self::COMPLETED => 'Completed',
            self::CANCELLED => 'Cancelled',
            self::REFUNDED => 'Refunded',
            self::DESIGNING_PROCESS => 'Designing Process',
            self::PRINTING_PROCESS => 'Printing Process',
            self::PACKAGING_PROCESS => 'Packaging Process',
        };
    }
}
