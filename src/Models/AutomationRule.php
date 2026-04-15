<?php

namespace FyWolf\Tickets\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property string $trigger
 * @property array $conditions
 * @property array $actions
 * @property bool $active
 * @property int $sort_order
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class AutomationRule extends Model
{
    protected $table = 'ticket_automation_rules';

    protected $fillable = [
        'name',
        'trigger',
        'conditions',
        'actions',
        'active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'conditions' => 'array',
            'actions'    => 'array',
            'active'     => 'boolean',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true);
    }

    public function scopeForTrigger(Builder $query, string $trigger): Builder
    {
        return $query->where('trigger', $trigger);
    }
}
