<?php

namespace App\Enums;

enum EnquiryStatus: string
{
    case NEW = 'new';
    case UNDER_REVIEW = 'under_review';
    case QUOTED = 'quoted';
    case APPROVED = 'approved';
    case CONFIRMED = 'confirmed';
    case NOT_APPROVED = 'not_approved';

    public function label(): string
    {
        return match($this) {
            self::NEW => 'New',
            self::UNDER_REVIEW => 'Under Review',
            self::QUOTED => 'Quoted',
            self::APPROVED => 'Approved',
            self::CONFIRMED => 'Confirmed',
            self::NOT_APPROVED => 'Not Approved',
        };
    }

    public function customerLabel(): string
    {
        return match($this) {
            self::NEW => 'Received',
            self::UNDER_REVIEW, self::QUOTED, self::APPROVED => 'In Discussion',
            self::CONFIRMED => 'Order Placed',
            self::NOT_APPROVED => 'Closed',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
