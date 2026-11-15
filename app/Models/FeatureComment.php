<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeatureComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'feature_id',
        'user_id',
        'isi',
    ];

    public function feature(): BelongsTo
    {
        return $this->belongsTo(Feature::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
