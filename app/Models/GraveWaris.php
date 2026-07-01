<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GraveWaris extends Model
{
    use HasFactory;

    protected $table = 'grave_waris';

    protected $fillable = [
        'grave_record_id',
        'nama',
        'no_tel',
    ];

    public function graveRecord()
    {
        return $this->belongsTo(GraveRecord::class);
    }
}
