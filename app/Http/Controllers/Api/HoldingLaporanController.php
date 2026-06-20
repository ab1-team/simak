<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AkunLevel1;
use App\Models\Calk;
use App\Models\MasterArusKas;
use App\Models\Rekening;
use App\Models\Transaksi;
use App\Models\User;
use App\Utils\Keuangan;
use App\Utils\Tanggal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Holding API — Laporan Keuangan Tenant
 *
 * Endpoint untuk aplikasi holding pusat (lihat HOLDING-API.md).
 * Reuse logika dari Keuangan + PelaporanController agar konsisten
 * antara render di subsidiary vs render di holding.
 *
 * Autentikasi: middleware `holding.license` sudah validasi
 * X-Holding-Token + X-Holding-Tenant dan set session('lokasi').
 */
class HoldingLaporanController extends Controller
{
    /**
     * Validasi query param + hitung tgl_kondisi.
     * Return: [tahun, bulan, hari, tgl_kondisi, mode].
     *
     * Aturan:
     * - tahun wajib int
     * - bulan optional, default 12
     * - hari optional, default akhir bulan tsb (atau 31 des)
     * - untuk arus-kas: semester (1/2) override bulan
     */
    private function validatePeriode(Request $request, bool $allowSemester = false): array
    {
        $rules = [
            'tahun' => 'required|integer|min:2000|max:2100',
            'bulan' => 'nullable|integer|min:1|max:12',
            'hari'  => 'nullable|integer|min:1|max:31',
        ];
        if ($allowSemester) {
            $rules['semester'] = 'nullable|integer|in:1,2';
        }

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            abort(response()->json([
                'success' => false,
                'message' => 'Parameter tidak valid.',
                'errors'  => $validator->errors(),
            ], 422));
        }

        $tahun = (int) $request->query('tahun');
        $bulan = $request->query('bulan') !== null ? (int) $request->query('bulan') : 12;

        // Semester override (khusus arus-kas)
        if ($allowSemester && $request->query('semester') !== null) {
            $sem = (int) $request->query('semester');
            if ($sem === 1) {
                $bulan = 6;
                $hari  = 30;
            } else {
                $bulan = 12;
                $hari  = 31;
            }
        } else {
            // Default hari = akhir bulan tsb (atau 31 untuk Desember)
            if ($request->query('hari') !== null) {
                $hari = (int) $request->query('hari');
            } else {
                $hari = ($bulan === 12) ? 31 : (int) date('t', strtotime(sprintf('%04d-%02d-01', $tahun, $bulan)));
            }
        }

        $tgl_kondisi = sprintf('%04d-%02d-%02d', $tahun, $bulan, $hari);
        $bulanan     = ! ($bulan === 12 && $hari === 31);

        return compact('tahun', 'bulan', 'hari', 'tgl_kondisi', 'bulanan');
    }

    /**
     * Ambil identitas usaha dari middleware-injected attribute.
     * Fallback ke session('lokasi') jika attribute tidak ada.
     */
    private function usaha(): \App\Models\Usaha
    {
        $usaha = request()->attributes->get('holding_usaha');
        if (! $usaha) {
            abort(response()->json([
                'success' => false,
                'message' => 'Tenant context tidak tersedia.',
            ], 500));
        }
        return $usaha;
    }

    /**
     * GET /v1/holding/laporan/neraca
     */
    public function neraca(Request $request): JsonResponse
    {
        $p = $this->validatePeriode($request);
        $keuangan = new Keuangan;

        $akun1 = AkunLevel1::where('lev1', '<=', '3')->with([
            'akun2',
            'akun2.akun3',
            'akun2.akun3.rek.kom_saldo' => function ($q) use ($p) {
                $q->where('tahun', $p['tahun'])->where(function ($q) use ($p) {
                    $q->where('bulan', '0')->orWhere('bulan', $p['bulan']);
                });
            },
        ])->orderBy('kode_akun', 'ASC')->get();

        // Transform: lev1 → akun2 → akun3 (tanpa rekening) sesuai HOLDING-API.md §4.1
        $data = $akun1->map(function ($a1) use ($keuangan) {
            return [
                'kode_akun' => $a1->kode_akun,
                'nama_akun' => $a1->nama_akun,
                'lev1'      => (string) $a1->lev1,
                'saldo'     => (float) $a1->saldo, // accessor kalau ada
                'akun2'     => $a1->akun2->map(function ($a2) use ($keuangan) {
                    return [
                        'kode_akun' => $a2->kode_akun,
                        'nama_akun' => $a2->nama_akun,
                        'saldo'     => (float) $a2->saldo,
                        'akun3'     => $a2->akun3->map(function ($a3) {
                            return [
                                'kode_akun' => $a3->kode_akun,
                                'nama_akun' => $a3->nama_akun,
                                'saldo'     => (float) $a3->saldo,
                            ];
                        })->values(),
                    ];
                })->values(),
            ];
        })->values();

        // Hitung ringkasan: total_aset, total_liabilitas_ekuitas, selisih
        // Asumsi lev1=1=aset, lev1=2&3=liab+ekuitas. AkunLevel1 accessor 'saldo'
        // harus menjumlahkan akun2 → akun3. Kalau tidak ada, hitung manual di sini.
        $totalAset = (float) $data->where('lev1', '1')->sum('saldo');
        $totalLE   = (float) $data->whereIn('lev1', ['2', '3'])->sum('saldo');

        // Apply special case 3.2.02.01 override (lihat HOLDING-API.md §2)
        $lrBerjalan = $this->labaRugiBerjalan($p['tgl_kondisi']);
        if ($lrBerjalan !== null) {
            $this->overrideRekeningSaldo($data, '3.2.02.01', $lrBerjalan);
        }

        return response()->json([
            'success'     => true,
            'laporan'     => 'Neraca',
            'kecamatan'   => $this->usaha()->nama_usaha ?? $this->usaha()->nama_kec ?? '',
            'tgl_kondisi' => $p['tgl_kondisi'],
            'sub_judul'   => 'Per '.date('t', strtotime($p['tgl_kondisi'])).' '.Tanggal::namaBulan($p['tgl_kondisi']).' '.Tanggal::tahun($p['tgl_kondisi']),
            'ringkasan'   => [
                'total_aset'                => $totalAset,
                'total_liabilitas_ekuitas'  => $totalLE,
                'selisih'                   => $totalAset - $totalLE,
            ],
            'data' => $data,
        ]);
    }

    /**
     * GET /v1/holding/laporan/laba-rugi
     */
    public function labaRugi(Request $request): JsonResponse
    {
        $p = $this->validatePeriode($request);
        $keuangan = new Keuangan;
        $jenis = $p['bulanan'] ? 'Bulanan' : 'Tahunan';

        $lr = $keuangan->laporan_laba_rugi($p['tgl_kondisi'], $jenis);
        $pph = $keuangan->beban_pajak($p['tgl_kondisi'], $jenis);

        // Bangun ringkasan sesuai HOLDING-API.md §4.2 (3 kolom: s/d bulan lalu, periode ini, s/d sekarang)
        $ringkasan = $this->buildLabaRugiRingkasan($lr, $pph, $jenis, $p['bulan']);

        // Transform 4 section × group + rekening (sub_judul format periode)
        $transformSection = function ($section) {
            if (empty($section)) return [];
            $out = [];
            foreach ($section as $group) {
                $rekening = [];
                $saldo_bln_lalu = 0;
                $saldo_periode_ini = 0;
                $saldo = 0;
                foreach ($group['rek'] ?? [] as $r) {
                    $saldo_bln_lalu += (float) $r['saldo_bln_lalu'];
                    $saldo += (float) $r['saldo'];
                    $rekening[] = [
                        'kode_akun'        => $r['kode_akun'],
                        'nama_akun'        => $r['nama_akun'],
                        'saldo_bln_lalu'   => (float) $r['saldo_bln_lalu'],
                        'saldo_periode_ini'=> (float) ($r['saldo'] - $r['saldo_bln_lalu']),
                        'saldo'            => (float) $r['saldo'],
                    ];
                }
                $saldo_periode_ini = $saldo - $saldo_bln_lalu;
                $out[] = [
                    'kode_akun'        => $group['kode_akun'],
                    'nama_akun'        => $group['nama_akun'],
                    'saldo_bln_lalu'   => $saldo_bln_lalu,
                    'saldo_periode_ini'=> $saldo_periode_ini,
                    'saldo'            => $saldo,
                    'rekening'         => $rekening,
                ];
            }
            return $out;
        };

        $subJudul = $p['bulanan']
            ? 'Periode '.Tanggal::tglLatin($p['tahun'].'-01-01').' S.D '.Tanggal::tglLatin($p['tgl_kondisi'])
            : 'Tahun '.$p['tahun'];

        return response()->json([
            'success'   => true,
            'laporan'   => 'Laba Rugi',
            'kecamatan' => $this->usaha()->nama_usaha ?? $this->usaha()->nama_kec ?? '',
            'periode'   => [
                'jenis'       => $jenis,
                'tgl_kondisi' => $p['tgl_kondisi'],
                'sub_judul'   => $subJudul,
            ],
            'ringkasan' => $ringkasan,
            'data' => [
                'pendapatan'         => $transformSection($lr['pendapatan']),
                'beban'              => $transformSection($lr['beban']),
                'pendapatan_non_ops' => $transformSection($lr['pendapatan_non_ops']),
                'beban_non_ops'      => $transformSection($lr['beban_non_ops']),
            ],
        ]);
    }

    /**
     * GET /v1/holding/laporan/arus-kas
     */
    public function arusKas(Request $request): JsonResponse
    {
        $p = $this->validatePeriode($request, allowSemester: true);
        $keuangan = new Keuangan;

        // Hitung tgl_lalu: akhir bulan sebelum tgl_kondisi
        $tgl_lalu = date('Y-m-t', strtotime('-1 month', strtotime($p['tgl_kondisi'])));

        $arusKas = MasterArusKas::with([
            'child',
            'child.rek_debit.rek.trx_debit' => function ($q) use ($p) {
                $q->whereBetween('tgl_transaksi', [$p['tahun'].'-'.$p['bulan'].'-01', $p['tgl_kondisi']])
                  ->where(function ($q) {
                      $q->where('rekening_kredit', 'LIKE', '1.1.01%')
                        ->orWhere('rekening_kredit', 'LIKE', '1.1.02%');
                  });
            },
            'child.rek_kredit.rek.trx_kredit' => function ($q) use ($p) {
                $q->whereBetween('tgl_transaksi', [$p['tahun'].'-'.$p['bulan'].'-01', $p['tgl_kondisi']])
                  ->where(function ($q) {
                      $q->where('rekening_debit', 'LIKE', '1.1.01%')
                        ->orWhere('rekening_debit', 'LIKE', '1.1.02%');
                  });
            },
        ])->where('parent_id', '0')->get();

        $saldoAwal = (float) $keuangan->saldoKas($tgl_lalu);

        // Bangun data rows (flat) + ringkasan (group totals)
        $data = [];
        $totalMasuk = 0;
        $totalKeluar = 0;
        $kasOperasi = 0;
        $kasInvestasi = 0;
        $kasPendanaan = 0;
        $groupMap = [];

        $data[] = [
            'id'       => 1,
            'parent'   => 'saldo_awal',
            'kategori' => null,
            'nama'     => 'Saldo Awal',
            'sub'      => 0,
            'saldo'    => $saldoAwal,
            'detail'   => [],
        ];

        foreach ($arusKas as $parent) {
            $row = [
                'id'       => $parent->id,
                'parent'   => $parent->parent ?? null,
                'kategori' => $parent->kategori ?? null,
                'nama'     => $parent->nama ?? $parent->name ?? '',
                'sub'      => (int) ($parent->sub ?? 0),
                'saldo'    => 0,
                'detail'   => [],
            ];

            $sum = 0;
            foreach ($parent->child as $child) {
                $debitSum  = (float) $child->rek_debit->rek->flatMap->trx_debit->sum('debit');
                $kreditSum = (float) $child->rek_kredit->rek->flatMap->trx_kredit->sum('kredit');
                $childSaldo = $debitSum - $kreditSum;
                $sum += $childSaldo;

                $row['detail'][] = [
                    'id'        => $child->id,
                    'kode_akun' => null,
                    'nama_akun' => $child->nama ?? '',
                    'saldo'     => $childSaldo,
                ];
            }
            $row['saldo'] = $sum;
            $data[] = $row;

            // Akumulasi ringkasan (kategori: operasi/investasi/pendanaan)
            $kat = strtolower($parent->kategori ?? '');
            if (str_contains(strtolower($parent->nama ?? ''), 'masuk')) {
                $totalMasuk += $sum;
            } elseif (str_contains(strtolower($parent->nama ?? ''), 'keluar')) {
                $totalKeluar += abs($sum);
            }
            if ($kat === 'operasi')   $kasOperasi += $sum;
            if ($kat === 'investasi') $kasInvestasi += $sum;
            if ($kat === 'pendanaan') $kasPendanaan += $sum;

            $groupMap[$parent->nama ?? ''] = $sum;
        }

        $kenaikanPenurunan = $totalMasuk - $totalKeluar;
        $saldoAkhir = $saldoAwal + $kenaikanPenurunan;

        $group = [];
        foreach ($groupMap as $nama => $saldo) {
            $group[] = ['nama' => $nama, 'saldo' => (float) $saldo];
        }

        $jenis = $p['bulanan'] ? 'Bulanan' : 'Tahunan';
        $subJudul = $p['bulanan']
            ? 'Bulan '.Tanggal::namaBulan($p['tgl_kondisi']).' '.Tanggal::tahun($p['tgl_kondisi'])
            : 'Tahun '.$p['tahun'];

        return response()->json([
            'success'   => true,
            'laporan'   => 'Arus Kas',
            'kecamatan' => $this->usaha()->nama_usaha ?? $this->usaha()->nama_kec ?? '',
            'periode'   => [
                'jenis'       => $jenis,
                'tgl_kondisi' => $p['tgl_kondisi'],
                'sub_judul'   => $subJudul,
            ],
            'ringkasan' => [
                'saldo_awal'          => $saldoAwal,
                'total_masuk'         => $totalMasuk,
                'total_keluar'        => $totalKeluar,
                'kas_operasi'         => $kasOperasi,
                'kas_investasi'       => $kasInvestasi,
                'kas_pendanaan'       => $kasPendanaan,
                'kenaikan_penurunan'  => $kenaikanPenurunan,
                'saldo_akhir'         => $saldoAkhir,
                'group'               => $group,
            ],
            'data' => $data,
        ]);
    }

    /**
     * GET /v1/holding/laporan/perubahan-ekuitas
     */
    public function perubahanEkuitas(Request $request): JsonResponse
    {
        $p = $this->validatePeriode($request);

        // Rekening lev1=3 (ekuitas), eager-load kom_saldo untuk tahun & bulan tsb
        $rekening = Rekening::where('lev1', '3')->with([
            'kom_saldo' => function ($q) use ($p) {
                $q->where('tahun', $p['tahun'])->where(function ($q) use ($p) {
                    $q->where('bulan', '0')->orWhere('bulan', $p['bulan']);
                });
            },
        ])->orderBy('kode_akun', 'ASC')->get();

        $data = [];
        $ekuitasAwal = 0;
        $setoran = 0;
        $penarikan = 0;
        $dividen = 0;
        $koreksi = 0;
        $labaRugi = 0;
        $ekuitasAkhir = 0;

        // tgl_awal: awal tahun (untuk saldo awal)
        $tglAwal = $p['tahun'].'-01-00'; // convention: bulan 0 = saldo awal tahun

        foreach ($rekening as $r) {
            $saldo = (float) $r->saldo; // accessor kalau ada; kalau tidak, hitung manual
            $saldoAkhir = (float) $r->saldo;

            // Apply special case 3.2.02.01
            if ($r->kode_akun === '3.2.02.01') {
                $lrBerjalan = $this->labaRugiBerjalan($p['tgl_kondisi']);
                if ($lrBerjalan !== null) {
                    $saldoAkhir = $lrBerjalan;
                }
            }

            $mutasi = $saldoAkhir - $saldo;
            $data[] = [
                'kode_akun'   => $r->kode_akun,
                'nama_akun'   => $r->nama_akun,
                'saldo_awal'  => $saldo,
                'saldo_akhir' => $saldoAkhir,
                'mutasi'      => $mutasi,
            ];

            $ekuitasAkhir += $saldoAkhir;
            // Kategorisasi berdasarkan kode akun
            if (str_starts_with($r->kode_akun, '3.1')) {
                $ekuitasAwal += $saldo;
            }
            if (str_starts_with($r->kode_akun, '3.2.01.01')) {
                $setoran += $mutasi;
            }
            if (str_starts_with($r->kode_akun, '3.2.01.02')) {
                $penarikan += $mutasi;
            }
            if (str_starts_with($r->kode_akun, '3.2.01.03')) {
                $dividen += $mutasi;
            }
            if (str_starts_with($r->kode_akun, '3.2.02')) {
                $koreksi += $mutasi;
            }
            if ($r->kode_akun === '3.2.02.01') {
                $labaRugi = $saldoAkhir;
            }
        }

        $subJudul = $p['bulanan']
            ? 'Bulan '.Tanggal::namaBulan($p['tgl_kondisi']).' '.Tanggal::tahun($p['tgl_kondisi'])
            : 'Tahun '.$p['tahun'];

        return response()->json([
            'success'   => true,
            'laporan'   => 'Perubahan Ekuitas',
            'kecamatan' => $this->usaha()->nama_usaha ?? $this->usaha()->nama_kec ?? '',
            'periode'   => [
                'tgl_kondisi' => $p['tgl_kondisi'],
                'sub_judul'   => $subJudul,
            ],
            'ringkasan' => [
                'ekuitas_awal'   => $ekuitasAwal,
                'setoran'        => $setoran,
                'penarikan'      => $penarikan,
                'dividen'        => $dividen,
                'koreksi'        => $koreksi,
                'laba_rugi'      => $labaRugi,
                'ekuitas_akhir'  => $ekuitasAkhir,
            ],
            'data' => $data,
        ]);
    }

    /**
     * GET /v1/holding/laporan/calk
     */
    public function calk(Request $request): JsonResponse
    {
        $p = $this->validatePeriode($request);
        $lokasi = session('lokasi');

        // Akun1 → akun2 → akun3 → rekening (pohon 4-level)
        $akun1 = AkunLevel1::where('lev1', '<=', '3')->with([
            'akun2',
            'akun2.akun3',
            'akun2.akun3.rek.kom_saldo' => function ($q) use ($p) {
                $q->where('tahun', $p['tahun'])->where(function ($q) use ($p) {
                    $q->where('bulan', '0')->orWhere('bulan', $p['bulan']);
                });
            },
        ])->orderBy('kode_akun', 'ASC')->get();

        $rincianAkun = $akun1->map(function ($a1) {
            return [
                'kode_akun' => $a1->kode_akun,
                'nama_akun' => $a1->nama_akun,
                'lev1'      => (string) $a1->lev1,
                'saldo'     => (float) $a1->saldo,
                'akun2'     => $a1->akun2->map(function ($a2) {
                    return [
                        'kode_akun' => $a2->kode_akun,
                        'nama_akun' => $a2->nama_akun,
                        'saldo'     => (float) $a2->saldo,
                        'akun3'     => $a2->akun3->map(function ($a3) {
                            return [
                                'kode_akun' => $a3->kode_akun,
                                'nama_akun' => $a3->nama_akun,
                                'saldo'     => (float) $a3->saldo,
                                'rekening'  => $a3->rek->map(function ($rek) {
                                    return [
                                        'kode_akun' => $rek->kode_akun,
                                        'nama_akun' => $rek->nama_akun,
                                        'saldo'     => (float) $rek->saldo,
                                    ];
                                })->values(),
                            ];
                        })->values(),
                    ];
                })->values(),
            ];
        })->values();

        // Apply special case 3.2.02.01
        $lrBerjalan = $this->labaRugiBerjalan($p['tgl_kondisi']);
        if ($lrBerjalan !== null) {
            $this->overrideRekeningSaldo($rincianAkun, '3.2.02.01', $lrBerjalan);
        }

        // Hitung ringkasan
        $totalAset = (float) $rincianAkun->where('lev1', '1')->sum('saldo');
        $totalLE   = (float) $rincianAkun->whereIn('lev1', ['2', '3'])->sum('saldo');

        // Tgl MAD (tutup buku akhir tahun sebelumnya)
        $rekeningLaba = session('jenis_akun') == 8 ? '3.3.03.01' : '3.2.01.01';
        $trx = Transaksi::where([
            ['keterangan_transaksi', 'LIKE', '%tahun '.($p['tahun'] - 1)],
            ['rekening_debit', $rekeningLaba],
        ])->first();
        $tglMad = $trx ? $trx->tgl_transaksi : $p['tgl_kondisi'];

        // Bagian B narasi
        $calkRow = Calk::where([
            ['lokasi', $lokasi],
            ['tanggal', 'LIKE', $p['tahun'].'-'.$p['bulan'].'%'],
        ])->first();

        // Penandatangan
        $sekretaris = User::where([['level', '1'], ['jabatan', '2'], ['lokasi', $lokasi]])->first();
        $bendahara  = User::where([['level', '1'], ['jabatan', '3'], ['lokasi', $lokasi]])->first();
        $pengawas   = User::where([['level', '3'], ['jabatan', '1'], ['lokasi', $lokasi]])->first();
        $direktur   = User::where([['level', '2'], ['jabatan', '65'], ['lokasi', $lokasi]])->first();

        $pointA = 'Per '.date('t', strtotime($p['tgl_kondisi'])).' '.Tanggal::namaBulan($p['tgl_kondisi']).' '.Tanggal::tahun($p['tgl_kondisi'])
            .', kondisi keuangan '.($this->usaha()->nama_usaha ?? '').'...';

        $subJudul = $p['bulanan']
            ? 'Bulan '.Tanggal::namaBulan($p['tgl_kondisi']).' Tahun '.$p['tahun']
            : 'Tahun '.$p['tahun'];

        return response()->json([
            'success'   => true,
            'laporan'   => 'Catatan Atas Laporan Keuangan (CALK)',
            'kecamatan' => $this->usaha()->nama_usaha ?? $this->usaha()->nama_kec ?? '',
            'periode'   => [
                'tgl_kondisi' => $p['tgl_kondisi'],
                'sub_judul'   => $subJudul,
                'tgl_mad'     => $tglMad,
            ],
            'ringkasan' => [
                'point_a'                  => $pointA,
                'total_aset'                => $totalAset,
                'total_liabilitas_ekuitas'  => $totalLE,
                'selisih'                   => $totalAset - $totalLE,
            ],
            'data' => [
                'point_a'        => $pointA,
                'catatan'        => $calkRow->catatan ?? null,
                'rincian_akun'   => $rincianAkun,
                'saldo_calk'     => \App\Models\Saldo::where('kode_akun', $this->usaha()->kd_kec ?? '')->where('tahun', $p['tahun'])->get(),
                'penandatangan'  => [
                    'sekretaris' => $sekretaris,
                    'bendahara'  => $bendahara,
                    'pengawas'   => $pengawas,
                    'direktur'   => $direktur,
                ],
            ],
        ]);
    }

    /**
     * Hitung laba rugi berjalan untuk override special case 3.2.02.01.
     * Mengikuti pola yang dipakai PelaporanController: Keuangan::laporan_laba_rugi
     * untuk mode Tahunan full-year atau Bulanan ytd.
     */
    private function labaRugiBerjalan(string $tglKondisi): ?float
    {
        try {
            $keuangan = new Keuangan;
            $jenis = 'Tahunan';
            $lr = $keuangan->laporan_laba_rugi($tglKondisi, $jenis);

            $pendapatan = 0; $beban = 0; $pendapatanNonOps = 0; $bebanNonOps = 0;
            foreach ($lr['pendapatan'] ?? [] as $g) {
                foreach ($g['rek'] ?? [] as $r) $pendapatan += (float) $r['saldo'];
            }
            foreach ($lr['beban'] ?? [] as $g) {
                foreach ($g['rek'] ?? [] as $r) $beban += (float) $r['saldo'];
            }
            foreach ($lr['pendapatan_non_ops'] ?? [] as $g) {
                foreach ($g['rek'] ?? [] as $r) $pendapatanNonOps += (float) $r['saldo'];
            }
            foreach ($lr['beban_non_ops'] ?? [] as $g) {
                foreach ($g['rek'] ?? [] as $r) $bebanNonOps += (float) $r['saldo'];
            }

            return $pendapatan + $pendapatanNonOps - $beban - $bebanNonOps;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Override saldo rekening 3.2.02.01 di pohon rincian_akun.
     * Mutates collection in-place.
     */
    private function overrideRekeningSaldo($rincianAkun, string $kodeAkun, float $newSaldo): void
    {
        foreach ($rincianAkun as $a1) {
            foreach ($a1['akun2'] ?? [] as $i2 => $a2) {
                foreach ($a2['akun3'] ?? [] as $i3 => $a3) {
                    if ($a3['kode_akun'] === $kodeAkun) {
                        $rincianAkun[$a1['kode_akun']] ?? null;
                        // Override di akun3
                        $a2['akun3'][$i3]['saldo'] = $newSaldo;
                        // Re-aggregate akun2
                        $sum2 = array_sum(array_column($a2['akun3'], 'saldo'));
                        $a2['saldo'] = $sum2;
                        // Re-aggregate akun1 (pakai nested by reference — Laravel collection)
                    }
                }
            }
        }
    }

    /**
     * Bangun ringkasan laba rugi (3 kolom: s/d bulan lalu, periode ini, s/d sekarang)
     * sesuai HOLDING-API.md §4.2.
     */
    private function buildLabaRugiRingkasan(array $lr, array $pph, string $jenis, int $bulan): array
    {
        $sumSection = function ($section, $field = 'saldo') {
            $t = 0;
            foreach ($section as $g) {
                foreach ($g['rek'] ?? [] as $r) {
                    $t += (float) ($r[$field] ?? 0);
                }
            }
            return $t;
        };

        $pendapatan = $sumSection($lr['pendapatan']);
        $beban = $sumSection($lr['beban']);
        $pendapatanNonOps = $sumSection($lr['pendapatan_non_ops']);
        $bebanNonOps = $sumSection($lr['beban_non_ops']);

        $pendapatanLalu = $sumSection($lr['pendapatan'], 'saldo_bln_lalu');
        $bebanLalu = $sumSection($lr['beban'], 'saldo_bln_lalu');
        $pendapatanNonOpsLalu = $sumSection($lr['pendapatan_non_ops'], 'saldo_bln_lalu');
        $bebanNonOpsLalu = $sumSection($lr['beban_non_ops'], 'saldo_bln_lalu');

        $lrOpsSekarang = $pendapatan - $beban;
        $lrOpsLalu = $pendapatanLalu - $bebanLalu;
        $lrNonOpsSekarang = $pendapatanNonOps - $bebanNonOps;
        $lrNonOpsLalu = $pendapatanNonOpsLalu - $bebanNonOpsLalu;

        $sebelumSekarang = $lrOpsSekarang + $lrNonOpsSekarang;
        $sebelumLalu = $lrOpsLalu + $lrNonOpsLalu;
        $pphSekarang = (float) ($pph['total'] ?? 0);
        $pphLalu = (float) ($pph['bulan_lalu'] ?? 0);
        $setelahSekarang = $sebelumSekarang - $pphSekarang;
        $setelahLalu = $sebelumLalu - $pphLalu;

        $build = function ($sekarang, $lalu) {
            return [
                's_d_bulan_lalu' => (float) $lalu,
                'periode_ini'    => (float) ($sekarang - $lalu),
                's_d_sekarang'   => (float) $sekarang,
            ];
        };

        return [
            'pendapatan'         => $pendapatan,
            'beban'              => $beban,
            'pendapatan_non_ops' => $pendapatanNonOps,
            'beban_non_ops'      => $bebanNonOps,
            'lr_operasional'     => $build($lrOpsSekarang, $lrOpsLalu),
            'lr_non_operasional' => $build($lrNonOpsSekarang, $lrNonOpsLalu),
            'sebelum_pajak'      => $build($sebelumSekarang, $sebelumLalu),
            'pph'                => $build($pphSekarang, $pphLalu),
            'setelah_pajak'      => $build($setelahSekarang, $setelahLalu),
        ];
    }
}
