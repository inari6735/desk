<?php

namespace App\Enum;

enum ActionType: string
{
    case DEVELOPMENT = 'development';
    case TESTING = 'testing';
    case BUG_FIXING = 'bug_fixing';
    case RESEARCH = 'research';
    case PLANNING = 'planning';
    case MEETINGS = 'meetings';
    case SUPPORT = 'support';
    case DEVOPS = 'devops';
    case DOCUMENTATION = 'documentation';

    public function label(): string
    {
        return match ($this) {
            self::DEVELOPMENT => 'Development',
            self::TESTING => 'Testing',
            self::BUG_FIXING => 'Bug Fixing',
            self::RESEARCH => 'Research',
            self::PLANNING => 'Planning',
            self::MEETINGS => 'Meetings',
            self::SUPPORT => 'Support',
            self::DEVOPS => 'DevOps',
            self::DOCUMENTATION => 'Documentation',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::DEVELOPMENT => '#0f62fe',
            self::TESTING => '#198038',
            self::BUG_FIXING => '#da1e28',
            self::RESEARCH => '#8a3ffc',
            self::PLANNING => '#005d5d',
            self::MEETINGS => '#ee5396',
            self::SUPPORT => '#0072c3',
            self::DEVOPS => '#fa4d56',
            self::DOCUMENTATION => '#6f6f6f',
        };
    }
}
