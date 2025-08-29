<?php

namespace App\Models\Enums;

enum AgreementStatus: string
{
    case PENDING = 'pending';

    case APPROVED = 'approved';

    case REJECTED = 'rejected';

    case ACTIVE = 'active';

    case EXPIRED = 'expired';

    case TERMINATED = 'terminated';

    case ARCHIVED = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::APPROVED => 'Approved',
            self::REJECTED => 'Rejected',
            self::ACTIVE => 'Active',
            self::EXPIRED => 'Expired',
            self::TERMINATED => 'Terminated',
            self::ARCHIVED => 'Archived',
        };
    }

    public function meaning(): string
    {
        return match ($this) {
            self::PENDING => 'The agreement is pending approval.',
            self::APPROVED => 'The agreement has been approved.',
            self::REJECTED => 'The agreement has been rejected.',
            self::ACTIVE => 'The agreement is currently active.',
            self::EXPIRED => 'The agreement has expired.',
            self::TERMINATED => 'The agreement has been terminated.',
            self::ARCHIVED => 'The agreement has been archived for future reference.',
        };
    }
}
