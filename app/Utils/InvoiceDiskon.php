<?php

namespace App\Utils;

class InvoiceDiskon
{
    /**
     * Hitung diskon perpanjangan dari total yang dibayar (jumlah) dan biaya per bulan.
     *
     * Aturan: setiap 11 bulan yang dibayar dianggap 1 tahun penuh (12 bulan)
     * dan mendapat diskon 1 bulan. Karena jumlah bulan tidak disimpan,
     * nilai dimundurkan dari jumlah / biaya.
     *
     * @param  mixed  $jumlah  Total tagihan yang sudah final (sudah setelah diskon)
     * @param  mixed  $biaya  Biaya per bulan
     * @return array{m: int, bulan_diskon: int, n: int, diskon: int, subtotal: int, total: int, berlaku: bool}
     */
    public static function hitung($jumlah, $biaya)
    {
        // Biaya disimpan sebagai varchar, jumlah sebagai int: parse ke string bulat
        // agar tidak terkena galat floating point (mis. 0.1 + 0.2).
        $total = self::keBulat($jumlah);
        $perBulan = self::keBulat($biaya);

        $dasar = [
            'm' => 0,
            'bulan_diskon' => 0,
            'n' => 0,
            'diskon' => 0,
            'subtotal' => $total,
            'total' => $total,
            'berlaku' => false,
        ];

        // Tanpa biaya per bulan yang valid, atau total bukan kelipatan biaya,
        // tidak ada hitungan bulan yang bisa dipercaya -> jangan tampilkan diskon.
        if ($perBulan <= 0 || $total <= 0) {
            return $dasar;
        }

        if ($total % $perBulan !== 0) {
            return $dasar;
        }

        $m = intdiv($total, $perBulan);
        $bulanDiskon = intdiv($m, 11);
        $n = $m + $bulanDiskon;

        return [
            'm' => $m,
            'bulan_diskon' => $bulanDiskon,
            'n' => $n,
            'diskon' => $bulanDiskon * $perBulan,
            'subtotal' => $total + ($bulanDiskon * $perBulan),
            'total' => $total,
            'berlaku' => true,
        ];
    }

    /**
     * Ubah nilai database (int / varchar / float) menjadi integer penuh tanpa
     * galat pembulatan floating point.
     */
    private static function keBulat($nilai): int
    {
        $nilai = str_replace(',', '', trim((string) $nilai));

        if ($nilai === '' || ! is_numeric($nilai)) {
            return 0;
        }

        return (int) round((float) $nilai);
    }
}
