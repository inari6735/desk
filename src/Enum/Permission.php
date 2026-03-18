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

    // Projects
    case PROJECT_CREATE = 'PROJECT_CREATE';
    case PROJECT_EDIT = 'PROJECT_EDIT';
    case PROJECT_DELETE = 'PROJECT_DELETE';

    // Todos
    case TODO_CREATE = 'TODO_CREATE';
    case TODO_EDIT = 'TODO_EDIT';
    case TODO_DELETE = 'TODO_DELETE';

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
            self::PROJECT_CREATE => 'Create projects',
            self::PROJECT_EDIT => 'Edit projects',
            self::PROJECT_DELETE => 'Delete projects',
            self::TODO_CREATE => 'Create todos',
            self::TODO_EDIT => 'Edit todos',
            self::TODO_DELETE => 'Delete todos',
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
