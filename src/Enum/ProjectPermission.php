<?php

namespace App\Enum;

enum ProjectPermission: string
{
    case VIEW = 'PROJECT_VIEW';
    case CREATE_TODO = 'PROJECT_CREATE_TODO';
    case EDIT_TODO = 'PROJECT_EDIT_TODO';

    public function label(): string
    {
        return match ($this) {
            self::VIEW => 'View project',
            self::CREATE_TODO => 'Create todos',
            self::EDIT_TODO => 'Edit todos',
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
