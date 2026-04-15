<?php

namespace FyWolf\Tickets\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $category_id
 * @property string $label
 * @property string $key
 * @property string $type
 * @property ?array $options
 * @property bool $required
 * @property int $sort_order
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class TicketCategoryField extends Model
{
    protected $table = 'ticket_category_fields';

    protected $fillable = [
        'category_id',
        'label',
        'key',
        'type',
        'options',
        'required',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'options'  => 'array',
            'required' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(TicketCategory::class, 'category_id');
    }

    public function toFormComponent(): \Filament\Forms\Components\Field
    {
        $base = match ($this->type) {
            'number'   => \Filament\Forms\Components\TextInput::make("custom_field_values.{$this->key}")->numeric(),
            'textarea' => \Filament\Forms\Components\Textarea::make("custom_field_values.{$this->key}"),
            'select'   => \Filament\Forms\Components\Select::make("custom_field_values.{$this->key}")
                ->options(collect($this->options ?? [])->pluck('label', 'value')->toArray()),
            'toggle'   => \Filament\Forms\Components\Toggle::make("custom_field_values.{$this->key}"),
            default    => \Filament\Forms\Components\TextInput::make("custom_field_values.{$this->key}"),
        };

        return $base
            ->label($this->label)
            ->required($this->required);
    }
}
