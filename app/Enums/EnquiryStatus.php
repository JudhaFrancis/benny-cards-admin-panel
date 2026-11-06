<?php

namespace App\Enums;

enum EnquiryStatus: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';

    public function label(): string
    {
        return match($this) {
            self::PENDING => 'Pending Approval',
            self::APPROVED => 'Approved',
            self::REJECTED => 'Rejected',
        };
    }

    public function customerLabel(): string
    {
        return match($this) {
            self::PENDING => 'Received',
            self::APPROVED => 'Order Placed',
            self::REJECTED => 'Closed',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
