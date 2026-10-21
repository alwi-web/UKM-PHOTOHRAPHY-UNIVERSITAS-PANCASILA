<?php

namespace App\Models;

use App\Enums\EventStatus;
use App\Enums\EventType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'slug', 'type', 'description', 'location', 'start_at', 'end_at',
        'quota', 'poster', 'status', 'registration_open', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'type' => EventType::class,
            'status' => EventStatus::class,
            'start_at' => 'datetime',
            'end_at' => 'datetime',
            'registration_open' => 'boolean',
            'quota' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Event $event) {
            if (! $event->slug) {
                $event->slug = Str::slug($event->title).'-'.Str::lower(Str::random(5));
            }
        });
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withPivot('status')->withTimestamps();
    }

    public function scopeUpcoming(Builder $query): void
    {
        $query->where('status', EventStatus::Published)->where('start_at', '>=', now()->startOfDay())->orderBy('start_at');
    }
}
