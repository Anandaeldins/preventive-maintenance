<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InspeksiFmeaDetail extends Model
{
    protected $table = 'inspeksi_fmea_details';

    protected $fillable = [
        'inspeksi_id',
        'item',
        'severity',
        'occurrence',
        'detection',
        'rpn',
        'risk_index'
    ];

    public function inspeksi()
    {
        return $this->belongsTo(InspeksiHeader::class, 'inspeksi_id');
    }
    public function header()
{
    return $this->belongsTo(\App\Models\InspeksiHeader::class, 'inspeksi_id');
}
}