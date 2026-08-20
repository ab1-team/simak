<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use \Awobaz\Compoships\Compoships;
use Session;

class Rekening extends Model
{
    use HasFactory, Compoships;
    protected $table;
    public $timestamps = false;

    protected $guarded = ['id'];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        if (Session::get('jenis_akun') == 7) {
            $lokasi = Session::get('lokasi');
            $this->table = 'accounts' . ($lokasi ? '_' . $lokasi : '');
        } else {
            $this->table = 'rekening_' . Session::get('lokasi');
        }
    }

    protected static function booted()
    {
        static::addGlobalScope('aktif', function ($builder) {
            $builder->where(function ($query) {
                $query->whereNull('tgl_nonaktif')
                    ->orWhere('tgl_nonaktif', '>', date('Y-m-d'));
            });
        });
    }

    // public function __construct()
    // {
    //     $this->table = 'rekening_' . Session::get('lokasi');
    // }

    public function trx_debit()
    {
        return $this->hasMany(Transaksi::class, 'rekening_debit', 'kode_akun');
    }

    public function trx_kredit()
    {
        return $this->hasMany(Transaksi::class, 'rekening_kredit', 'kode_akun');
    }

    public function inventaris()
    {
        return $this->hasMany(Inventaris::class, 'kategori', 'lev4');
    }

    public function saldo()
    {
        return $this->hasOne(Saldo::class, 'kode_akun', 'kode_akun');
    }

    public function kom_saldo()
    {
        return $this->hasMany(Saldo::class, 'kode_akun', 'kode_akun');
    }

    public function kom_eb()
    {
        return $this->hasMany(Ebudgeting::class, 'kode_akun', 'kode_akun');
    }

    public function eb()
    {
        return $this->hasOne(Ebudgeting::class, 'kode_akun', 'kode_akun');
    }

    public function getRouteKeyName()
    {
        return 'kode_akun';
    }
}
