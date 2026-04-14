<?php

namespace FyWolf\Tickets\Filament\Admin\Resources\CannedResponses\Pages;

use FyWolf\Tickets\Filament\Admin\Resources\CannedResponses\CannedResponseResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

class CreateCannedResponse extends CreateRecord
{
    protected static string $resource = CannedResponseResource::class;

    protected static bool $canCreateAnother = false;

    protected function getHeaderActions(): array
    {
        return [
            $this->getCreateFormAction()->formId('form')
                ->tooltip(fn (Action $action) => $action->getLabel())
                ->hiddenLabel()
                ->icon('tabler-plus'),
        ];
    }

    protected function getFormActions(): array
    {
        return [];
    }
}
