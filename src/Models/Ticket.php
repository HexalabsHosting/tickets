<?php

namespace FyWolf\Tickets\Models;

use App\Models\Server;
use App\Models\User;
use FyWolf\Tickets\Enums\TicketPriority;
use FyWolf\Tickets\Enums\TicketStatus;
use FyWolf\Tickets\Filament\Server\Resources\Tickets\Pages\ListTickets;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Support\Markdown;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $title
 * @property ?int $category_id
 * @property ?TicketCategory $category
 * @property TicketPriority $priority
 * @property TicketStatus $status
 * @property ?string $description
 * @property ?Carbon $closed_at
 * @property int $server_id
 * @property Server $server
 * @property ?int $author_id
 * @property ?User $author
 * @property ?int $assigned_user_id
 * @property ?User $assignedUser
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Ticket extends Model
{
    protected $fillable = [
        'title',
        'category_id',
        'priority',
        'status',
        'description',
        'closed_at',
        'server_id',
        'author_id',
        'assigned_user_id',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $model) {
            $model->status    = TicketStatus::Open;
            $model->server_id ??= Filament::getTenant()?->getKey();
            $model->author_id ??= auth()->user()?->id;
        });
    }

    protected function casts(): array
    {
        return [
            'priority'  => TicketPriority::class,
            'status'    => TicketStatus::class,
            'closed_at' => 'datetime',
        ];
    }

    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class, 'server_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(TicketCategory::class, 'category_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(TicketMessage::class);
    }

    public function close(?string $answer = null): void
    {
        if ($answer) {
            $this->messages()->create([
                'message'   => $answer,
                'author_id' => auth()->user()?->id,
            ]);
        }

        $this->status    = TicketStatus::Closed;
        $this->closed_at = now();
        $this->save();

        if (
            config('tickets.notifications.ticket_closed', true) &&
            $this->author &&
            collect($this->author->directAccessibleServers()->pluck('id')->all())->contains($this->server->id)
        ) {
            Notification::make()
                ->title(trans('tickets::tickets.notifications.closed'))
                ->body($answer ? Markdown::inline($answer) : null)
                ->actions([
                    Action::make('view')
                        ->label(trans('filament-actions::view.single.label'))
                        ->button()
                        ->markAsRead()
                        ->url(fn () => ListTickets::getUrl([
                            'tab'               => 'closed',
                            'tableAction'       => 'view',
                            'tableActionRecord' => $this->id,
                        ], panel: 'server', tenant: $this->server)),
                ])
                ->sendToDatabase($this->author);
        }
    }

    public function reopen(): void
    {
        $this->status    = $this->assigned_user_id ? TicketStatus::InProgress : TicketStatus::Open;
        $this->closed_at = null;
        $this->save();

        if (config('tickets.notifications.ticket_reopened', true) && $this->assignedUser) {
            Notification::make()
                ->title(trans('tickets::tickets.notifications.reopened'))
                ->sendToDatabase($this->assignedUser);
        }
    }

    public function assignTo(User $user, bool $setStatus = true): void
    {
        $this->assigned_user_id = $user->id;

        if ($setStatus) {
            $this->status = TicketStatus::InProgress;
        }

        $this->save();

        if (config('tickets.notifications.ticket_assigned', true)) {
            Notification::make()
                ->title(trans('tickets::tickets.notifications.assigned_to_you'))
                ->success()
                ->sendToDatabase($user);
        }
    }

    public function getSlaDeadline(): ?Carbon
    {
        if (!config('tickets.sla.enabled')) {
            return null;
        }

        $hours = match ($this->priority) {
            TicketPriority::Low      => config('tickets.sla.low_hours'),
            TicketPriority::Normal   => config('tickets.sla.normal_hours'),
            TicketPriority::High     => config('tickets.sla.high_hours'),
            TicketPriority::VeryHigh => config('tickets.sla.very_high_hours'),
        };

        return $this->created_at->copy()->addHours($hours);
    }

    public function isOverdue(): bool
    {
        $deadline = $this->getSlaDeadline();

        return $this->status->isOpen() && $deadline !== null && now()->isAfter($deadline);
    }

    public function getSlaRemainingHours(): ?float
    {
        $deadline = $this->getSlaDeadline();

        if ($deadline === null) {
            return null;
        }

        return round(now()->diffInMinutes($deadline, false) / 60, 1);
    }
}
