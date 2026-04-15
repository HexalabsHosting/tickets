<?php

namespace FyWolf\Tickets\Filament\Admin\Resources\TicketCategories;

use App\Filament\Components\Tables\Columns\DateTimeColumn;
use FyWolf\Tickets\Filament\Admin\Resources\TicketCategories\Pages\CreateTicketCategory;
use FyWolf\Tickets\Filament\Admin\Resources\TicketCategories\Pages\EditTicketCategory;
use FyWolf\Tickets\Filament\Admin\Resources\TicketCategories\Pages\ListTicketCategories;
use FyWolf\Tickets\Filament\Admin\Resources\TicketCategories\RelationManagers\CategoryFieldsRelationManager;
use FyWolf\Tickets\Models\TicketCategory;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class TicketCategoryResource extends Resource
{
    protected static ?string $model = TicketCategory::class;

    protected static string|\BackedEnum|null $navigationIcon = 'tabler-category';

    protected static string|\UnitEnum|null $navigationGroup = 'Tickets';

    protected static ?int $navigationSort = 97;

    public static function getNavigationLabel(): string
    {
        return trans('tickets::tickets.categories');
    }

    public static function getModelLabel(): string
    {
        return trans_choice('tickets::tickets.category_model', 1);
    }

    public static function getPluralModelLabel(): string
    {
        return trans_choice('tickets::tickets.category_model', 2);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->schema([
                TextInput::make('name')
                    ->label(trans('tickets::tickets.category_field_name'))
                    ->required()
                    ->maxLength(255),
                Select::make('parent_id')
                    ->label(trans('tickets::tickets.category_field_parent'))
                    ->placeholder(trans('tickets::tickets.category_field_parent_placeholder'))
                    ->options(fn (?TicketCategory $record) => TicketCategory::whereNull('parent_id')
                        ->when($record?->id, fn ($q) => $q->where('id', '!=', $record->id))
                        ->orderBy('sort_order')
                        ->orderBy('name')
                        ->pluck('name', 'id')
                    )
                    ->searchable(),
                Select::make('color')
                    ->label(trans('tickets::tickets.category_field_color'))
                    ->options([
                        'primary' => trans('tickets::tickets.color_primary'),
                        'success' => trans('tickets::tickets.color_success'),
                        'warning' => trans('tickets::tickets.color_warning'),
                        'danger'  => trans('tickets::tickets.color_danger'),
                        'info'    => trans('tickets::tickets.color_info'),
                        'gray'    => trans('tickets::tickets.color_gray'),
                    ])
                    ->selectablePlaceholder(false)
                    ->default('primary'),
                Select::make('icon')
                    ->label(trans('tickets::tickets.category_field_icon'))
                    ->placeholder('tabler-help-circle')
                    ->helperText(trans('tickets::tickets.category_field_icon_help'))
                    ->searchable()
                    ->prefixIcon(fn ($state): ?string => filled($state) ? $state : null)
                    ->getSearchResultsUsing(function (string $search): array {
                        $svgPath = base_path('vendor/secondnetwork/blade-tabler-icons/resources/svg');
                        $term    = strtolower(Str::remove('tabler-', trim($search)));

                        return collect(glob("$svgPath/*.svg"))
                            ->map(fn (string $file): string => 'tabler-' . basename($file, '.svg'))
                            ->when(filled($term), fn ($c) => $c->filter(fn (string $icon) => str_contains($icon, $term)))
                            ->take(50)
                            ->mapWithKeys(fn (string $icon): array => [$icon => $icon])
                            ->toArray();
                    })
                    ->getOptionLabelUsing(fn ($value): string => (string) $value),
                TextInput::make('sort_order')
                    ->label(trans('tickets::tickets.category_field_sort'))
                    ->numeric()
                    ->minValue(0)
                    ->default(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                TextColumn::make('name')
                    ->label(trans('tickets::tickets.category_field_name'))
                    ->description(fn (TicketCategory $cat) => $cat->parent?->name)
                    ->searchable()
                    ->sortable()
                    ->grow(),
                TextColumn::make('parent.name')
                    ->label(trans('tickets::tickets.category_field_parent'))
                    ->placeholder('—')
                    ->badge()
                    ->color('gray')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('color')
                    ->label(trans('tickets::tickets.category_field_color'))
                    ->badge()
                    ->color(fn (TicketCategory $cat) => $cat->color)
                    ->formatStateUsing(fn (string $state) => trans("tickets::tickets.color_{$state}"))
                    ->toggleable(),
                TextColumn::make('icon')
                    ->label(trans('tickets::tickets.category_field_icon'))
                    ->placeholder('—')
                    ->icon(fn (TicketCategory $cat) => $cat->icon)
                    ->toggleable(),
                TextColumn::make('children_count')
                    ->label(trans('tickets::tickets.category_subcategory_count'))
                    ->counts('children')
                    ->badge()
                    ->color('gray')
                    ->toggleable(),
                TextColumn::make('tickets_count')
                    ->label(trans('tickets::tickets.category_ticket_count'))
                    ->counts('tickets')
                    ->badge()
                    ->color('primary')
                    ->toggleable(),
                TextColumn::make('sort_order')
                    ->label(trans('tickets::tickets.category_field_sort'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                DateTimeColumn::make('updated_at')
                    ->label(trans('tickets::tickets.updated_at'))
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->disabled(fn (TicketCategory $cat) => $cat->children_count > 0 || $cat->tickets_count > 0),
            ])
            ->toolbarActions([
                CreateAction::make(),
            ])
            ->emptyStateIcon('tabler-category')
            ->emptyStateHeading(trans('tickets::tickets.no_categories'))
            ->emptyStateDescription('');
    }

    public static function getRelations(): array
    {
        return [
            CategoryFieldsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListTicketCategories::route('/'),
            'create' => CreateTicketCategory::route('/create'),
            'edit'   => EditTicketCategory::route('/{record}/edit'),
        ];
    }
}
