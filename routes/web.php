<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AppController;
use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\InvoiceController;
use App\Http\Controllers\Admin\LicenseController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Auth\SsoController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DesaController;
use App\Http\Controllers\GenerateController;
use App\Http\Controllers\PelaporanController;
use App\Http\Controllers\SopController;
use App\Http\Controllers\TransaksiController;
use App\Http\Controllers\UserController;
use App\Models\Usaha;
use App\Models\User;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::middleware('maintenance')->group(function () {
    Route::prefix('db')->group(function () {
        Route::get('/', [AdminAuthController::class, 'index'])->middleware('guest');
        Route::post('/auth', [AdminAuthController::class, 'login'])->middleware('guest');

        Route::get('/dashboard', [AdminController::class, 'index'])->middleware('master');

        Route::get('/app', [AppController::class, 'index'])->middleware('master');
        Route::get('/app/register', [AppController::class, 'register'])->middleware('master');
        Route::get('/app/{usaha}', [AppController::class, 'show'])->middleware('master');
        Route::get('/app/desa/{kode}', [AppController::class, 'desa'])->middleware('master');
        Route::get('/app/kecamatan/{kode}', [AppController::class, 'kecamatan'])->middleware('master');
        Route::get('/app/kabupaten/{kode}', [AppController::class, 'kabupaten'])->middleware('master');
        Route::get('/app/provinsi/{kode}', [AppController::class, 'provinsi'])->middleware('master');
        Route::post('/app/{usaha}/edit', [AppController::class, 'update'])->middleware('master');

        Route::get('/user/lokasi/{lokasi}', [AdminUserController::class, 'userLokasi'])->middleware('master');
        Route::post('/user/{user}/akses_tombol', [AdminUserController::class, 'aksesTombol'])->middleware('master');
        Route::post('/user/{user}/hak_akses', [AdminUserController::class, 'hakAkses'])->middleware('master');
        Route::resource('/user', AdminUserController::class)->middleware('master');

        Route::get('/invoice', [InvoiceController::class, 'index'])->middleware('master');
        Route::post('/invoice', [InvoiceController::class, 'store'])->middleware('master');
        Route::get('/nomor_invoice', [InvoiceController::class, 'InvoiceNo']);

        Route::get('/invoice/{invoice}', [InvoiceController::class, 'show'])->middleware('master');
        Route::get('/unpaid', [InvoiceController::class, 'unpaid'])->middleware('master');
        Route::get('/paid', [InvoiceController::class, 'paid'])->middleware('master');

        Route::put('/invoice/{invoice}/edit', [InvoiceController::class, 'update'])->middleware('master');

        Route::get('/license', [LicenseController::class, 'index'])->middleware('master');
        Route::post('/license', [LicenseController::class, 'store'])->middleware('master');
        Route::get('/license/{license}', [LicenseController::class, 'show'])->middleware('master');
        Route::put('/license/{license}', [LicenseController::class, 'update'])->middleware('master');
        Route::delete('/license/{license}', [LicenseController::class, 'destroy'])->middleware('master');

        Route::post('/logout', [AdminAuthController::class, 'logout'])->middleware('master');
    });

    Route::get('/', [AuthController::class, 'index'])->middleware(['guest', 'is_aktif'])->name('/');
    Route::get('/register', [AuthController::class, 'register']);
    Route::get('/register/user', [AuthController::class, 'user']);

    Route::get('/ambil_prov', [AuthController::class, 'provinsi'])->middleware(['guest', 'is_aktif']);
    Route::get('/ambil_kab/{kode}', [AuthController::class, 'kabupaten'])->middleware(['guest', 'is_aktif']);
    Route::get('/ambil_kec/{kode}', [AuthController::class, 'kecamatan'])->middleware(['guest', 'is_aktif']);
    Route::get('/ambil_des/{kode}', [AuthController::class, 'desa'])->middleware(['guest', 'is_aktif']);

    Route::post('/login', [AuthController::class, 'login'])->middleware(['guest', 'is_aktif']);
    Route::post('/register', [AuthController::class, 'store']);
    Route::post('/app', [AuthController::class, 'app']);

    Route::get('/pelaporan', [PelaporanController::class, 'index'])->middleware(['basic', 'is_aktif']);
    Route::get('/pelaporan/sub_laporan/{file}', [PelaporanController::class, 'subLaporan'])->middleware(['basic', 'is_aktif']);
    Route::post('/pelaporan/preview', [PelaporanController::class, 'preview'])->middleware(['basic', 'is_aktif']);
    Route::post('/pelaporan/preview/{lokasi?}', [PelaporanController::class, 'preview'])->middleware(['basic', 'is_aktif']);

    Route::get('/pelaporan/ba_bumdesma', [PelaporanController::class, 'beritaAcara'])->middleware(['auth', 'is_aktif']);
    Route::get('/pelaporan/mou', [PelaporanController::class, 'mou'])->middleware(['auth', 'is_aktif']);
    Route::get('/pelaporan/ts', [PelaporanController::class, 'ts'])->middleware(['auth', 'is_aktif']);

    Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'is_aktif']);
    Route::get('/piutang_jasa', [DashboardController::class, 'piutang'])->middleware(['auth', 'is_aktif']);
    Route::get('/pelaporan/invoice/{invoice}', [PelaporanController::class, 'invoice']);
    Route::get('/simpan_saldo', [DashboardController::class, 'simpanSaldo'])->middleware(['auth', 'is_aktif']);

    Route::post('/dashboard/jatuh_tempo', [DashboardController::class, 'jatuhTempo'])->middleware(['auth', 'is_aktif']);
    Route::post('/dashboard/nunggak', [DashboardController::class, 'nunggak'])->middleware(['auth', 'is_aktif']);
    Route::post('/dashboard/tagihan', [DashboardController::class, 'tagihan'])->middleware(['auth', 'is_aktif']);
    Route::get('/dashboard/pinjaman', [DashboardController::class, 'pinjaman'])->middleware(['auth', 'is_aktif']);
    Route::get('/dashboard/pemanfaat', [DashboardController::class, 'pemanfaat'])->middleware(['auth', 'is_aktif']);

    Route::get('/pengaturan/sop', [SopController::class, 'index'])->middleware(['auth', 'is_aktif']);
    Route::get('/pengaturan/ttd_pelaporan', [SopController::class, 'ttdPelaporan'])->middleware(['auth', 'is_aktif']);
    Route::get('/pengaturan/ttd_spk', [SopController::class, 'ttdSpk'])->middleware(['auth', 'is_aktif']);

    Route::get('/pengaturan/coa', [SopController::class, 'coa'])->middleware(['auth', 'is_aktif']);
    Route::post('/pengaturan/coa', [SopController::class, 'createCoa'])->middleware(['auth', 'is_aktif']);
    Route::put('/pengaturan/coa/{kode_akun}', [SopController::class, 'updateCoa'])->middleware(['auth', 'is_aktif']);
    Route::delete('/pengaturan/coa/{rekening}', [SopController::class, 'deleteCoa'])->middleware(['auth', 'is_aktif']);

    Route::put('/pengaturan/lembaga/{usaha}', [SopController::class, 'lembaga'])->middleware(['auth', 'is_aktif']);
    Route::put('/pengaturan/pengelola/{usaha}', [SopController::class, 'pengelola'])->middleware(['auth', 'is_aktif']);
    Route::put('/pengaturan/logo/{usaha}', [SopController::class, 'logo'])->middleware(['auth', 'is_aktif']);
    Route::put('/pengaturan/kop_laporan/{usaha}', [SopController::class, 'kopLaporan'])->middleware(['auth', 'is_aktif']);
    Route::get('/pengaturan/local_view/{key}/{val?}', [SopController::class, 'localView'])->middleware(['auth', 'is_aktif']);

    Route::post('/pengaturan/whatsapp/{token}', [SopController::class, 'whatsapp'])->middleware(['auth', 'is_aktif']);

    Route::get('/pengaturan/invoice', [SopController::class, 'invoice'])->middleware(['auth', 'is_aktif']);
    Route::get('/pengaturan/{inv}/invoice', [SopController::class, 'detailInvoice'])->middleware(['auth', 'is_aktif']);

    Route::post('/pengaturan/sop/simpanttdpelaporan', [SopController::class, 'simpanTtdPelaporan'])->middleware(['auth', 'is_aktif']);

    Route::resource('/database/desa', DesaController::class)->middleware(['auth', 'is_aktif']);

    Route::get('/transaksi/jurnal_umum/', [TransaksiController::class, 'jurnalUmum'])->middleware(['auth', 'is_aktif']);
    Route::get('/transaksi/jurnal_umum/{transaksi}', [TransaksiController::class, 'editTransaksi'])->middleware(['auth', 'is_aktif']);

    Route::get('/transaksi/jurnal_angsuran', [TransaksiController::class, 'jurnalAngsuran'])->middleware(['auth', 'is_aktif']);
    Route::get('/transaksi/tutup_buku', [TransaksiController::class, 'jurnalTutupBuku'])->middleware(['auth', 'is_aktif']);
    Route::get('/trasaksi/saldo/{kode_akun}', [TransaksiController::class, 'saldo'])->middleware(['auth', 'is_aktif']);

    Route::get('/transaksi/ambil_rekening/{id}', [TransaksiController::class, 'rekening'])->middleware(['auth', 'is_aktif']);
    Route::get('/transaksi/form_nominal/', [TransaksiController::class, 'form'])->middleware(['auth', 'is_aktif']);
    Route::get('/transaksi/form_angsuran/{id_pinkel}', [TransaksiController::class, 'formAngsuran'])->middleware(['auth', 'is_aktif']);
    Route::get('/transaksi/angsuran/target/{id_pinkel}', [TransaksiController::class, 'targetAngsuran'])->middleware(['auth', 'is_aktif']);

    Route::get('/transaksi/data/{idt}', [TransaksiController::class, 'data'])->middleware(['auth', 'is_aktif']);

    Route::get('/transaksi/tutup_buku/saldo_awal/{tahun}', [TransaksiController::class, 'saldoAwal'])->middleware(['auth', 'is_aktif']);
    Route::post('/transaksi/tutup_buku/saldo', [TransaksiController::class, 'saldoTutupBuku'])->middleware(['auth', 'is_aktif']);
    Route::post('/transaksi/tutup_buku', [TransaksiController::class, 'simpanTutupBuku'])->middleware(['auth', 'is_aktif']);

    Route::post('/transaksi/simpan_laba', [TransaksiController::class, 'simpanAlokasiLaba'])->middleware(['auth', 'is_aktif']);
    Route::post('/transaksi/reversal', [TransaksiController::class, 'reversal'])->middleware(['auth', 'is_aktif']);
    Route::post('/transaksi/hapus', [TransaksiController::class, 'hapus'])->middleware(['auth', 'is_aktif']);

    Route::get('/transaksi/angsuran/lpp/{id}', [TransaksiController::class, 'lpp'])->middleware(['auth', 'is_aktif']);
    Route::get('/transaksi/angsuran/detail_angsuran/{id}', [TransaksiController::class, 'detailAngsuran'])->middleware(['auth', 'is_aktif']);
    Route::get('/transaksi/detail_transaksi/', [TransaksiController::class, 'detailTransaksi'])->middleware(['auth', 'is_aktif']);

    Route::post('/transaksi/angsuran', [TransaksiController::class, 'angsuran'])->middleware(['auth', 'is_aktif']);
    Route::post('/transaksi/angsuran/cetak_bkm', [TransaksiController::class, 'cetakBkm'])->middleware(['auth', 'is_aktif']);

    Route::get('/transaksi/generate_real/{id_pinkel}', [TransaksiController::class, 'generateReal'])->middleware(['auth', 'is_aktif']);
    Route::get('/transaksi/regenerate_real/{id_pinkel}', [TransaksiController::class, 'realisasi'])->middleware(['auth', 'is_aktif']);

    Route::get('/transaksi/angsuran/form_anggota/{id_pinkel}', [TransaksiController::class, 'formAnggota'])->middleware(['auth', 'is_aktif']);
    Route::get('/angsuran/notifikasi/{idtp}', [TransaksiController::class, 'notifikasi'])->middleware(['auth', 'is_aktif']);

    Route::get('/transaksi/dokumen/kuitansi/{id}', [TransaksiController::class, 'kuitansi'])->middleware(['auth', 'is_aktif']);
    Route::get('/transaksi/dokumen/kuitansi_thermal/{id}', [TransaksiController::class, 'kuitansi_thermal'])->middleware(['auth', 'is_aktif']);
    Route::get('/transaksi/dokumen/bkk/{id}', [TransaksiController::class, 'bkk'])->middleware(['auth', 'is_aktif']);
    Route::get('/transaksi/dokumen/bkm/{id}', [TransaksiController::class, 'bkm'])->middleware(['auth', 'is_aktif']);
    Route::get('/transaksi/dokumen/bm/{id}', [TransaksiController::class, 'bm'])->middleware(['auth', 'is_aktif']);

    Route::get('/transaksi/dokumen/struk/{id}', [TransaksiController::class, 'struk'])->middleware(['auth', 'is_aktif']);
    Route::get('/transaksi/dokumen/struk_matrix/{id}', [TransaksiController::class, 'strukMatrix'])->middleware(['auth', 'is_aktif']);
    Route::get('/transaksi/dokumen/struk_thermal/{id}', [TransaksiController::class, 'strukThermal'])->middleware(['auth', 'is_aktif']);
    Route::get('/transaksi/dokumen/bkm_angsuran/{id}', [TransaksiController::class, 'bkmAngsuran'])->middleware(['auth', 'is_aktif']);
    Route::get('/transaksi/dokumen/bkk_angsuran/{id}', [TransaksiController::class, 'bkkAngsuran'])->middleware(['auth', 'is_aktif']);
    Route::post('/transaksi/dokumen/cetak', [TransaksiController::class, 'cetak'])->middleware(['auth', 'is_aktif']);

    Route::get('/transaksi/ebudgeting', [TransaksiController::class, 'ebudgeting'])->middleware(['auth', 'is_aktif']);
    Route::post('/transaksi/anggaran', [TransaksiController::class, 'formAnggaran'])->middleware(['auth', 'is_aktif']);
    Route::post('/transaksi/simpan_anggaran', [TransaksiController::class, 'simpanAnggaran'])->middleware(['auth', 'is_aktif']);

    Route::get('/transaksi/taksiran_pajak', [TransaksiController::class, 'taksiranPajak'])->middleware(['auth', 'is_aktif']);
    Route::post('/transaksi/taksiran_pajak', [TransaksiController::class, 'cetakTaksiranPajak'])->middleware(['auth', 'is_aktif']);
    Route::get('/transaksi/pendapatan/{tahun}/{bulan}', [TransaksiController::class, 'pendapatan'])->middleware(['auth', 'is_aktif']);

    Route::resource('/transaksi', TransaksiController::class)->middleware(['auth', 'is_aktif']);

    Route::resource('/profil', UserController::class);

    Route::get('/sync/{lokasi}', [DashboardController::class, 'sync'])->middleware(['auth', 'is_aktif']);
    Route::get('/link', function () {
        $target = '/home/akubumdes/public_html/apps/storage/app/public';
        $shortcut = '/home/akubumdes/public_html/apps/public/storage';
        symlink($target, $shortcut);
    });

    Route::get('/user', function () {
        $usaha = Usaha::where('domain', request()->getHost())->orwhere('domain_alt', request()->getHost())->first();
        $users = User::where('lokasi', $usaha->id)->with('l', 'j')->orderBy('level', 'ASC')->orderBy('jabatan', 'ASC')->get();

        return view('welcome', ['users' => $users, 'usaha' => $usaha]);
    });

    Route::get('/download/{file}', function ($file) {
        return response()->download(storage_path('app/public/docs/'.$file));
    })->name('download');

    Route::get('/generate', [GenerateController::class, 'index']);
    Route::get('/generate/kelompok', [GenerateController::class, 'kelompok']);
    Route::post('/generate/save/{offset?}', [GenerateController::class, 'generate']);

    Route::get('/unpaid', [DashboardController::class, 'unpaid'])->middleware(['auth', 'is_aktif']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware(['auth']);

    Route::get('/{invoice}', [PelaporanController::class, 'invoice']);
});

// SSO entry from Holding — must stay outside the maintenance group so the
// Holding dashboard can still reach us during planned downtime. Only valid
// when caller is not yet authenticated locally.
Route::get('/auth/sso', [SsoController::class, 'consume'])
    ->name('auth.sso')
    ->middleware('guest');
