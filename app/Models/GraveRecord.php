<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GraveRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_si_mati',
        'no_ic',
        'blok',
        'baris',
        'lot',
        'tarikh_kebumi',
        'masa_kebumi',
    ];

    protected $casts = [
        'baris' => 'integer',
        'lot' => 'integer',
        'tarikh_kebumi' => 'date:Y-m-d',
    ];

    public function waris()
    {
        return $this->hasMany(GraveWaris::class);
    }
}
