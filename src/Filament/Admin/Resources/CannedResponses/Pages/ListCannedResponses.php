<?php

namespace FyWolf\Tickets\Filament\Admin\Resources\CannedResponses\Pages;

use FyWolf\Tickets\Filament\Admin\Resources\CannedResponses\CannedResponseResource;
use Filament\Resources\Pages\ListRecords;

class ListCannedResponses extends ListRecords
{
    protected static string $resource = CannedResponseResource::class;
}
