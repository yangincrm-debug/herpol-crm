<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum CustomerType: string implements HasLabel
{
    case INDIVIDUAL = 'individual';
    case CORPORATE = 'corporate';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::INDIVIDUAL => 'Bireysel',
            self::CORPORATE => 'Kurumsal',
        };
    }
}