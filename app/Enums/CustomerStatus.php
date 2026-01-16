<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use Filament\Support\Contracts\HasColor;

enum CustomerStatus: string implements HasLabel, HasColor
{
    case ACTIVE = 'active';
    case PASSIVE = 'passive';
    case LEAD = 'lead';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::ACTIVE => 'Aktif Müşteri',
            self::PASSIVE => 'Pasif',
            self::LEAD => 'Potansiyel (Lead)',
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::ACTIVE => 'success',
            self::PASSIVE => 'danger',
            self::LEAD => 'warning',
        };
    }
}