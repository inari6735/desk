<?php

namespace App\Enum;

enum Permission: string
{
    // Users
    case USER_VIEW = 'USER_VIEW';
    case USER_EDIT = 'USER_EDIT';
    case USER_DELETE = 'USER_DELETE';

    // Admin
    case ADMIN_ACCESS = 'ADMIN_ACCESS';

    // Content
    case CONTENT_CREATE = 'CONTENT_CREATE';
    case CONTENT_EDIT = 'CONTENT_EDIT';
    case CONTENT_DELETE = 'CONTENT_DELETE';

    public function label(): string
    {
        return match ($this) {
            self::USER_VIEW => 'View users',
            self::USER_EDIT => 'Edit users',
            self::USER_DELETE => 'Delete users',
            self::ADMIN_ACCESS => 'Access admin panel',
            self::CONTENT_CREATE => 'Create content',
            self::CONTENT_EDIT => 'Edit content',
            self::CONTENT_DELETE => 'Delete content',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function choices(): array
    {
        $choices = [];
        foreach (self::cases() as $case) {
            $choices[$case->label()] = $case->value;
        }

        return $choices;
    }
}
