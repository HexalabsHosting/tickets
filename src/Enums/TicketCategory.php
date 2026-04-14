<?php

namespace FyWolf\Tickets\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum TicketCategory: string implements HasColor, HasIcon, HasLabel
{
    case Question = 'question';
    case Issue = 'issue';
    case Feedback = 'feedback';

    public function getIcon(): string
    {
        return match ($this) {
            self::Question => 'tabler-help-circle',
            self::Issue => 'tabler-exclamation-circle',
            self::Feedback => 'tabler-user-circle',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Question => 'primary',
            self::Issue => 'danger',
            self::Feedback => 'success',
        };
    }

    public function getLabel(): string
    {
        return str($this->value)->headline();
    }
}
