@php
    use App\Utils\Tanggal;

    $periode = trim(str_replace($tahun, '', $tgl));

    $r_pendapatan_total = 0;
    $pendapatan_total = 0;
    $r_beban_total = 0;
    $beban_total = 0;
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
                    <b style="text-transform: uppercase;">{{ $periode }} Tahun Anggaran {{ $tahun }}</b>
                </div>
            </td>
        </tr>
        <tr>
            <td colspan="3" height="5"></td>
        </tr>
    </table>

    <table border="0" width="100%" cellspacing="0" cellpadding="0" style="font-size: 11px;">
        <tr style="background: rgb(232, 232, 232); font-weight: bold; font-size: 12px;">
            <th rowspan="2" class="t l b" width="60%">Rekening</th>
            <th colspan="2" class="t l b" height="16">Akumulasi {{ $periode }}</th>
        </tr>
        <tr style="background: rgb(232, 232, 232); font-weight: bold; font-size: 12px;">
            <th class="t l b" width="20%" height="16">Rencana</th>
            <th class="t l b" width="20%">Realisasi</th>
        </tr>

        @foreach ($akun1 as $lev1)
            @php
                $rencana_lev1 = 0;
                $realisasi_lev1 = 0;
            @endphp
            <tr style="background: rgb(200, 200, 200); font-weight: bold;">
                <td colspan="3" class="t l b r">
                    <b>{{ $lev1->kode_akun }}. {{ $lev1->nama_akun }}</b>
                </td>
            </tr>
            @foreach ($lev1->akun2 as $lev2)
                <tr style="background: rgb(150, 150, 150); font-weight: bold;">
                    <td colspan="3" class="t l b r">
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

                        $rencana = 0;
                        $realisasi = 0;
                        foreach ($bulan_tampil as $m) {
                            $m = (int) $m;
                            $eb = $eb_by_month->get($m);
                            $rencana += $eb ? floatval($eb->jumlah) : 0;
                            $realisasi += $cumulative[$m] - ($cumulative[$m - 1] ?? 0);
                        }

                        $rencana_lev1 += $rencana;
                        $realisasi_lev1 += $realisasi;

                        if ($is_beban) {
                            $r_beban_total += $rencana;
                            $beban_total += $realisasi;
                        } else {
                            $r_pendapatan_total += $rencana;
                            $pendapatan_total += $realisasi;
                        }
                    @endphp
                    <tr style="background: {{ $bg }};">
                        <td class="t l b">{{ $rek->kode_akun }}. {{ $rek->nama_akun }}</td>
                        <td class="t l b" align="right">{{ number_format($rencana, 2) }}</td>
                        <td class="t l b r" align="right">{{ number_format($realisasi, 2) }}</td>
                    </tr>
                @endforeach
            @endforeach

            <tr style="background: rgb(150, 150, 150); font-weight: bold;">
                <td align="center" class="t l b" height="14">Total Realisasi {{ $lev1->nama_akun }}</td>
                <td align="right" class="t l b">{{ number_format($rencana_lev1, 2) }}</td>
                <td align="right" class="t l b r">{{ number_format($realisasi_lev1, 2) }}</td>
            </tr>
        @endforeach

        <tr>
            <td colspan="3" style="padding: 0px !important;">
                <table class="p" border="0" width="100%" cellspacing="0" cellpadding="0" style="font-size: 9px;">
                    <tr style="background: rgb(232, 232, 232); font-weight: bold; font-size: 10px;">
                        <th width="60%" class="t l b" height="28">Surplus</th>
                        <th width="20%" class="t l b" align="right">
                            {{ number_format($r_pendapatan_total - $r_beban_total, 2) }}
                        </th>
                        <th width="20%" class="t l b r" align="right">
                            {{ number_format($pendapatan_total - $beban_total, 2) }}
                        </th>
                    </tr>
                </table>

                <div style="margin-top: 16px;"></div>
                {!! json_decode(str_replace('{tanggal}', $tanggal_kondisi, $usaha->ttd->tanda_tangan_pelaporan), true) !!}
            </td>
        </tr>
    </table>
@endsection
