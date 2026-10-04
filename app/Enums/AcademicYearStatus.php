<?php

namespace App\Enums;

enum AcademicYearStatus: string
{
    case Draft = 'draft';
    case Active = 'active';
    case Archived = 'archived';

    /**
     * Allowed forward transitions. archived is a terminal state — never a key here.
     *
     * @return array<string, array<int, string>>
     */
    public static function allowedTransitions(): array
    {
        return [
            self::Draft->value => [self::Active->value, self::Archived->value],
            self::Active->value => [self::Archived->value],
            self::Archived->value => [],
        ];
    }

    public function canTransitionTo(self $target): bool
    {
        return in_array($target->value, self::allowedTransitions()[$this->value], true);
    }
}