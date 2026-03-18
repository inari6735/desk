<?php

namespace App\Enum;

enum Position: string
{
    case BACKEND = 'backend';
    case FRONTEND = 'frontend';
    case FULLSTACK = 'fullstack';
    case QA = 'qa';
    case DEVOPS = 'devops';
    case PM = 'pm';
    case DESIGNER = 'designer';
    case MANAGER = 'manager';
    case HR = 'hr';
    case CEO = 'ceo';

    public function label(): string
    {
        return match ($this) {
            self::BACKEND => 'Backend',
            self::FRONTEND => 'Frontend',
            self::FULLSTACK => 'Fullstack',
            self::QA => 'QA',
            self::DEVOPS => 'DevOps',
            self::PM => 'Project Manager',
            self::DESIGNER => 'Designer',
            self::MANAGER => 'Manager',
            self::HR => 'HR',
            self::CEO => 'CEO',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::BACKEND => '#4f46e5',
            self::FRONTEND => '#0891b2',
            self::FULLSTACK => '#7c3aed',
            self::QA => '#ca8a04',
            self::DEVOPS => '#ea580c',
            self::PM => '#2563eb',
            self::DESIGNER => '#db2777',
            self::MANAGER => '#059669',
            self::HR => '#9333ea',
            self::CEO => '#dc2626',
        };
    }
}
