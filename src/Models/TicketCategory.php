<?php

namespace FyWolf\Tickets\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property string $color
 * @property ?string $icon
 * @property ?int $parent_id
 * @property ?TicketCategory $parent
 * @property Collection<TicketCategory> $children
 * @property int $sort_order
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class TicketCategory extends Model
{
    protected $table = 'ticket_categories';

    protected $fillable = [
        'name',
        'color',
        'icon',
        'parent_id',
        'sort_order',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order')->orderBy('name');
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'category_id');
    }

    public function fields(): HasMany
    {
        return $this->hasMany(TicketCategoryField::class, 'category_id')->orderBy('sort_order')->orderBy('label');
    }

    public function getFullNameAttribute(): string
    {
        return $this->parent ? $this->parent->name . ' › ' . $this->name : $this->name;
    }

    public static function groupedOptions(): array
    {
        $roots = self::whereNull('parent_id')
            ->with('children')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $options = [];

        foreach ($roots as $root) {
            if ($root->children->isEmpty()) {
                $options[$root->id] = $root->name;
            } else {
                $group = [$root->id => $root->name . ' (' . trans('tickets::tickets.category_parent_label') . ')'];
                foreach ($root->children as $child) {
                    $group[$child->id] = '› ' . $child->name;
                }
                $options[$root->name] = $group;
            }
        }

        return $options;
    }
}
