<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Desa;
use App\Models\Kabupaten;
use App\Models\Kecamatan;
use App\Models\Usaha;
use App\Models\Wilayah;
use App\Utils\Tanggal;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Session;
use Yajra\DataTables\DataTables;

class AppController extends Controller
{
    public function index()
    {
        if (request()->ajax()) {
            $usaha = Usaha::with([
                'd.kec.kabupaten'
            ])->get();

            return DataTables::of($usaha)
                ->make();
        }

        $title = 'App Page';
        return view('admin.app.index')->with(compact('title'));
    }

    public function register()
    {
        $wilayah = Wilayah::whereRaw('Length(kode)=2')->orderBy('nama', 'ASC')->get();

        $title = 'Register Usaha Baru';
        return view('admin.app.register')->with(compact('title', 'wilayah'));
    }

    public function show(Usaha $usaha)
    {
        $kd_desa = $usaha->kd_desa;
        $kode_wilayah = explode('.', $kd_desa);
        $wilayah = Wilayah::whereRaw('Length(kode)=2')->orderBy('nama', 'ASC')->get();

        $title = $usaha->nama_usaha . ' ' . $usaha->d->kec->nama_kec;
        return view('admin.app.detail')->with(compact('title', 'usaha', 'kode_wilayah', 'wilayah'));
    }

    public function provinsi($kode)
    {
        $wilayah = Wilayah::where('kode', 'LIKE', $kode . '%')->whereRaw('LENGTH(kode)=2')->get();
        return response()->json([
            'data' => $wilayah
        ]);
    }

    public function kabupaten($kode)
    {
        $wilayah = Wilayah::where('kode', 'LIKE', $kode . '%')->whereRaw('LENGTH(kode)=5')->get();
        return response()->json([
            'data' => $wilayah
        ]);
    }

    public function kecamatan($kode)
    {
        $wilayah = Wilayah::where('kode', 'LIKE', $kode . '%')->whereRaw('LENGTH(kode)=8')->get();
        return response()->json([
            'data' => $wilayah
        ]);
    }

    public function desa($kode)
    {
        $wilayah = Wilayah::where('kode', 'LIKE', $kode . '%')->whereRaw('LENGTH(kode)=13')->get();
        return response()->json([
            'data' => $wilayah
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->only([
            'provinsi',
            'kabupaten',
            'kecamatan',
            'desa',
            'nama_usaha',
            'jenis_akun',
            'domain',
            'domain_alternatif',
            'tagihan_invoice',
            'biaya_maintenance',
            'tgl_register',
            'tgl_pakai',
            'masa_aktif'
        ]);

        $validate = Validator::make($data, [
            'provinsi' => 'required',
            'kabupaten' => 'required',
            'kecamatan' => 'required',
            'desa' => 'required',
            'nama_usaha' => 'required',
            'jenis_akun' => 'required',
            'domain' => 'required|unique:usaha,domain',
            'domain_alternatif' => 'required|unique:usaha,domain_alt',
            'tagihan_invoice' => 'required',
            'biaya_maintenance' => 'required',
            'tgl_register' => 'required',
            'tgl_pakai' => 'required',
            'masa_aktif' => 'required',
        ]);

        if ($validate->fails()) {
            return response()->json($validate->errors(), Response::HTTP_MOVED_PERMANENTLY);
        }

        $this->AddKabupaten($data['kabupaten']);
        $this->AddKecamatan($data['kecamatan']);
        $this->AddDesa($data['desa']);

        $usaha = Usaha::create([
            'kd_desa' => $data['desa'],
            'nama_usaha' => $data['nama_usaha'],
            'jenis_akun' => $data['jenis_akun'],
            'domain' => $data['domain'],
            'domain_alt' => $data['domain_alternatif'],
            'tagihan_invoice' => $data['tagihan_invoice'],
            'biaya' => str_replace(',', '', str_replace('.00', '', $data['biaya_maintenance'])),
            'tgl_register' => Tanggal::tglNasional($data['tgl_register']),
            'tgl_pakai' => Tanggal::tglNasional($data['tgl_pakai']),
            'masa_aktif' => Tanggal::tglNasional($data['masa_aktif']),
            'kepala_lembaga' => '-',
            'badan_pengawas' => '-',
            'kabag_administrasi' => '-',
            'kabag_keuangan' => '-',
            'bkk_bkm_bm' => '-',
            'npwp' => '-',
            'tgl_npwp' => date('Y-m-d'),
            'nomor_bh' => '-',
            'alamat' => '-',
            'email' => '-',
            'telpon' => '-',
            'logo' => '-',
            'background' => '-',
            'peraturan_desa' => '-',
        ]);

        return response()->json([
            'success' => true,
            'msg' => 'Usaha ' . $data['nama_usaha'] . ' Berhasil Didaftarkan',
            'id' => $usaha->id,
        ]);
    }

    public function update(Request $request, Usaha $usaha)
    {
        $data = $request->only([
            '_provinsi',
            '_kabupaten',
            '_kecamatan',
            '_desa',
            'provinsi',
            'kabupaten',
            'kecamatan',
            'desa',
            'nama_usaha',
            'domain',
            'domain_alternatif',
            'tagihan_invoice',
            'biaya_maintenance',
            'tgl_register',
            'tgl_pakai',
            'masa_aktif'
        ]);

        $validate = Validator::make($data, [
            '_provinsi' => 'required',
            '_kabupaten' => 'required',
            '_kecamatan' => 'required',
            '_desa' => 'required',
            'provinsi' => 'required',
            'kabupaten' => 'required',
            'kecamatan' => 'required',
            'desa' => 'required',
            'nama_usaha' => 'required',
            'domain' => 'required',
            'domain_alternatif' => 'required',
            'tagihan_invoice' => 'required',
            'biaya_maintenance' => 'required',
            'tgl_register' => 'required',
            'tgl_pakai' => 'required',
            'masa_aktif' => 'required',
        ]);

        if ($validate->fails()) {
            if ($validate->fails()) {
                return response()->json($validate->errors(), Response::HTTP_MOVED_PERMANENTLY);
            }
        }

        if ($data['_kabupaten'] != $data['kabupaten']) {
            $this->AddKabupaten($data['kabupaten']);
        }

        if ($data['_kecamatan'] != $data['kecamatan']) {
            $this->AddKecamatan($data['kecamatan']);
        }

        if ($data['_desa'] != $data['desa']) {
            $this->AddDesa($data['desa']);
        }

        Usaha::where('id', $usaha->id)->update([
            'kd_desa' => $data['desa'],
            'nama_usaha' => $data['nama_usaha'],
            'domain' => $data['domain'],
            'domain_alt' => $data['domain_alternatif'],
            'tagihan_invoice' => $data['tagihan_invoice'],
            'biaya' => str_replace(',', '', str_replace('.00', '', $data['biaya_maintenance'])),
            'tgl_register' => Tanggal::tglNasional($data['tgl_register']),
            'tgl_pakai' => Tanggal::tglNasional($data['tgl_pakai']),
            'masa_aktif' => Tanggal::tglNasional($data['masa_aktif'])
        ]);

        return response()->json([
            'success' => true,
            'msg' => 'Usaha ' . $data['nama_usaha'] . ' Berhasil Diperbarui',
            'id' => $usaha->id
        ]);
    }

    private function AddKabupaten($kode)
    {
        $kab = Kabupaten::where('kd_kab', $kode);
        if ($kab->count() <= 0) {
            $kabupaten = Wilayah::where('kode', $kode)->first();

            $nama_kab = $kabupaten->nama;
            $nama_kab = ucwords(strtolower(str_replace('KAB. ', '', $nama_kab)));
            $InsertKabupaten = Kabupaten::create([
                "kd_prov" => explode('.', $kode)[0],
                "kd_kab" => $kode,
                "nama_kab" => $nama_kab,
                "nama_lembaga" => $nama_kab,
                "alamat_kab" => "-",
                "telpon_kab" => "0",
                "email_kab" => "-",
                "web_kab" => "-",
                "web_kab_alternatif" => "-",
                "uname" => "-",
                "pass" => "-",
            ]);

            return $InsertKabupaten;
        }

        return $kab->first();
    }

    private function AddKecamatan($kode)
    {
        $kec = Kecamatan::where('kd_kec', $kode);
        if ($kec->count() <= 0) {
            $kecamatan = Wilayah::where('kode', $kode)->first();

            $InsertKecamatan = Kecamatan::create([
                "kd_kab" => explode('.', $kode)[0] . '.' . explode('.', $kode)[1],
                "kd_kec" => $kode,
                "nama_kec" => $kecamatan->nama,
                "alamat_kec" => "-",
                "telpon_kec" => "0",
                "email_kec" => "-",
                "web_kec" => "-",
                "web_alternatif" => "-",
                "logo" => "-",
                "uname" => "-",
                "pass" => "-",
            ]);

            return $InsertKecamatan;
        }

        return $kec->first();
    }

    private function AddDesa($kode)
    {
        $d = Desa::where('kode_desa', $kode)->orWhere('kd_desa', str_replace('.', '', $kode))->first();
        if (! $d) {
            $kd_prov = explode('.', $kode)[0];
            $kd_kab = explode('.', $kode)[1];
            $kd_kec = explode('.', $kode)[2];

            $kec = Wilayah::where('kode', $kd_prov . '.' . $kd_kab . '.' . $kd_kec)->first();
            $desa = Wilayah::where('kode', $kode)->first();

            $InsertDesa = Desa::insert([
                "kd_kec" => $kd_prov . '.' . $kd_kab . '.' . $kd_kec,
                "nama_kec" => $kec->nama,
                "kd_desa" => str_replace('.', '', $kode),
                "nama_desa" => $desa->nama,
                "alamat_desa" => '-',
                "telp_desa" => "-",
                "sebutan" => "1",
                "kode_desa" => $kode,
                "kades" => "-",
                "pangkat" => "-",
                "nip" => "-",
                "no_kades" => "-",
                "sekdes" => "-",
                "no_sekdes" => "-",
                "ked" => "-",
                "no_ked" => "-",
                "deskripsi_desa" => "-",
                "uname" => "-",
                "pass" => "-",
            ]);

            return $InsertDesa;
        }

        return $d ?? Desa::where('kode_desa', $kode)->first();
    }
}
