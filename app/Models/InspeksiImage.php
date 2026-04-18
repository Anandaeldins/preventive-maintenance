<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InspeksiImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'inspeksi_header_id',
        'image_path'
    ];

    // relasi ke header inspeksi
    public function inspeksiHeader()
    {
        return $this->belongsTo(InspeksiHeader::class);
    }
}