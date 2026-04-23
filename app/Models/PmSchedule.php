<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany; // tambahkan ini

class PmSchedule extends Model
{
    protected $fillable = [
    'segment_id',
    'planned_date',
    'priority',
    'status',
    'created_by',
    'approved_by',
    'approved_at',
    'signature_teknisi',
    'signature_ro',
    'teknisi_1',
    'teknisi_2',
    'notes',
];

    protected $casts = [
        'planned_date' => 'date',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function teknisi1(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teknisi_1');
    }

    public function teknisi2(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teknisi_2');
    }

   
public function segment()
{
    return $this->belongsTo(Segment::class);
}
public function inspeksiHeader()
{
    return $this->hasOne(\App\Models\InspeksiHeader::class,'schedule_id');
}

public static function rejectOverdueWithoutInspeksi(?string $today = null): int
{
    $today = $today ?? now()->toDateString();

    return static::query()
        ->where('status', 'approved')
        ->whereDate('planned_date', '<', $today)
        ->whereDoesntHave('inspeksiHeader')
        ->update([
            'status' => 'rejected',
        ]);
}

}
