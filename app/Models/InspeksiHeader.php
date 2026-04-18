<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\InspeksiFmeaDetail;
use App\Models\InspeksiKondisiUmum;
use App\Models\PmSchedule;
use App\Models\InspeksiDetail;
use App\Models\User;

use App\Models\Segment;


class InspeksiHeader extends Model
{
    protected $table = 'inspeksi_headers';

    protected $fillable = [
        'segment_inspeksi',
        'jalur_fo',
        'nama_pelaksana',
        'driver',
        'cara_patroli',
        'cara_patroli_lainnya',
        'tanggal_inspeksi',
        'priority',
        'schedule_pm',
        'prepared_by',
        'approved_by',
        'prepared_signature',
        'approved_signature',
        'schedule_id',
        'status_workflow'
    ];

    protected $casts = [
        'tanggal_inspeksi' => 'date',
    ];

    public function fmeaDetails(): HasMany
    {
        return $this->hasMany(InspeksiFmeaDetail::class, 'inspeksi_id');
    }

    public function kondisiUmum(): HasOne
    {
        return $this->hasOne(InspeksiKondisiUmum::class, 'inspeksi_id');
    }

    public function pmSchedule(): BelongsTo
    {
        return $this->belongsTo(PmSchedule::class, 'schedule_id');
    }

 

       
    public function getRiskSummaryAttribute()
    {
        $maxRpn = $this->fmeaDetails()->max('rpn');
        if ($maxRpn >= 100) {
            return ['priority' => 'KRITIS', 'recommendation' => 'Perlu perbaikan segera'];
        } elseif ($maxRpn >= 50) {
            return ['priority' => 'SEDANG', 'recommendation' => 'Pantau kondisi'];
        } else {
            return ['priority' => 'RENDAH', 'recommendation' => 'Jadwalkan inspeksi rutin'];
        }
    }

    public function details()
{
    return $this->hasMany(InspeksiDetail::class,'inspeksi_id');
}

public function preparer()
{
    return $this->belongsTo(User::class,'prepared_by');
}

public function approver()
{
    return $this->belongsTo(User::class,'approved_by');
}

public function segment()
{
    return $this->belongsTo(Segment::class, 'segment_id');
}

public function images()
{
    return $this->hasMany(InspeksiImage::class);
}

public function creator()
{
    return $this->belongsTo(User::class, 'prepared_by');
}

}