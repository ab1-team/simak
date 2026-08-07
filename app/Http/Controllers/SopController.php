<?php

namespace App\Http\Controllers;

use App\Models\AdminInvoice;
use App\Models\AkunLevel1;
use App\Models\AkunLevel3;
use App\Models\Personalia;
use App\Models\Rekening;
use App\Models\Saldo;
use App\Models\TandaTanganLaporan;
use App\Models\Usaha;
use App\Models\User;
use App\Utils\Tanggal;
use Cookie;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Session;
use Yajra\DataTables\DataTables;

class SopController extends Controller
{
    public function index()
    {
        $api = env('APP_API', 'https://api-whatsapp.sidbm.net');

        $usaha = Usaha::where('id', Session::get('lokasi'))->with('ttd', 'personalia')->first();
        $token = 'SIMAK-'.str_pad($usaha->id, 4, '0', STR_PAD_LEFT);

        $title = 'Personalisasi SOP';

        return view('sop.index')->with(compact('title', 'usaha', 'api', 'token'));
    }

    public function coa()
    {
        $title = 'Chart Of Account (CoA)';

        if (request()->ajax()) {
            $akun1 = AkunLevel1::with([
                'akun2',
                'akun2.akun3',
                'akun2.akun3.rek',
            ])->get();

            $coa = [];
            foreach ($akun1 as $ak1) {
                $akun_level_1 = [
                    'id' => $ak1->kode_akun,
                    'text' => $ak1->kode_akun.'. '.$ak1->nama_akun,
                    'children' => [],
                ];

                foreach ($ak1->akun2 as $ak2) {
                    $akun2 = [
                        'id' => $ak2->kode_akun,
                        'text' => $ak2->kode_akun.'. '.$ak2->nama_akun,
                        'children' => [],
                    ];

                    foreach ($ak2->akun3 as $ak3) {
                        $akun3 = [
                            'id' => $ak3->kode_akun,
                            'text' => $ak3->kode_akun.'. '.$ak3->nama_akun,
                            'children' => [],
                        ];

                        foreach ($ak3->rek as $rek) {
                            $akun4 = [
                                'id' => $rek->kode_akun,
                                'text' => $rek->kode_akun.'. '.$rek->nama_akun,
                            ];

                            array_push($akun3['children'], $akun4);
                        }
                        array_push($akun2['children'], $akun3);
                    }
                    array_push($akun_level_1['children'], $akun2);
                }
                array_push($coa, $akun_level_1);
            }

            return response()->json($coa);
        }

        return view('sop.coa')->with(compact('title'));
    }

    public function createCoa(Request $request)
    {
        $data = $request->only([
            'id_akun',
            'nama_akun',
        ]);

        $rek = Rekening::where('kode_akun', $data['id_akun'])->count();
        if ($rek <= 0) {
            $kode_akun = explode('.', $data['id_akun']);
            $lev1 = $kode_akun[0];
            $lev2 = $kode_akun[1];
            $lev3 = str_pad($kode_akun[2], 2, '0', STR_PAD_LEFT);
            $lev4 = str_pad($kode_akun[3], 2, '0', STR_PAD_LEFT);

            $data['id_akun'] = $lev1.'.'.$lev2.'.'.$lev3.'.'.$lev4;
            $nama_akun = preg_replace('/\d/', '', $data['nama_akun']);
            $nama_akun = preg_replace('/[^A-Za-z\s]/', '', $nama_akun);
            $nama_akun = trim($nama_akun);

            $insert = [
                'parent_id' => $lev1.$lev2.intval($lev3),
                'lev1' => $lev1,
                'lev2' => $lev2,
                'lev3' => $lev3,
                'lev4' => $lev4,
                'kode_akun' => $data['id_akun'],
                'nama_akun' => $nama_akun,
                'jenis_mutasi' => ($lev1 == '1' || $lev1 == '5') ? 'debet' : 'kredit',
            ];

            $rekening = Rekening::insert($insert);
            Saldo::where('kode_akun', $data['id_akun'])->delete();

            $insert_saldo = [];
            $rek = Rekening::with('kom_saldo')->first();
            foreach ($rek->kom_saldo as $saldo) {
                $insert_saldo[] = [
                    'id' => str_replace('.', '', $data['id_akun']).$saldo->tahun.str_pad($saldo->bulan, 2, '0', STR_PAD_LEFT),
                    'kode_akun' => $data['id_akun'],
                    'tahun' => $saldo->tahun,
                    'bulan' => $saldo->bulan,
                    'debit' => 0,
                    'kredit' => 0,
                ];
            }

            Saldo::insert($insert_saldo);

            return response()->json([
                'success' => true,
                'msg' => 'Akun '.$nama_akun.' berhasil ditambahkan dengan kode '.$data['id_akun'],
                'nama_akun' => $data['id_akun'].'. '.$nama_akun,
                'id' => $data['id_akun'],
            ]);
        }

        return response()->json([
            'success' => false,
            'msg' => 'Akun gagal ditambahkan',
        ]);
    }

    public function updateCoa(Request $request, $kode_akun)
    {
        $data = $request->only([
            'id_akun',
            'nama_akun',
        ]);

        $nama_akun = preg_replace('/\d/', '', $data['nama_akun']);
        $nama_akun = preg_replace('/[^A-Za-z\s]/', '', $nama_akun);
        $nama_akun = trim($nama_akun);

        $lev1 = explode('.', $data['id_akun'])[0];
        $lev2 = explode('.', $data['id_akun'])[1];
        $lev3 = explode('.', $data['id_akun'])[2];
        $lev4 = explode('.', $data['id_akun'])[3];

        if ($lev4 > 0) {
            $rekening = Rekening::where('kode_akun', $kode_akun)->first();
            if ($rekening->nama_akun != $nama_akun && $rekening->kode_akun == $data['id_akun']) {
                Rekening::where('kode_akun', $rekening->kode_akun)->update([
                    'nama_akun' => $nama_akun,
                ]);

                return response()->json([
                    'success' => true,
                    'msg' => 'Akun dengan kode '.$data['id_akun'].' berhasil diperbarui',
                    'nama_akun' => $data['id_akun'].'. '.$nama_akun,
                    'id' => $data['id_akun'],
                ]);
            }
        } else {
            $akun_level_3 = AkunLevel3::where('kode_akun', $kode_akun)->first();
            if ($akun_level_3->nama_akun != $nama_akun && $akun_level_3->kode_akun == $data['id_akun']) {
                AkunLevel3::where('kode_akun', $akun_level_3->kode_akun)->update([
                    'nama_akun' => $nama_akun,
                ]);

                return response()->json([
                    'success' => true,
                    'msg' => 'Akun dengan kode '.$data['id_akun'].' berhasil diperbarui',
                    'nama_akun' => $data['id_akun'].'. '.$nama_akun,
                    'id' => $data['id_akun'],
                ]);
            }
        }

        return response()->json([
            'success' => false,
            'msg' => 'Akun gagal diperbarui',
        ]);
    }

    public function deleteCoa(Request $request, Rekening $rekening)
    {
        $data = $request->only([
            'id_akun',
            'nama_akun',
        ]);

        if ($rekening->kode_akun == $data['id_akun']) {
            Rekening::where('kode_akun', $rekening->kode_akun)->delete();
            Saldo::where('kode_akun', $rekening->kode_akun)->delete();

            return response()->json([
                'success' => true,
                'msg' => 'Akun dengan kode '.$data['id_akun'].' berhasil dihapus',
                'id' => $data['id_akun'],
            ]);
        }

        return response()->json([
            'success' => false,
            'msg' => 'Akun gagal dihapus',
        ]);
    }

    public function lembaga(Request $request, Usaha $usaha)
    {
        $data = $request->only([
            'nama_bumdes',
            'nomor_badan_hukum',
            'telpon',
            'email',
            'alamat',
            'peraturan_desa',
            'npwp',
            'tanggal_npwp',
        ]);

        $validate = Validator::make($data, [
            'nama_bumdes' => 'required',
            'nomor_badan_hukum' => 'required',
            'telpon' => 'required',
            'email' => 'required',
            'alamat' => 'required',
            'peraturan_desa' => 'required',
            'npwp' => 'required',
            'tanggal_npwp' => 'required',
        ]);

        if ($validate->fails()) {
            return response()->json($validate->errors(), Response::HTTP_MOVED_PERMANENTLY);
        }

        $update = [
            'nama_usaha' => ucwords(strtolower($data['nama_bumdes'])),
            'nomor_bh' => $data['nomor_badan_hukum'],
            'telpon' => $data['telpon'],
            'email' => $data['email'],
            'alamat' => $data['alamat'],
            'npwp' => $data['npwp'],
            'tgl_npwp' => Tanggal::tglNasional($data['tanggal_npwp']),
            'peraturan_desa' => $request->peraturan_desa,
            // 'jenis_akun' => $request->jenis_akun ?? 5,

        ];

        $usaha = Usaha::where('id', $usaha->id)->update($update);
        Session::put('nama_usaha', ucwords(strtolower($data['nama_bumdes'])));

        return response()->json([
            'success' => true,
            'msg' => 'Identitas Lembaga Berhasil Diperbarui.',
            'nama_usaha' => ucwords(strtolower($data['nama_bumdes'])),
        ]);
    }

    public function pengelola(Request $request, Usaha $usaha)
    {
        $data = $request->only([
            'sebutan_pengawas',
            'kepala_lembaga',
            'kabag_administrasi',
            'kabag_keuangan',
            'bkk_bkm',
        ]);

        $validate = Validator::make($data, [
            'sebutan_pengawas' => 'required',
            'kepala_lembaga' => 'required',
            'kabag_administrasi' => 'required',
            'kabag_keuangan' => 'required',
            'bkk_bkm' => 'required',
        ]);

        if ($validate->fails()) {
            return response()->json($validate->errors(), Response::HTTP_MOVED_PERMANENTLY);
        }

        $usaha = Usaha::where('id', $usaha->id)->update([
            'badan_pengawas' => ucwords(strtolower($data['sebutan_pengawas'])),
            'kepala_lembaga' => ucwords(strtolower($data['kepala_lembaga'])),
            'kabag_administrasi' => ucwords(strtolower($data['kabag_administrasi'])),
            'kabag_keuangan' => ucwords(strtolower($data['kabag_keuangan'])),
            'bkk_bkm_bm' => ucwords(strtolower($data['bkk_bkm'])),
        ]);

        return response()->json([
            'success' => true,
            'msg' => 'Sebutan Pengelola Berhasil Diperbarui.',
        ]);
    }

    public function personalia(Request $request, Usaha $usaha)
    {
        $data = $request->only([
            'sebutan',
            'nama',
        ]);

        $validate = Validator::make($data, [
            'sebutan' => 'required|array',
            'nama' => 'required|array',
        ]);

        if ($validate->fails()) {
            return response()->json($validate->errors(), Response::HTTP_MOVED_PERMANENTLY);
        }

        Personalia::where('lokasi', $usaha->id)->delete();
        foreach ($data['sebutan'] as $key => $sebutan) {
            if (trim($sebutan) == '' || trim($data['nama'][$key]) == '') {
                continue;
            }

            Personalia::create([
                'lokasi' => $usaha->id,
                'sebutan' => ucwords(strtolower($sebutan)),
                'nama' => $data['nama'][$key],
            ]);
        }

        return response()->json([
            'success' => true,
            'msg' => 'Personalia Berhasil Diperbarui.',
        ]);
    }

    public function kopLaporan(Request $request, Usaha $usaha)
    {
        $data = $request->only([
            'kop_laporan',
        ]);

        $validate = Validator::make($data, [
            'kop_laporan' => 'required',
        ]);

        if ($validate->fails()) {
            return response()->json($validate->errors(), 400);
        }

        $data['kop_laporan'] = preg_replace('/<table[^>]*>/', '<table width="100%" border="0" cellpading="0" cellspacing="0" style="font-size: 12px;">', $data['kop_laporan']);
        $data['kop_laporan'] = preg_replace('/height:\s*[\d.]+px;?\s*/', '', $data['kop_laporan']);
        $data['kop_laporan'] = preg_replace('/\s*height="[^"]*"/', '', $data['kop_laporan']);
        $data['kop_laporan'] = preg_replace('/<p(\s[^>]*)?>/', '<div$1>', $data['kop_laporan']);
        $data['kop_laporan'] = preg_replace('/<\/p>/', '</div>', $data['kop_laporan']);

        $usaha = Usaha::where('id', $usaha->id)->update([
            'kop_laporan' => $data['kop_laporan'],
        ]);

        return response()->json([
            'success' => true,
            'msg' => 'Kop Dokumen Berhasil Diperbarui.',
        ]);
    }

    public function logo(Request $request, Usaha $usaha)
    {
        $data = $request->only([
            'logo',
        ]);

        $validate = Validator::make($data, [
            'logo' => 'required|image|mimes:jpg,png,jpeg|max:4096',
        ]);

        if ($request->file('logo')->isValid()) {
            $extension = $request->file('logo')->getClientOriginalExtension();

            $filename = time().'_'.$usaha->id.'_'.date('Ymd').'.'.$extension;
            $path = $request->file('logo')->storeAs('logo', $filename, 'public');

            if (Storage::exists('logo/'.$usaha->logo)) {
                if ($usaha->logo != 'simak.png') {
                    Storage::delete('logo/'.$usaha->logo);
                }
            }

            $u = Usaha::where('id', $usaha->id)->update([
                'logo' => str_replace('logo/', '', $path),
            ]);

            Session::put('logo', str_replace('logo/', '', $path));

            return response()->json([
                'success' => true,
                'msg' => 'Logo berhasil diperbarui.',
            ]);
        }

        return response()->json([
            'success' => false,
            'msg' => 'Logo gagal diperbarui',
        ]);
    }

    public function whatsapp($token)
    {
        User::where('lokasi', Session::get('lokasi'))->update([
            'ip' => $token,
        ]);

        return response()->json([
            'success' => true,
            'msg' => 'Sukses',
        ]);
    }

    public function ttdPelaporan()
    {
        $title = 'Pengaturan Tanda Tangan Pelaporan';
        $usaha = Usaha::where('id', Session::get('lokasi'))->with('ttd')->first();
        $ttd = TandaTanganLaporan::where([['lokasi', Session::get('lokasi')]])->first();

        $tanggal = false;
        if ($ttd) {
            $str = strpos($ttd->tanda_tangan_pelaporan, '{tanggal}');

            if ($str !== false) {
                $tanggal = true;
            }
        }

        return view('sop.partials.ttd_pelaporan')->with(compact('title', 'usaha', 'tanggal'));
    }

    public function simpanTtdPelaporan(Request $request)
    {
        $data = $request->only([
            'field',
            'tanda_tangan',
        ]);

        if ($data['field'] == 'tanda_tangan_pelaporan') {
            $data['tanda_tangan'] = preg_replace('/<table[^>]*>/', '<table class="p0" border="0" width="100%" cellspacing="0" cellpadding="0" style="font-size: 11px;">', $data['tanda_tangan'], 1);
        } else {
            $data['tanda_tangan'] = preg_replace('/<table[^>]*>/', '<table class="p0" border="0" width="100%" cellspacing="0" cellpadding="0" style="font-size: 12px;">', $data['tanda_tangan'], 1);
        }
        $data['tanda_tangan'] = preg_replace('/height:\s*[^;]+;?/', '', $data['tanda_tangan']);

        $data['tanda_tangan'] = str_replace('colgroup', 'tr', $data['tanda_tangan']);
        $data['tanda_tangan'] = preg_replace('/<col([^>]*)>/', '<td$1>&nbsp;</td>', $data['tanda_tangan']);

        $ttd = TandaTanganLaporan::where('lokasi', Session::get('lokasi'))->count();
        if ($ttd <= 0) {
            $insert = [
                'lokasi' => Session::get('lokasi'),
            ];

            if ($data['field'] == 'tanda_tangan_pelaporan') {
                $insert['tanda_tangan_spk'] = '';
                $insert['tanda_tangan_pelaporan'] = json_encode($data['tanda_tangan']);
            } else {
                $insert['tanda_tangan_pelaporan'] = '';
                $insert['tanda_tangan_spk'] = json_encode($data['tanda_tangan']);
            }

            $tanda_tangan = TandaTanganLaporan::create($insert);
        } else {
            // dd($data['tanda_tangan']);
            $tanda_tangan = TandaTanganLaporan::where('lokasi', Session::get('lokasi'))->update([
                $data['field'] => json_encode($data['tanda_tangan']),
            ]);
        }

        return response()->json([
            'success' => true,
            'msg' => ucwords(str_replace('_', ' ', $data['field'])).' Berhasil diperbarui',
        ]);
    }

    public function invoice()
    {
        if (request()->ajax()) {
            $usaha = Usaha::where('id', Session::get('lokasi'))->first();
            $tgl_pembuatan_invoice = date('Y-m-d', strtotime('-14 days', strtotime($usaha->masa_aktif)));

            $invoice = AdminInvoice::where([
                ['lokasi', Session::get('lokasi')],
                ['status', 'PAID'],
            ])->orwhere(function ($query) {
                $query->where([
                    ['lokasi', Session::get('lokasi')],
                    ['status', 'UNPAID'],
                    ['tgl_invoice', '<=', date('Y-m-d')],
                ]);
            })->with('jp')->withSum('trx', 'jumlah')->get();

            return DataTables::of($invoice)
                ->editColumn('tgl_invoice', function ($row) {
                    return Tanggal::tglIndo($row->tgl_invoice);
                })
                ->editColumn('jumlah', function ($row) {
                    return number_format($row->jumlah);
                })
                ->editColumn('status', function ($row) {
                    if ($row->status == 'PAID') {
                        return '<span class="badge badge-success">'.$row->status.'</span>';
                    }

                    return '<span class="badge badge-danger">'.$row->status.'</span>';
                })
                ->addColumn('saldo', function ($row) {
                    if ($row->trx_sum_jumlah) {
                        return number_format($row->jumlah - $row->trx_sum_jumlah);
                    }

                    return number_format($row->jumlah);
                })
                ->rawColumns(['status'])
                ->make(true);
        }

        $title = 'Daftar Invoice';

        return view('sop.invoice')->with(compact('title'));
    }

    public function detailInvoice(AdminInvoice $inv)
    {

        $title = 'Invoice #'.$inv->nomor.' - '.$inv->jp->nama_jp;

        return view('sop.detail_invoice')->with(compact('title', 'inv'));
    }

    public function localView($key, $val = '')
    {
        if (Cookie::has('config')) {
            $config = json_decode(request()->cookie('config'), true);
            cookie()->forget('config');
        } else {
            $config = [
                'sidebarColor' => 'success',
                'sidebarType' => 'bg-gradient-dark',
                'navbarFixed' => 'position-sticky blur shadow-blur mt-4 left-auto top-1 z-index-sticky',
                'sidebarMini' => 'g-sidenav-pinned',
                'darkMode' => '',
            ];
        }

        $config[$key] = $val;

        $cookie = cookie('config', json_encode($config), 60 * 24 * 365);
        Session::put('config', json_encode($config));

        return response()->json([
            'success' => true,
            'msg' => 'Pengaturan Halaman berhasil disimpan',
        ])->cookie($cookie);
    }
}
