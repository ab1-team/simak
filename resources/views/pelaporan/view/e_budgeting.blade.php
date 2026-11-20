@php
    use App\Utils\Tanggal;
    $r_pendapatan1 = 0;
    $r_pendapatan2 = 0;
    $r_pendapatan3 = 0;

    $pendapatan1 = 0;
    $pendapatan2 = 0;
    $pendapatan3 = 0;

    $r_beban1 = 0;
    $r_beban2 = 0;
    $r_beban3 = 0;

    $beban1 = 0;
    $beban2 = 0;
    $beban3 = 0;

    $komulatif_pendapatan = 0;
    $komulatif_beban = 0;
@endphp

@extends('pelaporan.layout.base')

@section('content')
    <style>
        html {
            margin-left: 40px;
            margin-right: 40px;
        }
    </style>

    <table border="0" width="100%" cellspacing="0" cellpadding="0" style="font-size: 10px;">
        <tr>
            <td colspan="3" align="center">
                <div style="font-size: 18px;">
                    <b>LAPORAN PENGGUNAAN DANA (E-BUDGETING)</b>
                </div>
                <div style="font-size: 16px;">
                    <b style="text-transform: uppercase;">Triwulan
                        {{ $keuangan->romawi(str_pad($triwulan, '2', '0', STR_PAD_LEFT)) }} Tahun
                        Anggaran {{ $tahun }}</b>
                </div>
            </td>
        </tr>
        <tr>
            <td colspan="3" height="5"></td>
        </tr>
    </table>

    <table border="0" width="100%" cellspacing="0" cellpadding="0" style="font-size: 11px;">
        <tr style="background: rgb(232, 232, 232); font-weight: bold; font-size: 12px;">
            <th rowspan="2" class="t l b" width="32%">Rekening</th>
            <th rowspan="2" class="t l b" width="10%">Komulatif Bulan Lalu</th>
            @foreach ($bulan_tampil as $bt)
                <th colspan="2" class="t l b" width="16%" height="16">
                    {{ Tanggal::namaBulan(date('Y') . '-' . $bt . '-01') }}
                </th>
            @endforeach
            <th rowspan="2" class="t l b r" width="10%">Total</th>
        </tr>
        <tr style="background: rgb(232, 232, 232); font-weight: bold; font-size: 12px;">
            <th class="t l b" width="8%" height="16">Rencana</th>
            <th class="t l b" width="8%">Realisasi</th>
            <th class="t l b" width="8%">Rencana</th>
            <th class="t l b" width="8%">Realisasi</th>
            <th class="t l b" width="8%">Rencana</th>
            <th class="t l b" width="8%">Realisasi</th>
        </tr>

        @foreach ($akun1 as $lev1)
            @php
                $kom_saldo_lalu = 0;

                $bulan1 = 0;
                $bulan2 = 0;
                $bulan3 = 0;

                $rencana1 = 0;
                $rencana2 = 0;
                $rencana3 = 0;
            @endphp
            <tr style="background: rgb(200, 200, 200); font-weight: bold;">
                <td colspan="9" class="t l b r">
                    <b>{{ $lev1->kode_akun }}. {{ $lev1->nama_akun }}</b>
                </td>
            </tr>
            @foreach ($lev1->akun2 as $lev2)
                <tr style="background: rgb(150, 150, 150); font-weight: bold;">
                    <td colspan="9" class="t l b r">
                        <b>{{ $lev2->kode_akun }}. {{ $lev2->nama_akun }}</b>
                    </td>
                </tr>
                @foreach ($lev2->rek as $rek)
                        @php
                            $bg = 'rgb(230, 230, 230)';
                            if ($loop->iteration % 2 == 0) {
                                $bg = 'rgba(255, 255, 255)';
                            }

                            $jenis_mutasi = strtolower((string) $rek->jenis_mutasi);
                            $is_beban = in_array($jenis_mutasi, ['debet', 'debit'], true);
                            if ($jenis_mutasi === '') {
                                $is_beban = in_array((string) $rek->lev1, ['5', '6'], true);
                            }

                            $saldo_by_month = $rek->kom_saldo->keyBy('bulan');
                            $eb_by_month = $rek->kom_eb->keyBy('bulan');

                            $cumulative = [];
                            $running = 0;
                            for ($m = 0; $m <= 12; $m++) {
                                if ($saldo_by_month->has($m)) {
                                    $saldo = $saldo_by_month->get($m);
                                    $running = $is_beban
                                        ? floatval($saldo->debit) - floatval($saldo->kredit)
                                        : floatval($saldo->kredit) - floatval($saldo->debit);
                                }
                                $cumulative[$m] = $running;
                            }

                            $komulatif = $triwulan > 1 ? $cumulative[(int) $bulan_akhir] : 0;
                            $rencana = [];
                            $realisasi = [];
                            foreach ($bulan_tampil as $idx => $m) {
                                $m = (int) $m;
                                $eb = $eb_by_month->get($m);
                                $rencana[$idx] = $eb ? floatval($eb->jumlah) : 0;
                                $realisasi[$idx] = $cumulative[$m] - ($cumulative[$m - 1] ?? 0);
                            }
                            $total = $komulatif + array_sum($realisasi);

                            $kom_saldo_lalu += $komulatif;
                            $rencana1 += $rencana[0];
                            $bulan1 += $realisasi[0];
                            $rencana2 += $rencana[1];
                            $bulan2 += $realisasi[1];
                            $rencana3 += $rencana[2];
                            $bulan3 += $realisasi[2];

                            if ($is_beban) {
                                $r_beban1 += $rencana[0];
                                $beban1 += $realisasi[0];
                                $r_beban2 += $rencana[1];
                                $beban2 += $realisasi[1];
                                $r_beban3 += $rencana[2];
                                $beban3 += $realisasi[2];
                                $komulatif_beban += $komulatif;
                            } else {
                                $r_pendapatan1 += $rencana[0];
                                $pendapatan1 += $realisasi[0];
                                $r_pendapatan2 += $rencana[1];
                                $pendapatan2 += $realisasi[1];
                                $r_pendapatan3 += $rencana[2];
                                $pendapatan3 += $realisasi[2];
                                $komulatif_pendapatan += $komulatif;
                            }
                        @endphp
                        <tr style="background: {{ $bg }};">
                            <td class="t l b">{{ $rek->kode_akun }}. {{ $rek->nama_akun }}</td>
                            <td class="t l b" align="right">{{ number_format($komulatif, 2) }}</td>
                            <td class="t l b" align="right">{{ number_format($rencana[0], 2) }}</td>
                            <td class="t l b" align="right">{{ number_format($realisasi[0], 2) }}</td>
                            <td class="t l b" align="right">{{ number_format($rencana[1], 2) }}</td>
                            <td class="t l b" align="right">{{ number_format($realisasi[1], 2) }}</td>
                            <td class="t l b" align="right">{{ number_format($rencana[2], 2) }}</td>
                            <td class="t l b" align="right">{{ number_format($realisasi[2], 2) }}</td>
                            <td class="t l b r" align="right">{{ number_format($total, 2) }}</td>
                        </tr>
                    @endforeach
            @endforeach

            <tr style="background: rgb(150, 150, 150); font-weight: bold;">
                <td align="center" class="t l b" height="14">Total Realisasi {{ $lev1->nama_akun }}</td>
                <td align="right" class="t l b">{{ number_format($kom_saldo_lalu, 2) }}</td>
                <td align="right" class="t l b">{{ number_format($rencana1, 2) }}</td>
                <td align="right" class="t l b">{{ number_format($bulan1, 2) }}</td>
                <td align="right" class="t l b">{{ number_format($rencana2, 2) }}</td>
                <td align="right" class="t l b">{{ number_format($bulan2, 2) }}</td>
                <td align="right" class="t l b">{{ number_format($rencana3, 2) }}</td>
                <td align="right" class="t l b">{{ number_format($bulan3, 2) }}</td>
                <td align="right" class="t l b r">
                    {{ number_format($kom_saldo_lalu + $bulan1 + $bulan2 + $bulan3, 2) }}
                </td>
            </tr>
        @endforeach

        @php
            $r_pendapatan = $r_pendapatan1 + $r_pendapatan2 + $r_pendapatan3;
            $r_beban = $r_beban1 + $r_beban2 + $r_beban3;

            $pendapatan = $pendapatan1 + $pendapatan2 + $pendapatan3;
            $beban = $beban1 + $beban2 + $beban3;
        @endphp

        <tr>
            <td colspan="9" style="padding: 0px !important;">
                <table class="p" border="0" width="100%" cellspacing="0" cellpadding="0" style="font-size: 9px;">
                    <tr style="background: rgb(232, 232, 232); font-weight: bold; font-size: 10px;">
                        <th width="32%" class="t l b" height="28">Surplus</th>
                        <th width="10%" class="t l b" align="right">
                            {{ number_format($komulatif_pendapatan - $komulatif_beban, 2) }}
                        </th>
                        <th width="8%" class="t l b" align="right">
                            {{ number_format($r_pendapatan1 - $r_beban1, 2) }}
                        </th>
                        <th width="8%" class="t l b" align="right">{{ number_format($pendapatan1 - $beban1, 2) }}
                        </th>
                        <th width="8%" class="t l b" align="right">
                            {{ number_format($r_pendapatan2 - $r_beban2, 2) }}
                        </th>
                        <th width="8%" class="t l b" align="right">{{ number_format($pendapatan2 - $beban2, 2) }}
                        </th>
                        <th width="8%" class="t l b" align="right">
                            {{ number_format($r_pendapatan3 - $r_beban3, 2) }}
                        </th>
                        <th width="8%" class="t l b" align="right">{{ number_format($pendapatan3 - $beban3, 2) }}
                        </th>
                        <th width="10%" class="t l b r" align="right">
                            {{ number_format($komulatif_pendapatan - $komulatif_beban + ($pendapatan - $beban), 2) }}
                        </th>
                    </tr>
                </table>

                <div style="margin-top: 16px;"></div>
                {!! json_decode(str_replace('{tanggal}', $tanggal_kondisi, $usaha->ttd->tanda_tangan_pelaporan), true) !!}
            </td>
        </tr>
    </table>
@endsection
