<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InspeksiDetail extends Model
{
    protected $fillable = [
        'inspeksi_id',
        'objek',
        'status',
        'atribut',
        'catatan'
    ];

    protected $casts = [
        'atribut' => 'array'
    ];

    public function inspeksi()
    {
        return $this->belongsTo(InspeksiHeader::class, 'inspeksi_id');
    }
}
