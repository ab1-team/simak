@extends('pelaporan.layout.base')
<title>{{ $title }}</title>
@section('content')
    <style>
        .t {
            border-top: 1px solid #000
        }

        .l {
            border-left: 1px solid #000
        }

        .b {
            border-bottom: 1px solid #000
        }

        .r {
            border-right: 1px solid #000
        }

        .bg {
            background: #e6e6e6;
            font-weight: bold
        }
    </style>
    @php
        function formatKurung($angka)
        {
            if ($angka < 0) {
                return '(' . number_format(abs($angka), 2, ',', '.') . ')';
            }
            return number_format($angka, 2, ',', '.');
        }
    @endphp
    <table width="100%" style="font-size:11px;">
        <tr>
            <td align="center">
                <b style="font-size:18px;">LABA RUGI</b><br>
                <b style="font-size:14px;">{{ strtoupper($sub_judul) }}</b>
            </td>
        </tr>
    </table>

    <br>

    <table width="100%" cellspacing="0" cellpadding="0" style="font-size:11px;">
        @php

            // PENJUALAN (E)

            $penjualan = collect($sections['penjualan']);
            $penjualan_map = $penjualan->keyBy('kode_akun');

            $penjualan_nilai = $penjualan_map['4.1.01.01']['saldo'] ?? 0; // A
            $diskon = $penjualan_map['4.1.01.02']['saldo'] ?? 0; // B
            $retur = $penjualan_map['4.1.01.03']['saldo'] ?? 0; // C
            $cashback = $penjualan_map['4.1.01.06']['saldo'] ?? 0; // D

            $total_penjualan = $penjualan_nilai - abs($diskon) - abs($retur) - abs($cashback); // E

            // DATA DARI CONTROLLER
            $g_pembelian = $pembelian_persediaan ?? 0; // G
            $h_potongan = $potongan_pembelian ?? 0; // H

            $persediaan_awal = $persediaan_awal ?? 0; // F
            $persediaan_akhir = $persediaan_akhir ?? 0; // K

            // RUMUS
            $total_pembelian = $g_pembelian - $h_potongan; // I = G - H
            $total_persediaan = $persediaan_awal + $total_pembelian; // J = F + I
            $hpp = $total_persediaan - $persediaan_akhir; // L = J - K
            $laba_kotor = $total_penjualan - $hpp; // M = E - L

            // hanya akun potongan (tanpa 5.1.01.01)
            $potongan_akun = collect($sections['pembelian'])->where('kode_akun', '!=', '5.1.01.01');

        @endphp
        @php
            $total_beban_operasional = collect($sections['beban_operasional'])->sum('saldo');
            $total_pendapatan_non_usaha = collect($sections['pendapatan_non_usaha'])->sum('saldo');
            $total_beban_non_usaha = collect($sections['beban_non_usaha'])->sum('saldo');
            $total_beban_pajak = collect($sections['beban_pajak'])->sum('saldo');

            // Laba sebelum pajak
            $laba_sebelum_pajak =
                $laba_kotor - $total_beban_operasional + $total_pendapatan_non_usaha - $total_beban_non_usaha;

            // Laba bersih
            $laba_bersih = $laba_sebelum_pajak - $total_beban_pajak;
        @endphp
        <tr class="bg">
            <td class="t l b r" colspan="3" align="center"><b>LABA KOTOR</b></td>
        </tr>

        {{-- PENJUALAN --}}
        @foreach ($penjualan as $acc)
            <tr>
                <td class="l b" width="8%">{{ $acc['kode_akun'] }}</td>
                <td class="l b r" width="60%">{{ $acc['nama'] }}</td>
                <td class="b r" width="18%" align="right">{{ formatKurung($acc['saldo']) }}</td>
            </tr>
        @endforeach

        <tr>
            <td class="l b"></td>
            <td class="l b r"><b>Penjualan Bersih</b></td>
            <td class="b r" align="right"><b>{{ formatKurung($total_penjualan) }}</b></td>
        </tr>

        {{-- PERSEDIAAN AWAL (F) --}}
        <tr>
            <td class="l b"></td>
            <td class="l b r">Persediaan Awal</td>
            <td class="b r" align="right">{{ formatKurung($persediaan_awal) }}</td>
        </tr>

        {{-- PEMBELIAN (G) --}}
        <tr>
            <td class="l b"></td>
            <td class="l b r">Pembelian</td>
            <td class="b r" align="right">{{ formatKurung($g_pembelian) }}</td>
        </tr>

        {{-- POTONGAN PEMBELIAN (H) --}}
        @foreach ($potongan_akun as $acc)
            <tr>
                <td class="l b"></td>
                <td class="l b r">{{ $acc['nama'] }}</td>
                <td class="b r" align="right">{{ formatKurung($acc['saldo']) }}</td>
            </tr>
        @endforeach

        {{-- <tr>
            <td class="l b"></td>
            <td class="l b r">Potongan Pembelian</td>
            <td class="b r" align="right">{{ formatKurung($h_potongan) }}</td>
        </tr> --}}

        {{-- TOTAL PEMBELIAN (I) --}}
        <tr>
            <td class="l b"></td>
            <td class="l b r"><b>Total Pembelian</b></td>
            <td class="b r" align="right"><b>{{ formatKurung($total_pembelian) }}</b></td>
        </tr>

        {{-- TOTAL PERSEDIAAN (J) --}}
        <tr>
            <td class="l b"></td>
            <td class="l b r"><b>Total Persediaan</b></td>
            <td class="b r" align="right"><b>{{ formatKurung($total_persediaan) }}</b></td>
        </tr>

        {{-- PERSEDIAAN AKHIR (K) --}}
        <tr>
            <td class="l b"></td>
            <td class="l b r">Persediaan Akhir</td>
            <td class="b r" align="right">{{ formatKurung($persediaan_akhir) }}</td>
        </tr>

        {{-- HPP (L) --}}
        <tr>
            <td class="l b"></td>
            <td class="l b r"><b>Harga Pokok Penjualan</b></td>
            <td class="b r" align="right"><b>{{ formatKurung($hpp) }}</b></td>
        </tr>

        {{-- LABA KOTOR (M) --}}
        <tr>
            <td class="l b"></td>
            <td class="l b r"><b>Laba Kotor</b></td>
            <td class="b r" align="right"><b>{{ formatKurung($laba_kotor) }}</b></td>
        </tr>

        {{-- SECTION LAIN --}}
        @foreach ([
            'pendapatan_lain' => 'PENDAPATAN LAIN-LAIN',
            'beban_operasional' => 'BEBAN OPERASIONAL',
            'pendapatan_non_usaha' => 'PENDAPATAN NON USAHA',
            'beban_non_usaha' => 'BEBAN NON USAHA',
            'beban_pajak' => 'BEBAN PAJAK',
        ] as $key => $label)
            @php $total = collect($sections[$key])->sum('saldo'); @endphp

            <tr class="bg">
                <td class="t l b r" colspan="3" align="center"><b>{{ $label }}</b></td>
            </tr>

            @foreach ($sections[$key] as $acc)
                <tr>
                    <td class="l b">{{ $acc['kode_akun'] }}</td>
                    <td class="l b r">{{ $acc['nama'] }}</td>
                    <td class="b r" align="right">{{ formatKurung($acc['saldo']) }}</td>
                </tr>
            @endforeach

            <tr>
                <td class="l b"></td>
                <td class="l b r"><b>Jumlah {{ $label }}</b></td>
                <td class="b r" align="right"><b>{{ formatKurung($total) }}</b></td>
            </tr>
        @endforeach
        <tr>
            <td class="l b"></td>
            <td class="l b r"><b>Laba Bersih</b></td>
            <td class="b r" align="right">
                <b>{{ formatKurung($laba_bersih) }}</b>
            </td>
        </tr>

    </table>
@endsection
