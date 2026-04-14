<?php

namespace FyWolf\Tickets\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum TicketStatus: string implements HasColor, HasIcon, HasLabel
{
    case Open = 'open';
    case InProgress = 'in_progress';
    case WaitingForCustomer = 'waiting_for_customer';
    case Closed = 'closed';

    public function getIcon(): string
    {
        return match ($this) {
            self::Open => 'tabler-circle-dashed',
            self::InProgress => 'tabler-progress',
            self::WaitingForCustomer => 'tabler-clock-pause',
            self::Closed => 'tabler-circle-check',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Open => 'primary',
            self::InProgress => 'success',
            self::WaitingForCustomer => 'warning',
            self::Closed => 'danger',
        };
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::WaitingForCustomer => trans('tickets::tickets.waiting_for_customer'),
            default => str($this->value)->headline(),
        };
    }

    public function isOpen(): bool
    {
        return $this !== self::Closed;
    }
}
