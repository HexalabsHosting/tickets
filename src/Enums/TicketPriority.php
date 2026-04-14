<?php

namespace FyWolf\Tickets\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum TicketPriority: string implements HasColor, HasIcon, HasLabel
{
    case Low = 'low';
    case Normal = 'normal';
    case High = 'high';
    case VeryHigh = 'very_high';

    public function getIcon(): string
    {
        return match ($this) {
            self::Low => 'tabler-chevron-down',
            self::Normal => 'tabler-menu',
            self::High => 'tabler-chevron-up',
            self::VeryHigh => 'tabler-chevrons-up',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Low => 'success',
            self::Normal => 'primary',
            self::High => 'warning',
            self::VeryHigh => 'danger',
        };
    }

    public function getLabel(): string
    {
        return str($this->value)->headline();
    }
}
