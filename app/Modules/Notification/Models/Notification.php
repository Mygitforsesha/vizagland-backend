<?php

namespace App\Modules\Notification\Models;

use App\Modules\Notification\Enums\NotificationType;
use App\Modules\User\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    public const CREATED_AT = 'notification_created_at';

    public const UPDATED_AT = null;

    protected $primaryKey = 'notification_id';

    protected $table = 'notifications';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'notification_user_id',
        'notification_type',
        'notification_title',
        'notification_message',
        'notification_is_read',
        'notification_read_at',
        'notification_deleted_at',
    ];

    protected function casts(): array
    {
        return [
            'notification_type' => NotificationType::class,
            'notification_is_read' => 'boolean',
            'notification_read_at' => 'datetime',
            'notification_deleted_at' => 'datetime',
            'notification_created_at' => 'datetime',
        ];
    }

    /**
     * @param  Builder<Notification>  $query
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('notification_deleted_at');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'notification_user_id', 'user_id');
    }
}
