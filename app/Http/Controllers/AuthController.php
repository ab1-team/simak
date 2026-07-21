<?php

namespace App\Http\Controllers;

use App\Models\AdminInvoice;
use App\Models\Desa;
use App\Models\Kabupaten;
use App\Models\Kecamatan;
use App\Models\Menu;
use App\Models\MenuTombol;
use App\Models\Usaha;
use App\Models\User;
use App\Models\Wilayah;
use App\Utils\Keuangan;
use App\Utils\Tanggal;
use Auth;
use DB;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Session;

class AuthController extends Controller
{
    public function index()
    {
        $keuangan = new Keuangan;
        if ($keuangan->startWith(request()->getHost(), 'master.sidbm')) {
            return redirect('/master');
        }

        $usaha = Usaha::where('domain', explode('//', request()->url(''))[1])->orwhere('domain_alt', explode('//', request()->url(''))[1])->with([
            'd',
            'd.sebutan_desa',
            'd.kec',
        ])->first();

        $logo = '/assets/img/icon/favicon.png';
        if ($usaha->logo) {
            $logo = '/storage/logo/'.$usaha->logo;
        }

        $appTheme = (in_array($usaha->theme ?? 'default', ['default', 'merah-putih'], true))
            ? $usaha->theme
            : 'default';

        return view('auth.login')->with(compact('usaha', 'logo', 'appTheme'));
    }

    public function register()
    {
        $usaha = Usaha::where('domain', request()->getHost())->orwhere('domain_alt', request()->getHost())->with([
            'd',
            'd.sebutan_desa',
            'd.kec',
        ])->first();

        if ($usaha->id != '1') {
            abort(404);
        }

        $logo = '/assets/img/icon/favicon.png';
        if ($usaha->logo) {
            $logo = '/storage/logo/'.$usaha->logo;
        }

        $appTheme = (in_array($usaha->theme ?? 'default', ['default', 'merah-putih'], true))
            ? $usaha->theme
            : 'default';

        return view('auth.register')->with(compact('usaha', 'logo', 'appTheme'));
    }

    public function provinsi()
    {
        $wilayah = Wilayah::whereRaw('LENGTH(kode)=2')->orderBy('nama', 'ASC')->get();

        return response()->json([
            'success' => true,
            'data' => $wilayah,
        ]);
    }

    public function kabupaten($kode)
    {
        $wilayah = Wilayah::whereRaw('LENGTH(kode)=5')->where('kode', 'LIKE', $kode.'%')->orderBy('nama', 'ASC')->get();

        return response()->json([
            'success' => true,
            'data' => $wilayah,
        ]);
    }

    public function kecamatan($kode)
    {
        $wilayah = Wilayah::whereRaw('LENGTH(kode)=8')->where('kode', 'LIKE', $kode.'%')->orderBy('nama', 'ASC')->get();

        return response()->json([
            'success' => true,
            'data' => $wilayah,
        ]);
    }

    public function desa($kode)
    {
        $wilayah = Wilayah::whereRaw('LENGTH(kode)=13')->where('kode', 'LIKE', $kode.'%')->orderBy('nama', 'ASC')->get();

        return response()->json([
            'success' => true,
            'data' => $wilayah,
        ]);
    }

    public function login(Request $request)
    {
        $url = $request->getHost();
        $username = htmlspecialchars($request->username);
        $password = $request->password;

        $validate = $this->validate($request, [
            'username' => 'required',
            'password' => 'required',
        ]);

        $usaha = Usaha::where('domain', $url)->orwhere('domain_alt', $url)->with([
            'd.kec.kabupaten',
        ])->first();

        $kec = $usaha->d->kec;
        $lokasi = $usaha->id;

        $icon = '/assets/img/icon/favicon.png';
        if ($usaha->logo) {
            $icon = '/storage/logo/'.$usaha->logo;
        }

        $user = User::where([
            ['uname', $username],
            ['lokasi', $lokasi],
        ])->first();

        if ($user) {
            $AksesMenu = Menu::whereNotIn('id', explode('#', $user->akses_menu))->where('parent_id', '0')->with('child')->orderBy('sort', 'ASC')->get();
            $AksesTombol = MenuTombol::whereNotIn('id', explode('#', $user->akses_tombol))->pluck('akses')->toArray();

            if ($password === $user->pass) {
                $inv = $this->generateInvoice($usaha);
                if (Auth::loginUsingId($user->id)) {
                    $request->session()->regenerate();

                    session([
                        'nama_usaha' => $usaha->nama_usaha,
                        'nama' => $user->namadepan.' '.$user->namabelakang,
                        'foto' => $user->foto,
                        'logo' => $usaha->logo,
                        'lokasi' => $usaha->id,
                        'usaha' => $user->usaha,
                        'lokasi_user' => $user->lokasi,
                        'icon' => $icon,
                        'menu' => $AksesMenu,
                        'tombol' => $AksesTombol,
                        'jenis_akun' => $usaha->jenis_akun,
                    ]);

                    return redirect('/dashboard')->with([
                        'pesan' => 'Selamat Datang '.$user->namadepan.' '.$user->namabelakang,
                        'is_invoice' => $inv,
                    ]);
                }
            }
        }

        return redirect()->back();
    }

    public function store(Request $request)
    {
        $data = $request->only([
            'provinsi',
            'kabupaten',
            'kecamatan',
            'desa',
            'nama_usaha',
            'tgl_register',
            'alamat',
            'email',
            'telpon',
        ]);

        $validate = Validator::make($data, [
            'provinsi' => 'required',
            'kabupaten' => 'required',
            'kecamatan' => 'required',
            'desa' => 'required',
            'nama_usaha' => 'required',
            'tgl_register' => 'required',
            'alamat' => 'required',
            'email' => 'required',
            'telpon' => 'required',
        ]);

        if ($validate->fails()) {
            return response()->json($validate->errors(), Response::HTTP_MOVED_PERMANENTLY);
        }

        $kab = Kabupaten::where([
            ['kd_kab', $data['kabupaten']],
            ['kd_prov', $data['provinsi']],
        ])->first();
        if (! $kab) {
            $kabupaten = Wilayah::where('kode', $data['kabupaten'])->first();

            $nama_kab = $kabupaten->nama;
            $nama_kab = ucwords(strtolower(str_replace('KAB. ', '', $nama_kab)));
            Kabupaten::create([
                'kd_prov' => $data['provinsi'],
                'kd_kab' => $data['kabupaten'],
                'nama_kab' => $nama_kab,
                'nama_lembaga' => $nama_kab,
                'alamat_kab' => '-',
                'telpon_kab' => '0',
                'email_kab' => '-',
                'web_kab' => '-',
                'web_kab_alternatif' => '-',
                'uname' => '-',
                'pass' => '-',
            ]);
        }

        $kec = Kecamatan::where([
            ['kd_kec', $data['kecamatan']],
            ['kd_kab', $data['kabupaten']],
        ])->first();
        if (! $kec) {
            $kecamatan = Wilayah::where('kode', $data['kecamatan'])->first();

            $kec = Kecamatan::create([
                'kd_kab' => $data['kabupaten'],
                'kd_kec' => $data['kecamatan'],
                'nama_kec' => $kecamatan->nama,
                'alamat_kec' => '-',
                'telpon_kec' => '0',
                'email_kec' => '-',
                'web_kec' => '-',
                'web_alternatif' => '-',
                'logo' => '-',
                'uname' => '-',
                'pass' => '-',
            ]);
        }

        $desa = Desa::where([
            ['kd_desa', $data['desa']],
            ['kd_kec', $data['kecamatan']],
        ])->first();
        if (! $desa) {
            $desa = Wilayah::where('kode', $data['desa'])->first();

            Desa::insert([
                'kd_kec' => $data['kecamatan'],
                'nama_kec' => $kec->nama_kec,
                'kd_desa' => str_replace('.', '', $data['desa']),
                'nama_desa' => $desa->nama,
                'alamat_desa' => $data['alamat'],
                'telp_desa' => '-',
                'sebutan' => '1',
                'kode_desa' => $data['desa'],
                'kades' => '-',
                'pangkat' => '-',
                'nip' => '-',
                'no_kades' => '-',
                'sekdes' => '-',
                'no_sekdes' => '-',
                'ked' => '-',
                'no_ked' => '-',
                'deskripsi_desa' => '-',
                'uname' => '-',
                'pass' => '-',
            ]);
        }

        $domain = str_replace('BUMDES', '', strtoupper($data['nama_usaha']));
        $domain = str_replace('BUMDESMA', '', strtoupper($domain));
        $domain = str_replace(' ', '-', $domain);

        $usaha = Usaha::create([
            'kd_desa' => $data['desa'],
            'nama_usaha' => $data['nama_usaha'],
            'kepala_lembaga' => '-',
            'badan_pengawas' => '-',
            'kabag_administrasi' => '-',
            'kabag_keuangan' => '-',
            'bkk_bkm_bm' => '-',
            'npwp' => '-',
            'tgl_npwp' => date('Y-m-d'),
            'nomor_bh' => '-',
            'alamat' => $data['alamat'],
            'email' => $data['email'],
            'telpon' => $data['telpon'],
            'domain' => $domain.'.akubumdes.id',
            'domain_alt' => $domain.'.siupk.net',
            'logo' => '-',
            'background' => '-',
            'tgl_register' => Tanggal::tglNasional($data['tgl_register']),
            'tgl_pakai' => Tanggal::tglNasional($data['tgl_register']),
            'biaya' => '0',
            'peraturan_desa' => '-',
        ]);

        Session::put('lokasi', $usaha->id);

        return redirect('/register/user');
    }

    public function user()
    {
        User::create([
            'namadepan' => 'Direktur',
            'namabelakang' => '',
            'ins' => 'DR',
            'jk' => '',
            'tempat_lahir' => '',
            'tgl_lahir' => date('Y-m-d'),
            'alamat' => '',
            'hp' => '',
            'nik' => '',
            'pendidikan' => '1',
            'jabatan' => '1',
            'level' => '1',
            'usaha' => Session::get('lokasi'),
            'lokasi' => Session::get('lokasi'),
            'foto' => '',
            'status' => '1',
            'uname' => 'Direktur',
            'pass' => 'Direktur',
        ]);

        User::create([
            'namadepan' => 'Sekretaris',
            'namabelakang' => '',
            'ins' => 'SK',
            'jk' => '',
            'tempat_lahir' => '',
            'tgl_lahir' => date('Y-m-d'),
            'alamat' => '',
            'hp' => '',
            'nik' => '',
            'pendidikan' => '1',
            'jabatan' => '2',
            'level' => '1',
            'usaha' => Session::get('lokasi'),
            'lokasi' => Session::get('lokasi'),
            'foto' => '',
            'status' => '1',
            'uname' => 'Sekretaris',
            'pass' => 'Sekretaris',
        ]);

        User::create([
            'namadepan' => 'Bendahara',
            'namabelakang' => '',
            'ins' => 'SK',
            'jk' => '',
            'tempat_lahir' => '',
            'tgl_lahir' => date('Y-m-d'),
            'alamat' => '',
            'hp' => '',
            'nik' => '',
            'pendidikan' => '1',
            'jabatan' => '3',
            'level' => '1',
            'usaha' => Session::get('lokasi'),
            'lokasi' => Session::get('lokasi'),
            'foto' => '',
            'status' => '1',
            'uname' => 'Bendahara',
            'pass' => 'Bendahara',
        ]);

        $lokasi = Session::get('lokasi');
        DB::statement("CREATE TABLE akun_$lokasi LIKE akun_1");
        DB::statement("CREATE TABLE ebudgeting_$lokasi LIKE transaksi_1");
        DB::statement("CREATE TABLE inventaris_$lokasi LIKE inventaris_1");
        DB::statement("CREATE TABLE rekening_$lokasi LIKE rekening_1");
        DB::statement("CREATE TABLE transaksi_$lokasi LIKE transaksi_1");
        DB::statement("CREATE TABLE saldo_$lokasi LIKE saldo_1");

        DB::statement("INSERT rekening_$lokasi SELECT * FROM rekening_1");
        DB::statement("INSERT akun_$lokasi SELECT * FROM akun_1");

        $create = "
            CREATE TRIGGER `create_saldo_debit_$lokasi` AFTER INSERT ON `transaksi_$lokasi`
                FOR EACH ROW BEGIN
                    INSERT INTO saldo_$lokasi (`id`, `kode_akun`, `tahun`, `bulan`, `debit`, `kredit`)
                        VALUES (CONCAT(REPLACE(NEW.rekening_debit, '.',''), YEAR(NEW.tgl_transaksi), LPAD(MONTH(NEW.tgl_transaksi), 2, '0')), NEW.rekening_debit, YEAR(NEW.tgl_transaksi), LPAD(MONTH(NEW.tgl_transaksi), 2, '0'), (SELECT SUM(jumlah) FROM transaksi_$lokasi WHERE rekening_debit=NEW.rekening_debit AND tgl_transaksi BETWEEN CONCAT(YEAR(NEW.tgl_transaksi),'-01-01') AND LAST_DAY(NEW.tgl_transaksi)), (SELECT SUM(jumlah) FROM transaksi_$lokasi WHERE rekening_kredit=NEW.rekening_debit AND tgl_transaksi BETWEEN CONCAT(YEAR(NEW.tgl_transaksi),'-01-01') AND LAST_DAY(NEW.tgl_transaksi))) ON DUPLICATE KEY UPDATE 
                        debit= (SELECT SUM(jumlah) FROM transaksi_$lokasi WHERE rekening_debit=NEW.rekening_debit AND tgl_transaksi BETWEEN CONCAT(YEAR(NEW.tgl_transaksi),'-01-01') AND LAST_DAY(NEW.tgl_transaksi)),
                        kredit=(SELECT SUM(jumlah) FROM transaksi_$lokasi WHERE rekening_kredit=NEW.rekening_debit AND tgl_transaksi BETWEEN CONCAT(YEAR(NEW.tgl_transaksi),'-01-01') AND LAST_DAY(NEW.tgl_transaksi));

                    INSERT INTO saldo_$lokasi (`id`, `kode_akun`, `tahun`, `bulan`, `debit`, `kredit`)
                        VALUES (CONCAT(REPLACE(NEW.rekening_kredit, '.',''), YEAR(NEW.tgl_transaksi), LPAD(MONTH(NEW.tgl_transaksi), 2, '0')), NEW.rekening_kredit, YEAR(NEW.tgl_transaksi), LPAD(MONTH(NEW.tgl_transaksi), 2, '0'), (SELECT SUM(jumlah) FROM transaksi_$lokasi WHERE rekening_debit=NEW.rekening_kredit AND tgl_transaksi BETWEEN CONCAT(YEAR(NEW.tgl_transaksi),'-01-01') AND LAST_DAY(NEW.tgl_transaksi)), (SELECT SUM(jumlah) FROM transaksi_$lokasi WHERE rekening_kredit=NEW.rekening_kredit AND tgl_transaksi BETWEEN CONCAT(YEAR(NEW.tgl_transaksi),'-01-01') AND LAST_DAY(NEW.tgl_transaksi))) ON DUPLICATE KEY UPDATE 
                        debit= (SELECT SUM(jumlah) FROM transaksi_$lokasi WHERE rekening_debit=NEW.rekening_kredit AND tgl_transaksi BETWEEN CONCAT(YEAR(NEW.tgl_transaksi),'-01-01') AND LAST_DAY(NEW.tgl_transaksi)),
                        kredit=(SELECT SUM(jumlah) FROM transaksi_$lokasi WHERE rekening_kredit=NEW.rekening_kredit AND tgl_transaksi BETWEEN CONCAT(YEAR(NEW.tgl_transaksi),'-01-01') AND LAST_DAY(NEW.tgl_transaksi));

                END;
            ";

        $update = "
            CREATE TRIGGER `update_saldo_debit_$lokasi` AFTER UPDATE ON `transaksi_$lokasi`
                FOR EACH ROW BEGIN

                    INSERT INTO saldo_$lokasi (`id`, `kode_akun`, `tahun`, `bulan`, `debit`, `kredit`)
                        VALUES (CONCAT(REPLACE(NEW.rekening_debit, '.',''), YEAR(NEW.tgl_transaksi), LPAD(MONTH(NEW.tgl_transaksi), 2, '0')), NEW.rekening_debit, YEAR(NEW.tgl_transaksi), LPAD(MONTH(NEW.tgl_transaksi), 2, '0'), (SELECT SUM(jumlah) FROM transaksi_$lokasi WHERE rekening_debit=NEW.rekening_debit AND tgl_transaksi BETWEEN CONCAT(YEAR(NEW.tgl_transaksi),'-01-01') AND LAST_DAY(NEW.tgl_transaksi)), (SELECT SUM(jumlah) FROM transaksi_$lokasi WHERE rekening_kredit=NEW.rekening_debit AND tgl_transaksi BETWEEN CONCAT(YEAR(NEW.tgl_transaksi),'-01-01') AND LAST_DAY(NEW.tgl_transaksi))) ON DUPLICATE KEY UPDATE 
                        debit= (SELECT SUM(jumlah) FROM transaksi_$lokasi WHERE rekening_debit=NEW.rekening_debit AND tgl_transaksi BETWEEN CONCAT(YEAR(NEW.tgl_transaksi),'-01-01') AND LAST_DAY(NEW.tgl_transaksi)),
                        kredit=(SELECT SUM(jumlah) FROM transaksi_$lokasi WHERE rekening_kredit=NEW.rekening_debit AND tgl_transaksi BETWEEN CONCAT(YEAR(NEW.tgl_transaksi),'-01-01') AND LAST_DAY(NEW.tgl_transaksi));

                    INSERT INTO saldo_$lokasi (`id`, `kode_akun`, `tahun`, `bulan`, `debit`, `kredit`)
                        VALUES (CONCAT(REPLACE(NEW.rekening_kredit, '.',''), YEAR(NEW.tgl_transaksi), LPAD(MONTH(NEW.tgl_transaksi), 2, '0')), NEW.rekening_kredit, YEAR(NEW.tgl_transaksi), LPAD(MONTH(NEW.tgl_transaksi), 2, '0'), (SELECT SUM(jumlah) FROM transaksi_$lokasi WHERE rekening_debit=NEW.rekening_kredit AND tgl_transaksi BETWEEN CONCAT(YEAR(NEW.tgl_transaksi),'-01-01') AND LAST_DAY(NEW.tgl_transaksi)), (SELECT SUM(jumlah) FROM transaksi_$lokasi WHERE rekening_kredit=NEW.rekening_kredit AND tgl_transaksi BETWEEN CONCAT(YEAR(NEW.tgl_transaksi),'-01-01') AND LAST_DAY(NEW.tgl_transaksi))) ON DUPLICATE KEY UPDATE 
                        debit= (SELECT SUM(jumlah) FROM transaksi_$lokasi WHERE rekening_debit=NEW.rekening_kredit AND tgl_transaksi BETWEEN CONCAT(YEAR(NEW.tgl_transaksi),'-01-01') AND LAST_DAY(NEW.tgl_transaksi)),
                        kredit=(SELECT SUM(jumlah) FROM transaksi_$lokasi WHERE rekening_kredit=NEW.rekening_kredit AND tgl_transaksi BETWEEN CONCAT(YEAR(NEW.tgl_transaksi),'-01-01') AND LAST_DAY(NEW.tgl_transaksi));

                END;
            ";

        $delete = "
            CREATE TRIGGER `delete_saldo_debit_$lokasi` AFTER DELETE ON `transaksi_$lokasi`
                FOR EACH ROW BEGIN

                    INSERT INTO saldo_$lokasi (`id`, `kode_akun`, `tahun`, `bulan`, `debit`, `kredit`)
                        VALUES (CONCAT(REPLACE(OLD.rekening_debit, '.',''), YEAR(OLD.tgl_transaksi), LPAD(MONTH(OLD.tgl_transaksi), 2, '0')), OLD.rekening_debit, YEAR(OLD.tgl_transaksi), MONTH(OLD.tgl_transaksi), (SELECT SUM(jumlah) FROM transaksi_$lokasi WHERE rekening_debit=OLD.rekening_debit AND tgl_transaksi BETWEEN CONCAT(YEAR(OLD.tgl_transaksi),'-01-01') AND LAST_DAY(OLD.tgl_transaksi)), (SELECT SUM(jumlah) FROM transaksi_$lokasi WHERE rekening_debit=OLD.rekening_debit AND tgl_transaksi BETWEEN CONCAT(YEAR(OLD.tgl_transaksi),'-01-01') AND LAST_DAY(OLD.tgl_transaksi))) ON DUPLICATE KEY UPDATE 
                        debit= (SELECT SUM(jumlah) FROM transaksi_$lokasi WHERE rekening_debit=OLD.rekening_debit AND tgl_transaksi BETWEEN CONCAT(YEAR(OLD.tgl_transaksi),'-01-01') AND LAST_DAY(OLD.tgl_transaksi)),
                        kredit=(SELECT SUM(jumlah) FROM transaksi_$lokasi WHERE rekening_kredit=OLD.rekening_debit AND tgl_transaksi BETWEEN CONCAT(YEAR(OLD.tgl_transaksi),'-01-01') AND LAST_DAY(OLD.tgl_transaksi));

                    INSERT INTO saldo_$lokasi (`id`, `kode_akun`, `tahun`, `bulan`, `debit`, `kredit`)
                        VALUES (CONCAT(REPLACE(OLD.rekening_kredit, '.',''), YEAR(OLD.tgl_transaksi), LPAD(MONTH(OLD.tgl_transaksi), 2, '0')), OLD.rekening_kredit, YEAR(OLD.tgl_transaksi), MONTH(OLD.tgl_transaksi), (SELECT SUM(jumlah) FROM transaksi_$lokasi WHERE rekening_debit=OLD.rekening_kredit AND tgl_transaksi BETWEEN CONCAT(YEAR(OLD.tgl_transaksi),'-01-01') AND LAST_DAY(OLD.tgl_transaksi)), (SELECT SUM(jumlah) FROM transaksi_$lokasi WHERE rekening_kredit=OLD.rekening_kredit AND tgl_transaksi BETWEEN CONCAT(YEAR(OLD.tgl_transaksi),'-01-01') AND LAST_DAY(OLD.tgl_transaksi))) ON DUPLICATE KEY UPDATE 
                        debit= (SELECT SUM(jumlah) FROM transaksi_$lokasi WHERE rekening_debit=OLD.rekening_kredit AND tgl_transaksi BETWEEN CONCAT(YEAR(OLD.tgl_transaksi),'-01-01') AND LAST_DAY(OLD.tgl_transaksi)),
                        kredit=(SELECT SUM(jumlah) FROM transaksi_$lokasi WHERE rekening_kredit=OLD.rekening_kredit AND tgl_transaksi BETWEEN CONCAT(YEAR(OLD.tgl_transaksi),'-01-01') AND LAST_DAY(OLD.tgl_transaksi));

                END;
            ";

        DB::statement($create);
        DB::statement($update);
        DB::statement($delete);

        return redirect('/register')->with('sucess', 'Register berhasil');
    }

    public function logout(Request $request)
    {
        $user = auth()->user()->namadepan.' '.auth()->user()->namabelakang;
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('pesan', 'Terima Kasih '.$user);
    }

    private function generateInvoice($usaha)
    {
        $tgl_pembuatan_invoice = date('Y-m-d', strtotime('-14 days', strtotime($usaha->masa_aktif)));
        $invoice = AdminInvoice::where([
            ['lokasi', $usaha->id],
            ['jenis_pembayaran', '2'],
        ])->where('tgl_invoice', '>=', $tgl_pembuatan_invoice);

        $is_invoice = false;
        if ($invoice->count() <= 0) {
            $tanggal = $tgl_pembuatan_invoice;
            $nomor_invoice = date('ymd');
            $invoice = AdminInvoice::where('tgl_invoice', $tanggal)->count();
            $nomor_urut = str_pad($invoice + 1, '2', '0', STR_PAD_LEFT);
            $nomor_invoice .= $nomor_urut;

            $invoice = AdminInvoice::create([
                'lokasi' => $usaha->id,
                'nomor' => $nomor_invoice,
                'jenis_pembayaran' => 2,
                'tgl_invoice' => $tgl_pembuatan_invoice,
                'tgl_lunas' => $tgl_pembuatan_invoice,
                'status' => 'UNPAID',
                'jumlah' => $usaha->biaya * $usaha->tagihan_invoice,
                'id_user' => 1,
            ]);

            if (date('Y-m-d') >= $tgl_pembuatan_invoice) {
                $is_invoice = $invoice;
            }
        } else {
            if (date('Y-m-d') >= $tgl_pembuatan_invoice) {
                $is_invoice = $invoice->where('status', 'UNPAID')->first();
            }
        }

        return $is_invoice;
    }

    public function app()
    {
        return response()->json([
            'success' => true,
            'time' => date('Y-m-d H:i:s'),
        ]);
    }
}
