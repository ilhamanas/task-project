<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use HasFactory;

    public const STATUS_PLANNING = 'planning';
    public const STATUS_BERJALAN = 'berjalan';
    public const STATUS_SELESAI = 'selesai';
    public const STATUS_DITUNDA = 'ditunda';

    public const STATUSES = [
        self::STATUS_PLANNING => 'Planning',
        self::STATUS_BERJALAN => 'Berjalan',
        self::STATUS_SELESAI => 'Selesai',
        self::STATUS_DITUNDA => 'Ditunda',
    ];

    protected $fillable = [
        'nama',
        'deskripsi',
        'status',
        'pic_id',
        'tgl_mulai',
        'tgl_target',
    ];

    protected $casts = [
        'tgl_mulai' => 'date',
        'tgl_target' => 'date',
    ];

    public function pic(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pic_id');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_members')->withTimestamps();
    }

    public function features(): HasMany
    {
        return $this->hasMany(Feature::class);
    }

    public function scopeAccessibleBy(Builder $query, User $user): Builder
    {
        if ($user->isAdmin()) {
            return $query;
        }

        return $query->whereHas('members', function (Builder $memberQuery) use ($user) {
            $memberQuery->where('users.id', $user->id);
        });
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function isOverdue(): bool
    {
        return $this->tgl_target !== null
            && $this->tgl_target->lt(today())
            && $this->status !== self::STATUS_SELESAI;
    }

    public function progressPercent(): int
    {
        $total = $this->features_count ?? $this->features()->count();
        if ($total === 0) {
            return 0;
        }

        $done = $this->selesai_features_count
            ?? $this->features()->where('status', Feature::STATUS_SELESAI)->count();

        return (int) round(($done / $total) * 100);
    }
}
