@php
    use App\Utils\Tanggal;

    $jumlah_bulan = count($bulan_tampil);
    $is_rekap = !empty($rekap);

    $lebar_rekening = $jumlah_bulan > 3 ? '20%' : '32%';
    $lebar_komulatif = $jumlah_bulan > 3 ? '6%' : '10%';
    $lebar_bulan = $jumlah_bulan > 3 ? '5.5%' : '16%';
    $lebar_sub_bulan = $jumlah_bulan > 3 ? '2.75%' : '8%';
    $lebar_total = $jumlah_bulan > 3 ? '6%' : '10%';
    $font_size = $jumlah_bulan > 3 ? '7px' : '11px';
    $jumlah_kolom = 3 + ($jumlah_bulan * 2);

    $r_pendapatan = array_fill(0, $jumlah_bulan, 0);
    $pendapatan = array_fill(0, $jumlah_bulan, 0);
    $r_beban = array_fill(0, $jumlah_bulan, 0);
    $beban = array_fill(0, $jumlah_bulan, 0);

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
                    <b style="text-transform: uppercase;">
                        @if ($is_rekap)
                            Rekap {{ $tgl }}
                        @else
                            Triwulan
                            {{ $keuangan->romawi(str_pad($triwulan, '2', '0', STR_PAD_LEFT)) }} Tahun
                            Anggaran {{ $tahun }}
                        @endif
                    </b>
                </div>
            </td>
        </tr>
        <tr>
            <td colspan="3" height="5"></td>
        </tr>
    </table>

    <table border="0" width="100%" cellspacing="0" cellpadding="0" style="font-size: {{ $font_size }};">
        <tr style="background: rgb(232, 232, 232); font-weight: bold; font-size: {{ $jumlah_bulan > 3 ? '7px' : '12px' }};">
            <th rowspan="2" class="t l b" width="{{ $lebar_rekening }}">Rekening</th>
            <th rowspan="2" class="t l b" width="{{ $lebar_komulatif }}">Komulatif Bulan Lalu</th>
            @foreach ($bulan_tampil as $bt)
                <th colspan="2" class="t l b" width="{{ $lebar_bulan }}" height="16">
                    {{ Tanggal::namaBulan(date('Y') . '-' . $bt . '-01') }}
                </th>
            @endforeach
            <th rowspan="2" class="t l b r" width="{{ $lebar_total }}">Total</th>
        </tr>
        <tr style="background: rgb(232, 232, 232); font-weight: bold; font-size: {{ $jumlah_bulan > 3 ? '7px' : '12px' }};">
            @foreach ($bulan_tampil as $bt)
                <th class="t l b" width="{{ $lebar_sub_bulan }}" height="16">Rencana</th>
                <th class="t l b" width="{{ $lebar_sub_bulan }}">Realisasi</th>
            @endforeach
        </tr>

        @foreach ($akun1 as $lev1)
            @php
                $kom_saldo_lalu = 0;
                $rencana_total = array_fill(0, $jumlah_bulan, 0);
                $realisasi_total = array_fill(0, $jumlah_bulan, 0);
            @endphp
            <tr style="background: rgb(200, 200, 200); font-weight: bold;">
                <td colspan="{{ $jumlah_kolom }}" class="t l b r">
                    <b>{{ $lev1->kode_akun }}. {{ $lev1->nama_akun }}</b>
                </td>
            </tr>
            @foreach ($lev1->akun2 as $lev2)
                <tr style="background: rgb(150, 150, 150); font-weight: bold;">
                    <td colspan="{{ $jumlah_kolom }}" class="t l b r">
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

                            $komulatif = !$is_rekap && $triwulan > 1 ? $cumulative[(int) $bulan_akhir] : 0;
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
                            foreach ($bulan_tampil as $idx => $m) {
                                $rencana_total[$idx] += $rencana[$idx];
                                $realisasi_total[$idx] += $realisasi[$idx];

                                if ($is_beban) {
                                    $r_beban[$idx] += $rencana[$idx];
                                    $beban[$idx] += $realisasi[$idx];
                                } else {
                                    $r_pendapatan[$idx] += $rencana[$idx];
                                    $pendapatan[$idx] += $realisasi[$idx];
                                }
                            }

                            if ($is_beban) {
                                $komulatif_beban += $komulatif;
                            } else {
                                $komulatif_pendapatan += $komulatif;
                            }
                        @endphp
                        <tr style="background: {{ $bg }};">
                            <td class="t l b">{{ $rek->kode_akun }}. {{ $rek->nama_akun }}</td>
                            <td class="t l b" align="right">{{ number_format($komulatif, 2) }}</td>
                            @foreach ($bulan_tampil as $idx => $m)
                                <td class="t l b" align="right">{{ number_format($rencana[$idx], 2) }}</td>
                                <td class="t l b" align="right">{{ number_format($realisasi[$idx], 2) }}</td>
                            @endforeach
                            <td class="t l b r" align="right">{{ number_format($total, 2) }}</td>
                        </tr>
                    @endforeach
            @endforeach

            <tr style="background: rgb(150, 150, 150); font-weight: bold;">
                <td align="center" class="t l b" height="14">Total Realisasi {{ $lev1->nama_akun }}</td>
                <td align="right" class="t l b">{{ number_format($kom_saldo_lalu, 2) }}</td>
                @foreach ($bulan_tampil as $idx => $m)
                    <td align="right" class="t l b">{{ number_format($rencana_total[$idx], 2) }}</td>
                    <td align="right" class="t l b">{{ number_format($realisasi_total[$idx], 2) }}</td>
                @endforeach
                <td align="right" class="t l b r">
                    {{ number_format($kom_saldo_lalu + array_sum($realisasi_total), 2) }}
                </td>
            </tr>
        @endforeach

        <tr>
            <td colspan="{{ $jumlah_kolom }}" style="padding: 0px !important;">
                <table class="p" border="0" width="100%" cellspacing="0" cellpadding="0" style="font-size: {{ $jumlah_bulan > 3 ? '6px' : '9px' }};">
                    <tr style="background: rgb(232, 232, 232); font-weight: bold; font-size: {{ $jumlah_bulan > 3 ? '6px' : '10px' }};">
                        <th width="{{ $lebar_rekening }}" class="t l b" height="28">Surplus</th>
                        <th width="{{ $lebar_komulatif }}" class="t l b" align="right">
                            {{ number_format($komulatif_pendapatan - $komulatif_beban, 2) }}
                        </th>
                        @foreach ($bulan_tampil as $idx => $m)
                            <th width="{{ $lebar_sub_bulan }}" class="t l b" align="right">
                                {{ number_format($r_pendapatan[$idx] - $r_beban[$idx], 2) }}
                            </th>
                            <th width="{{ $lebar_sub_bulan }}" class="t l b" align="right">
                                {{ number_format($pendapatan[$idx] - $beban[$idx], 2) }}
                            </th>
                        @endforeach
                        <th width="{{ $lebar_total }}" class="t l b r" align="right">
                            {{ number_format($komulatif_pendapatan - $komulatif_beban + (array_sum($pendapatan) - array_sum($beban)), 2) }}
                        </th>
                    </tr>
                </table>

                <div style="margin-top: 16px;"></div>
                {!! json_decode(str_replace('{tanggal}', $tanggal_kondisi, $usaha->ttd->tanda_tangan_pelaporan), true) !!}
            </td>
        </tr>
    </table>
@endsection
