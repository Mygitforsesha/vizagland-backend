<?php

namespace App\Modules\Property\Models;

use App\Modules\User\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

class PropertySearchHistory extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'property_search_histories';

    public $timestamps = false;

    public function getKeyName()
    {
        if (Schema::hasTable($this->getTable())) {
            if (Schema::hasColumn($this->getTable(), 'property_search_history_id')) {
                return 'property_search_history_id';
            }

            if (Schema::hasColumn($this->getTable(), 'id')) {
                return 'id';
            }
        }

        return $this->primaryKey;
    }

    public function getPropertySearchHistoryIdAttribute(): mixed
    {
        return $this->getAttribute('property_search_history_id')
            ?? $this->getAttribute('id')
            ?? $this->getKey();
    }

    /**
     * @var list<string>
     */
    protected $fillable = [
        'property_search_history_user_id',
        'property_search_history_keyword',
        'property_search_history_filters',
        'property_search_history_results_count',
        'property_search_history_ip_address',
        'property_search_history_mobile_number',
        'property_search_history_created_at',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'property_search_history_filters' => 'array',
            'property_search_history_results_count' => 'integer',
            'property_search_history_created_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'property_search_history_user_id', 'user_id');
    }

    public function recordedAt(): ?Carbon
    {
        $value = $this->getAttribute('property_search_history_created_at')
            ?? $this->getAttribute('created_at');

        if ($value === null) {
            return null;
        }

        return $value instanceof Carbon ? $value : Carbon::parse($value);
    }

    protected static function booted(): void
    {
        static::creating(function (self $history): void {
            if (
                Schema::hasColumn($history->getTable(), 'property_search_history_created_at')
                && $history->getAttribute('property_search_history_created_at') === null
            ) {
                $history->setAttribute('property_search_history_created_at', now());
            }
        });
    }
}
