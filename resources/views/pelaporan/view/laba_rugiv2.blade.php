extends('pelaporan.layout.base')
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
        if (!function_exists('formatKurung')) {
            function formatKurung($angka)
            {
                if ($angka < 0) {
                    return '(' . number_format(abs($angka), 2, ',', '.') . ')';
                }
                return number_format($angka, 2, ',', '.');
            }
        }

        $penjualan = collect($sections['penjualan'] ?? []);
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
            <td class="b r" align="right"><b>{{ formatKurung($penjualan_bersih ?? 0) }}</b></td>
        </tr>

        {{-- PERSEDIAAN AWAL --}}
        <tr>
            <td class="l b"></td>
            <td class="l b r">Persediaan Awal</td>
            <td class="b r" align="right">{{ formatKurung($persediaan_awal ?? 0) }}</td>
        </tr>

        {{-- PEMBELIAN --}}
        <tr>
            <td class="l b"></td>
            <td class="l b r">Pembelian</td>
            <td class="b r" align="right">{{ formatKurung($pembelian_persediaan ?? 0) }}</td>
        </tr>

        {{-- DISKON PEMBELIAN --}}
        <tr>
            <td class="l b"></td>
            <td class="l b r">Diskon Pembelian</td>
            <td class="b r" align="right">{{ formatKurung($diskon_pembelian ?? 0) }}</td>
        </tr>

        {{-- RETUR PEMBELIAN --}}
        <tr>
            <td class="l b"></td>
            <td class="l b r">Retur Pembelian</td>
            <td class="b r" align="right">{{ formatKurung($retur_pembelian ?? 0) }}</td>
        </tr>

        {{-- BEBAN PRODUKSI --}}
        <tr>
            <td class="l b"></td>
            <td class="l b r">Beban Produksi</td>
            <td class="b r" align="right">{{ formatKurung($beban_produksi ?? 0) }}</td>
        </tr>

        {{-- BEBAN TRANSPORT PRODUK --}}
        <tr>
            <td class="l b"></td>
            <td class="l b r">Beban Transport Produk</td>
            <td class="b r" align="right">{{ formatKurung($beban_transport ?? 0) }}</td>
        </tr>

        {{-- CASHBACK PEMBELIAN --}}
        <tr>
            <td class="l b"></td>
            <td class="l b r">Cashback Pembelian</td>
            <td class="b r" align="right">{{ formatKurung($cashback_pembelian ?? 0) }}</td>
        </tr>

        {{-- TOTAL PEMBELIAN --}}
        <tr>
            <td class="l b"></td>
            <td class="l b r"><b>Total Pembelian</b></td>
            <td class="b r" align="right"><b>{{ formatKurung($total_pembelian ?? 0) }}</b></td>
        </tr>

        {{-- TOTAL PERSEDIAAN --}}
        <tr>
            <td class="l b"></td>
            <td class="l b r"><b>Total Persediaan</b></td>
            <td class="b r" align="right"><b>{{ formatKurung($total_persediaan ?? 0) }}</b></td>
        </tr>

        {{-- PERSEDIAAN AKHIR --}}
        <tr>
            <td class="l b"></td>
            <td class="l b r">Persediaan Akhir</td>
            <td class="b r" align="right">{{ formatKurung($persediaan_akhir ?? 0) }}</td>
        </tr>

        {{-- HPP --}}
        <tr>
            <td class="l b"></td>
            <td class="l b r"><b>Harga Pokok Penjualan</b></td>
            <td class="b r" align="right"><b>{{ formatKurung($hpp ?? 0) }}</b></td>
        </tr>

        {{-- LABA KOTOR --}}
        <tr>
            <td class="l b"></td>
            <td class="l b r"><b>Laba Kotor</b></td>
            <td class="b r" align="right"><b>{{ formatKurung($laba_kotor ?? 0) }}</b></td>
        </tr>

        {{-- SECTION LAIN --}}
        @foreach ([
            'pendapatan_lain' => 'PENDAPATAN LAIN-LAIN',
            'beban_operasional' => 'BEBAN OPERASIONAL',
            'pendapatan_non_usaha' => 'PENDAPATAN NON USAHA',
            'beban_non_usaha' => 'BEBAN NON USAHA',
            'beban_pajak' => 'BEBAN PAJAK',
        ] as $key => $label)
            @php
                $items = $sections[$key] ?? [];
                $total = collect($items)->sum('saldo');
            @endphp

            <tr class="bg">
                <td class="t l b r" colspan="3" align="center"><b>{{ $label }}</b></td>
            </tr>

            @foreach ($items as $acc)
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

        @if (!empty($total_beban_pajak) && $total_beban_pajak != 0)
            <tr>
                <td class="l b"></td>
                <td class="l b r"><b>Laba Sebelum Pajak</b></td>
                <td class="b r" align="right">
                    <b>{{ formatKurung($laba_sebelum_pajak ?? 0) }}</b>
                </td>
            </tr>
        @endif

        <tr>
            <td class="l b"></td>
            <td class="l b r"><b>Laba Bersih</b></td>
            <td class="b r" align="right">
                <b>{{ formatKurung($laba_bersih ?? 0) }}</b>
            </td>
        </tr>

        @if (!empty($usaha->ttd->tanda_tangan_pelaporan))
            <tr>
                <td colspan="3" style="padding: 0px !important;">
                    <div style="margin-top: 16px;"></div>
                    {!! json_decode(str_replace('{tanggal}', $tanggal_kondisi ?? '', $usaha->ttd->tanda_tangan_pelaporan), true) !!}
                </td>
            </tr>
        @endif

    </table>
@endsection
