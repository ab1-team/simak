<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AdminInvoice;
use App\Models\License;
use App\Models\Menu;
use App\Models\MenuTombol;
use App\Models\Usaha;
use App\Models\User;
use App\Services\SsoTokenVerifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SsoController extends Controller
{
    public function __construct(private readonly SsoTokenVerifier $verifier)
    {
    }

    /**
     * Consume a Holding-issued SSO token and log the user in locally.
     *
     * Mirrors AuthController::login() mechanics — same Usaha-by-host lookup,
     * same session shape (nama_usaha, nama, foto, logo, lokasi, usaha,
     * lokasi_user, icon, menu, tombol, jenis_akun), same invoice bootstrap,
     * same flash payload. Credential check is replaced by HMAC verification.
     *
     * Flow:
     *   1. Verify HMAC + expiry
     *   2. Resolve local Usaha from request host
     *   3. Auto-resolve local admin user: level=1, jabatan=1, lokasi=Usaha.id
     *   4. Confirm user active
     *   5. Confirm local license usable
     *   6. Auth::loginUsingId + session regenerate + populate session
     *   7. Redirect to /dashboard with flash pesan + is_invoice
     */
    public function consume(Request $request)
    {
        $token = (string) $request->query('token', '');
        if ($token === '') {
            abort(400, 'Token SSO tidak ditemukan.');
        }

        // 1. Verify signature + expiry.
        $payload = $this->verifier->decode($token);
        if ($payload === null) {
            Log::warning('SSO token invalid or expired', [
                'ip' => $request->ip(),
                'ua' => $request->userAgent(),
            ]);
            abort(401, 'Token SSO tidak valid atau sudah kedaluwarsa.');
        }

        // 2. Resolve local Usaha from request host.
        $host = $request->getHost();
        $usaha = Usaha::where('domain', $host)
            ->orWhere('domain_alt', $host)
            ->first();

        if (! $usaha) {
            Log::warning('SSO host not bound to any Usaha', [
                'host' => $host,
                'ip' => $request->ip(),
            ]);
            abort(403, 'Host tidak terdaftar pada BUMDes manapun.');
        }

        // 3. Auto-resolve local admin user: level=1, jabatan=1, lokasi=Usaha.id.
        $user = User::where('lokasi', $usaha->id)
            ->where('level', 1)
            ->where('jabatan', 1)
            ->first();

        if (! $user) {
            Log::warning('SSO local admin user not found', [
                'usaha_id' => $usaha->id,
                'host' => $host,
            ]);
            abort(403, 'User admin BUMDes belum tersedia.');
        }

        if (! $user->isActive()) {
            Log::warning('SSO user inactive', [
                'local_user_id' => $user->id,
                'usaha_id' => $usaha->id,
            ]);
            abort(403, 'Akun admin BUMDes dinonaktifkan.');
        }

        // 4. Local license check — bound to the host's tenant (usaha).
        $license = License::where('usaha_id', $usaha->id)->first();
        if (! $license || ! $license->isUsable()) {
            Log::warning('SSO license not usable', [
                'local_user_id' => $user->id,
                'usaha_id' => $usaha->id,
            ]);
            abort(403, 'Lisensi tidak aktif atau sudah kedaluwarsa.');
        }

        // 5. Login — same mechanic as AuthController::login.
        $icon = '/assets/img/icon/favicon.png';
        if ($usaha->logo) {
            $icon = '/storage/logo/'.$usaha->logo;
        }

        $aksesMenu = Menu::whereNotIn('id', explode('#', $user->akses_menu))
            ->where('parent_id', '0')
            ->with('child')
            ->orderBy('sort', 'ASC')
            ->get();
        $aksesTombol = MenuTombol::whereNotIn('id', explode('#', $user->akses_tombol))
            ->pluck('akses')
            ->toArray();

        if (! Auth::loginUsingId($user->id)) {
            Log::error('SSO Auth::loginUsingId failed', [
                'local_user_id' => $user->id,
            ]);
            abort(500, 'Gagal login otomatis.');
        }
        $request->session()->regenerate();

        session([
            'nama_usaha'  => $usaha->nama_usaha,
            'nama'        => $user->namadepan.' '.$user->namabelakang,
            'foto'        => $user->foto,
            'logo'        => $usaha->logo,
            'lokasi'      => $usaha->id,
            'usaha'       => $user->usaha,
            'lokasi_user' => $user->lokasi,
            'icon'        => $icon,
            'menu'        => $aksesMenu,
            'tombol'      => $aksesTombol,
            'jenis_akun'  => $usaha->jenis_akun,
        ]);

        // 6. Invoice bootstrap (same as AuthController::login).
        $inv = $this->generateInvoice($usaha);

        Log::info('SSO auto-login success', [
            'user_id'    => $user->id,
            'usaha_id'   => $usaha->id,
            'license_id' => $license->id,
            'payload_uid' => $payload['uid'],
            'payload_lid' => $payload['lid'],
        ]);

        return redirect('/dashboard')->with([
            'pesan' => 'Selamat Datang '.$user->namadepan.' '.$user->namabelakang,
            'is_invoice' => $inv,
        ]);
    }

    /**
     * Mirror of AuthController::generateInvoice() — bootstrap periodic invoice
     * for the BUMDes the user just signed into.
     */
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
}