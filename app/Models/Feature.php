<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class Feature extends Model
{
    use HasFactory;

    public const STATUS_BELUM_MULAI = 'belum_mulai';
    public const STATUS_DIKERJAKAN = 'dikerjakan';
    public const STATUS_SELESAI = 'selesai';
    public const STATUS_GAGAL = 'gagal';

    public const STATUSES = [
        self::STATUS_BELUM_MULAI => 'Belum Mulai',
        self::STATUS_DIKERJAKAN => 'Dikerjakan',
        self::STATUS_SELESAI => 'Selesai',
        self::STATUS_GAGAL => 'Gagal',
    ];

    public const PRIORITY_RENDAH = 'rendah';
    public const PRIORITY_SEDANG = 'sedang';
    public const PRIORITY_TINGGI = 'tinggi';

    public const PRIORITIES = [
        self::PRIORITY_RENDAH => 'Rendah',
        self::PRIORITY_SEDANG => 'Sedang',
        self::PRIORITY_TINGGI => 'Tinggi',
    ];

    protected $fillable = [
        'project_id',
        'nama',
        'deskripsi',
        'status',
        'prioritas',
        'pic_id',
        'tgl_target',
        'tgl_mulai',
        'tgl_selesai',
        'catatan_gagal',
        'urutan',
    ];

    protected $casts = [
        'tgl_target' => 'date',
        'tgl_mulai' => 'date',
        'tgl_selesai' => 'date',
        'urutan' => 'integer',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function pic(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pic_id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(FeatureLog::class)->latest('created_at');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(FeatureComment::class)->orderBy('created_at')->orderBy('id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(FeatureAttachment::class)->latest();
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function priorityLabel(): string
    {
        return self::PRIORITIES[$this->prioritas] ?? $this->prioritas;
    }

    public function transitionStatus(string $newStatus, User $actor, ?string $catatanGagal = null, ?int $urutan = null): void
    {
        if (! array_key_exists($newStatus, self::STATUSES)) {
            throw ValidationException::withMessages([
                'status' => 'Status fitur tidak valid.',
            ]);
        }

        $oldStatus = $this->status;

        if ($newStatus === self::STATUS_GAGAL && $oldStatus !== self::STATUS_GAGAL) {
            $reason = trim((string) $catatanGagal);
            if ($reason === '') {
                throw ValidationException::withMessages([
                    'catatan_gagal' => 'Alasan gagal wajib diisi saat memindahkan fitur ke Gagal.',
                ]);
            }
            $this->catatan_gagal = $reason;
        } elseif ($newStatus === self::STATUS_GAGAL && $catatanGagal !== null && trim($catatanGagal) !== '') {
            $this->catatan_gagal = trim($catatanGagal);
        }

        if ($newStatus !== $oldStatus) {
            if ($newStatus === self::STATUS_DIKERJAKAN) {
                $this->tgl_mulai ??= today();
                if ($oldStatus === self::STATUS_SELESAI) {
                    $this->tgl_selesai = null;
                }
            } elseif ($newStatus === self::STATUS_SELESAI) {
                $this->tgl_mulai ??= today();
                $this->tgl_selesai = today();
            } elseif ($oldStatus === self::STATUS_SELESAI) {
                $this->tgl_selesai = null;
            }
        }

        DB::transaction(function () use ($newStatus, $oldStatus, $actor, $urutan) {
            $this->status = $newStatus;
            if ($urutan !== null) {
                $this->urutan = $urutan;
            }
            $this->save();

            if ($newStatus !== $oldStatus) {
                FeatureLog::create([
                    'feature_id' => $this->id,
                    'status_lama' => $oldStatus,
                    'status_baru' => $newStatus,
                    'user_id' => $actor->id,
                    'created_at' => now(),
                ]);
            }
        });
    }
}
