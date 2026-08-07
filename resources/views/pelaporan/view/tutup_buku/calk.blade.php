@php
    use App\Utils\Keuangan;
    $keuangan = new Keuangan();

    $saldo_aset = 0;
    $peraturan_desa = $usaha->peraturan_desa;

    $calk = [
        '0' => [
            'th_lalu' => 0,
            'th_ini' => 0,
        ],
        '1' => [
            'th_lalu' => 0,
            'th_ini' => 0,
        ],
        '2' => [
            'th_lalu' => 0,
            'th_ini' => 0,
        ],
        '3' => [
            'th_lalu' => 0,
            'th_ini' => 0,
        ],
        '4' => [
            'th_lalu' => 0,
            'th_ini' => 0,
        ],
        '5' => [
            'th_lalu' => 0,
            'th_ini' => 0,
        ],
    ];

    $i = 0;
    foreach ($saldo_calk as $_saldo) {
        if ($tgl_kondisi >= $tgl_mad) {
            $calk["$i"]['th_lalu'] = floatval($_saldo->debit);
            $calk["$i"]['th_ini'] = floatval($_saldo->kredit);
        }

        $i++;
    }
@endphp

@extends('pelaporan.layout.base')

@section('content')
    <style>
        ol,
        ul {
            margin-left: unset;
        }
    </style>
    <table border="0" width="100%" cellspacing="0" cellpadding="0">
        <tr>
            <td colspan="3" align="center">
                <div style="font-size: 18px;">
                    <b>CATATAN ATAS LAPORAN KEUANGAN</b>
                </div>
                <div style="font-size: 18px; text-transform: uppercase;">
                    <b>{!! $usaha->nama_usaha !!}</b>
                </div>
                <div style="font-size: 16px;">
                    <b>{{ strtoupper($sub_judul) }}</b>
                </div>
            </td>
        </tr>
        <tr>
            <td colspan="3" height="5"></td>
        </tr>
    </table>

    <ol style="list-style: upper-alpha;">
        <li>
            <div style="text-transform: uppercase;">Gambaran Umum</div>
            <div style="text-align: justify">
                {{ $usaha->nama_usaha }} adalah Badan Usaha yang didirikan oleh {{ $usaha->d->sebutan_desa->sebutan_desa }}
                {{ $usaha->d->nama_desa }} sebagai tindak lanjut dari amanat Pemerintahan Republik Indonesia yang antara
                lain tertuang dalam UU nomer 6 Tahun 2014 Tentang Desa., Peraturan Menteri Desa Pembangunan Daerah
                Tertinggal dan Transmigrasi Nomor 4 Tahun 2015 tentang Pendirian, Pengurusan dan Pengelolaan, dan Pembubaran
                Badan Usaha Milik Desa., Peraturan Menteri Dalam Negeri Nomor 1 Tahun 2016 tentang Pengelolaan Aset Desa.,
                PP No.72 Tahun 2005 tentang Desa Peraturan Pemerintah Nomor 11 Tahun 2011 Tentang Bumdes.
            </div>
            <p style="text-align: justify">
                Sesuai amanat regulasi maka setiap desa bisa berivestasi kepada Bumdes melalu penetapan APBDes sebagai modal
                mayoritas dan bisa menerima investasi masyarakat sebagai tambahan modal Bumdes. Modal tersebut digunakan
                untuk meningkatkan produktifitas ekonomi masyarakat dan keuangan desa dengan mengembangkan fungsi dan
                manfaat potensi sumber daya alam dan sumber daya manusia di wilayah desa setempat, disamping mencari sumber
                dukungan pengembangan dari pihak swasta dan pemerintah baik dilingkungan desa sendiri maupun luar desa.
            </p>
            <p style="text-align: justify">
                {{ $usaha->nama_usaha }} didirikan di {{ $usaha->d->sebutan_desa->sebutan_desa }} {{ $usaha->d->nama_desa }}
                berdasarkan PERATURAN KEPALA DESA NOMOR {{ $usaha->peraturan_desa }} dan mendapatkan Sertifikat Badan Hukum
                dari Menteri Hukum dan Hak Asasi Manusia No. {{ $usaha->nomor_bh }}. Dalam perjalanan pengelolaan
                manajeman dan bisnis {{ $usaha->nama_usaha }} memiliki struktur kepengurusan pusat sebagai berikut :
            </p>
            <table style="margin-top: -10px; margin-left: 15px;">
                <tr>
                    <td style="padding: 0px; 4px;" width="100">{{ $usaha->badan_pengawas }}</td>
                    <td style="padding: 0px; 4px;">:</td>
                    <td style="padding: 0px; 4px;">
                        {{ $pengawas ? $pengawas->namadepan . ' ' . $pengawas->namabelakang : '......................................' }}
                    </td>
                </tr>
                @if ($dir_utama)
                    <tr>
                        <td style="padding: 0px; 4px;">{{ $dir_utama->j->nama_jabatan }}</td>
                        <td style="padding: 0px; 4px;">:</td>
                        <td style="padding: 0px; 4px;">
                            {{ $dir_utama->namadepan . ' ' . $dir_utama->namabelakang }}
                        </td>
                    </tr>
                @else
                    <tr>
                        <td style="padding: 0px; 4px;">{{ $usaha->kepala_lembaga }}</td>
                        <td style="padding: 0px; 4px;">:</td>
                        <td style="padding: 0px; 4px;">
                            {{ $dir ? $dir->namadepan . ' ' . $dir->namabelakang : '......................................' }}
                        </td>
                    </tr>
                @endif
                <tr>
                    <td style="padding: 0px; 4px;">{{ $usaha->kabag_administrasi }}</td>
                    <td style="padding: 0px; 4px;">:</td>
                    <td style="padding: 0px; 4px;">
                        {{ $sekr ? $sekr->namadepan . ' ' . $sekr->namabelakang : '......................................' }}
                    </td>
                </tr>
                <tr>
                    <td style="padding: 0px; 4px;">{{ $usaha->kabag_keuangan }}</td>
                    <td style="padding: 0px; 4px;">:</td>
                    <td style="padding: 0px; 4px;">
                        {{ $bend ? $bend->namadepan . ' ' . $bend->namabelakang : '......................................' }}
                    </td>
                </tr>
                @if ($dir_utama)
                    <tr>
                        <td style="padding: 0px; 4px;">{{ $usaha->kepala_lembaga }}</td>
                        <td style="padding: 0px; 4px;">:</td>
                        <td style="padding: 0px; 4px;">
                            {{ $dir ? $dir->namadepan . ' ' . $dir->namabelakang : '......................................' }}
                        </td>
                    </tr>
                @endif
                @foreach ($personalia ?? [] as $p)
                    <tr>
                        <td style="padding: 0px; 4px;">{{ $p->sebutan }}</td>
                        <td style="padding: 0px; 4px;">:</td>
                        <td style="padding: 0px; 4px;">{{ $p->nama }}</td>
                    </tr>
                @endforeach
                {{-- <tr>
                    <td style="padding: 0px; 4px;">Unit Usaha</td>
                    <td style="padding: 0px; 4px;">:</td>
                    <td style="padding: 0px; 4px;">.................................</td>
                </tr> --}}
            </table>
        </li>
        <li style="margin-top: 12px;">
            <div style="text-transform: uppercase;">
                Ikhtisar Kebijakan Akutansi
            </div>
            <ol>
                <li>
                    Pernyataan Kepatuhan
                    <ol style="list-style: lower-alpha;">
                        <li>
                            Laporan keuangan disusun menggunakan Standar Akuntansi Keuangan ETAP dan/atau EP
                        </li>
                        <li>Dasar Penyusunan Kepmendesa 136 Tahun 2022</li>
                        <li>
                            Dasar penyusunan laporan keuangan adalah biaya historis dan menggunakan asumsi dasar akrual.
                            Mata uang penyajian yang digunakan untuk menyusun laporan keuangan ini adalah Rupiah.
                        </li>
                    </ol>
                </li>
                <li>
                    Piutang Usaha
                    <ol style="list-style: lower-alpha;">
                        <li>
                            Piutang usaha disajikan sebesar jumlah saldo pinjaman dikurangi
                            dengan cadangan kerugian pinjaman
                        </li>
                    </ol>
                </li>
                <li>
                    Aset Tetap (berwujud dan tidak berwujud)
                    <ol style="list-style: lower-alpha">
                        <li>
                            Aset tetap dicatat sebesar biaya perolehannya jika aset tersebut dimiliki secara hukum oleh
                            Bumdesma Lkd Aset tetap disusutkan menggunakan metode garis lurus tanpa nilai residu.
                        </li>
                    </ol>
                </li>
                <li>
                    Pengakuan Pendapatan dan Beban
                    <ol style="list-style: lower-alpha;">
                        <li>
                            Laba penjualan dan Jasa piutang yang sudah memasuki jatuh tempo pembayaran diakui sebagai
                            pendapatan meskipun tidak diterbitkan kuitansi sebagai bukti pembayaran jasa piutang. Sedangkan
                            denda keterlambatan pembayaran/pinalti diakui sebagai pendapatan pada saat diterbitkan kuitansi
                            pembayaran.
                        </li>
                        <li>
                            Adapun kewajiban bayar atas kebutuhan operasional, pemasaran
                            maupun non operasional pada suatu periode operasi tertentu sebagai akibat
                            telah menikmati manfaat/menerima fasilitas, maka hal tersebut sudah wajib diakui
                            sebagai beban meskipun belum diterbitkan kuitansi pembayaran.
                        </li>
                    </ol>
                </li>
                <li>
                    Pajak Penghasilan
                    <ol style="list-style: lower-alpha;">
                        <li>
                            Pajak Penghasilan mengikuti ketentuan perpajakan yang berlaku di Indonesia
                        </li>
                    </ol>
                </li>
            </ol>
        </li>
        <li style="margin-top: 12px;">
            <div style="text-transform: uppercase;">
                Informasi Tambahan Laporan Keuangan
            </div>
            <div>
                <table border="0" width="100%" cellspacing="0" cellpadding="0" style="font-size: 11px;">
                    <tr>
                        <td colspan="3" height="5"></td>
                    </tr>
                    <tr style="background: #000; color: #fff;">
                        <td width="30">Kode</td>
                        <td width="300">Nama Akun</td>
                        <td align="right">Saldo</td>
                    </tr>
                    <tr>
                        <td colspan="3" height="2"></td>
                    </tr>

                    @foreach ($akun1 as $lev1)
                        @php
                            $sum_akun1 = 0;
                        @endphp
                        <tr style="background: rgb(74, 74, 74); color: #fff;">
                            <td height="20" colspan="3" align="center">
                                <b>{{ $lev1->kode_akun }}. {{ $lev1->nama_akun }}</b>
                            </td>
                        </tr>
                        @foreach ($lev1->akun2 as $lev2)
                            <tr style="background: rgb(167, 167, 167); font-weight: bold;">
                                <td>{{ $lev2->kode_akun }}.</td>
                                <td colspan="2">{{ $lev2->nama_akun }}</td>
                            </tr>

                            @foreach ($lev2->akun3 as $lev3)
                                @php
                                    $sum_saldo = 0;
                                    $akun_lev4 = [];
                                @endphp

                                @foreach ($lev3->rek as $rek)
                                    @php
                                        $saldo = $keuangan->komSaldo($rek);
                                        if ($rek->kode_akun == '3.2.02.01' && ($bulan != '1' && $hari != '1')) {
                                            $saldo = $keuangan->laba_rugi($tgl_kondisi);
                                        }

                                        $sum_saldo += $saldo;
                                        $akun_lev4[] = [
                                            'kode_akun' => $rek->kode_akun,
                                            'nama_akun' => $rek->nama_akun,
                                            'saldo' => $saldo,
                                        ];
                                    @endphp
                                @endforeach

                                @php
                                    if ($lev1->lev1 == '1') {
                                        $debit += $sum_saldo;
                                    } else {
                                        $kredit += $sum_saldo;
                                    }

                                    $sum_akun1 += $sum_saldo;
                                @endphp

                                <tr style="background: rgb(200,200,200);">
                                    <td>{{ $lev3->kode_akun }}.</td>
                                    <td>{{ $lev3->nama_akun }}</td>
                                    @if ($sum_saldo < 0)
                                        <td align="right">({{ number_format($sum_saldo * -1, 2) }})</td>
                                    @else
                                        <td align="right">{{ number_format($sum_saldo, 2) }}</td>
                                    @endif
                                </tr>

                                @foreach ($akun_lev4 as $lev4)
                                    <tr style="background: rgb(255,255,255);">
                                        <td>{{ $lev4['kode_akun'] }}.</td>
                                        <td>{{ $lev4['nama_akun'] }}</td>
                                        @if ($lev4['saldo'] < 0)
                                            <td align="right">({{ number_format($lev4['saldo'] * -1, 2) }})</td>
                                        @else
                                            <td align="right">{{ number_format($lev4['saldo'], 2) }}</td>
                                        @endif
                                    </tr>
                                @endforeach
                            @endforeach
                        @endforeach

                        <tr style="background: rgb(167, 167, 167); font-weight: bold;">
                            <td height="20" colspan="2" align="left">
                                <b>Jumlah {{ $lev1->nama_akun }}</b>
                            </td>
                            <td align="right">{{ number_format($sum_akun1, 2) }}</td>
                        </tr>
                        <tr>
                            <td colspan="3" height="2"></td>
                        </tr>

                        @php
                            if ($lev1->lev1 == '1') {
                                $saldo_aset = $sum_akun1;
                            }
                        @endphp
                    @endforeach
                    <tr style="background: rgb(167, 167, 167); font-weight: bold;">
                        <td height="20" colspan="2" align="left">
                            <b>Jumlah Liabilitas + Ekuitas </b>
                        </td>
                        <td align="right">{{ number_format($kredit, 2) }}</td>
                    </tr>
                </table>
            </div>

            @php
                $saldo_aset = intval($saldo_aset);
                $kredit = intval($kredit);
            @endphp

            @if ($saldo_aset - $kredit != '0')
                <div style="color: #f44335">
                    Ada selisih antara Jumlah Aset dan Jumlah Liabilitas + Ekuitas sebesar
                    <b>Rp. {{ number_format($saldo_aset - $kredit, 2) }}</b>
                </div>
            @endif
        </li>
        <li style="margin-top: 12px;">
            <div style="text-transform: uppercase;">
                Ketentuan Pembagian Laba Usaha
            </div>
            <div style="text-align: justify">
                @if (Session::get('lokasi') == 4)
                    Pembagian laba {{ str_ireplace(['<br>', '<br/>', '<br />'], ' ', $usaha->nama_usaha) }}
                    dalam rapat/musyawarah desa/musyawarah kalurahan.
                    Adapun hasil keputusan pembagian laba tahun buku {{ $tahun }} adalah sebagai berikut:
                @else
                    Pembagian laba {{ $usaha->nama_usaha }} ditentukan dalam rapat pertanggungjawaban pengurus dan RUPS.
                    Adapun hasil keputusan pembagian laba tahun buku {{ $tahun }} adalah sebagai berikut:
                @endif
            </div>
            <ol>
                <li>
                    Total Laba bersih Rp. .....................
                </li>
                <li>
                    Alokasi penambahan modal {{ $usaha->nama_usaha }} Rp. .................
                </li>
                <li>
                    Alokasi PADes {{ $usaha->nama_usaha }} Rp. .................
                </li>
            </ol>
        </li>

        @if ($keterangan)
            <li style="margin-top: 12px;">
                <div style="text-transform: uppercase;">
                    Lain Lain
                </div>
                <div style="text-align: justify">
                    {!! $keterangan->catatan !!}.
                </div>
            </li>
        @endif

        <li style="margin-top: 12px;">
            <table border="0" width="100%" cellspacing="0" cellpadding="0" style="font-size: 11px;">
                <tr>
                    <td align="justify">
                        <div style="text-transform: uppercase;">
                            Penutup
                        </div>
                        <div style="text-align: justify">
                            Laporan Keuangan {{ $usaha->nama_usaha }} ini disajikan dengan berpedoman pada Keputusan
                            Kementerian
                            Desa Nomor 136/2022 Tentang Panduan Penyusunan Pelaporan Bumdes. Catatan atas Laporan Keuangan
                            (CaLK) ini merupakan bagian tidak terpisahkan dari Laporan Keuangan Badan Usaha Milik Desa
                            (Bumdes) Maju Jaya untuk Laporan Operasi Bulan {{ $nama_bulan }} Tahun {{ $tahun }}.
                            Selanjutnya Catatan atas Laporan Keuangan ini diharapkan untuk dapat berguna bagi pihak-pihak
                            yang berkepentingan (stakeholders) serta memenuhi prinsip-prinsip transparansi, akuntabilitas,
                            pertanggungjawaban, independensi, dan fairness dalam pengelolaan keuangan
                            {{ $usaha->nama_usaha }}.
                        </div>

                        <table border="0" width="100%" cellspacing="0" cellpadding="0" style="font-size: 11px;"
                            class="p">
                            <tr>
                                <td>
                                    <div style="margin-top: 16px;"></div>
                                    {!! json_decode(str_replace('{tanggal}', $tanggal_kondisi, $usaha->ttd->tanda_tangan_pelaporan), true) !!}
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </li>
    </ol>
@endsection
