<?php

namespace FyWolf\Tickets\Filament\Admin\Resources\TicketCategories\Pages;

use FyWolf\Tickets\Filament\Admin\Resources\TicketCategories\TicketCategoryResource;
use Filament\Resources\Pages\ListRecords;

class ListTicketCategories extends ListRecords
{
    protected static string $resource = TicketCategoryResource::class;
}
