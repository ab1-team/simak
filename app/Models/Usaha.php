<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Usaha extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    protected $table = 'usaha';
    public $timestamps = false;

    public function d()
    {
        return $this->belongsTo(Desa::class, 'kd_desa', 'kode_desa');
    }

    public function ttd()
    {
        return $this->belongsTo(TandaTanganLaporan::class, 'id', 'lokasi');
    }

    public function personalia()
    {
        return $this->hasMany(Personalia::class, 'lokasi', 'id');
    }
}
