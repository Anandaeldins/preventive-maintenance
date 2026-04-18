<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FmeaOutput extends Model
{
    protected $fillable = [
        'segment_id',
        'bulan',
        'tahun',
        'avg_rpn',
        'risk_index',
        'priority'
    ];
}