<?php

namespace FyWolf\Tickets\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum MessageType: string implements HasColor, HasIcon, HasLabel
{
    case Reply = 'reply';
    case Note = 'note';

    public function getIcon(): string
    {
        return match ($this) {
            self::Reply => 'tabler-message',
            self::Note  => 'tabler-note',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Reply => 'primary',
            self::Note  => 'warning',
        };
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::Reply => trans('tickets::tickets.message_type_reply'),
            self::Note  => trans('tickets::tickets.message_type_note'),
        };
    }
}
