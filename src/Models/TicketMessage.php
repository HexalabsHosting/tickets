<?php

namespace FyWolf\Tickets\Models;

use App\Models\User;
use Carbon\Carbon;
use FyWolf\Tickets\Enums\MessageType;
use FyWolf\Tickets\Services\WebhookService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $message
 * @property bool $hidden
 * @property MessageType $type
 * @property int $ticket_id
 * @property Ticket $ticket
 * @property ?int $author_id
 * @property ?User $author
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class TicketMessage extends Model
{
    protected $fillable = [
        'message',
        'hidden',
        'type',
        'attachments',
        'ticket_id',
        'author_id',
    ];

    protected function casts(): array
    {
        return [
            'type'        => MessageType::class,
            'attachments' => 'array',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $model) {
            $model->author_id ??= auth()->user()?->id;
        });

        static::created(function (self $model) {
            $ticket = $model->ticket;
            if (!$ticket || $model->type === MessageType::Note) {
                return;
            }

            if ($ticket->first_replied_at === null && $model->author_id !== $ticket->author_id) {
                $ticket->update(['first_replied_at' => $model->created_at]);
            }

            if ($model->author_id === $ticket->author_id) {
                WebhookService::send('new_reply', $ticket, $model->message);
            }
        });
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class, 'ticket_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
