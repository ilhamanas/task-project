<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeatureAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'feature_id',
        'user_id',
        'nama_file',
        'path',
        'mime_type',
        'ukuran',
    ];

    protected $casts = [
        'ukuran' => 'integer',
    ];

    public function feature(): BelongsTo
    {
        return $this->belongsTo(Feature::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function formattedSize(): string
    {
        $bytes = (int) $this->ukuran;

        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        }
        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 1) . ' KB';
        }

        return $bytes . ' B';
    }
}
