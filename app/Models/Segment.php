<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\PmSchedule;
use App\Models\MaintenanceTask;
use App\Models\Regional;

class Segment extends Model
{
    protected $table = 'segments';

    protected $fillable = [
        'nama_segment',
        'kode_segment',
        'jalur',
        'regional_id'
    ];

    // relasi ke pm schedule
   public function schedules()
{
    return $this->hasMany(PmSchedule::class, 'segment_id');
}

    // relasi ke task maintenance
    public function tasks()
    {
        return $this->hasMany(MaintenanceTask::class);
    }

    public function regional()
{
    return $this->belongsTo(Regional::class);
}
}