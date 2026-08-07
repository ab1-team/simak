<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Personalia extends Model
{
    use HasFactory;

    protected $table = 'personalia';
    public $timestamps = false;

    protected $guarded = ['id'];

    public function usaha()
    {
        return $this->belongsTo(Usaha::class, 'lokasi', 'id');
    }
}
