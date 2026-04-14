<?php

namespace FyWolf\Tickets\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property string $content
 * @property ?string $category
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class CannedResponse extends Model
{
    protected $table = 'ticket_canned_responses';

    protected $fillable = [
        'name',
        'content',
        'category',
    ];
}
