<?php

namespace Tests\Unit;

use App\Utils\InvoiceDiskon;
use Tests\TestCase;

class InvoiceDiskonTest extends TestCase
{
    /**
     * m = jumlah / biaya. Setiap kelipatan 11 bulan dianggap 1 tahun penuh.
     *-data provider memformat: [jumlah, biaya, m, n, bulan_diskon]
     */
    public static function kasusBulanan(): array
    {
        return [
            'm=10 tanpa diskon' => [1500000, 150000, 10, 10, 0],
            'm=11 diskon 1' => [1650000, 150000, 11, 12, 1],
            'm=12 diskon 1' => [1800000, 150000, 12, 13, 1],
            'm=22 diskon 2' => [3300000, 150000, 22, 24, 2],
            'm=55 diskon 5' => [8250000, 150000, 55, 60, 5],
            'm=1 tanpa diskon' => [150000, 150000, 1, 1, 0],
        ];
    }

    /**
     * @dataProvider kasusBulanan
     */
    public function test_hitung_bulan_dan_diskon(int $jumlah, int $biaya, int $m, int $n, int $bulanDiskon): void
    {
        $hasil = InvoiceDiskon::hitung($jumlah, $biaya);

        $this->assertTrue($hasil['berlaku']);
        $this->assertSame($m, $hasil['m']);
        $this->assertSame($n, $hasil['n']);
        $this->assertSame($bulanDiskon, $hasil['bulan_diskon']);
        $this->assertSame($biaya * $bulanDiskon, $hasil['diskon']);
        $this->assertSame($jumlah, $hasil['total']);
        $this->assertSame($jumlah + ($biaya * $bulanDiskon), $hasil['subtotal']);
    }

    public function test_biaya_nol_tidak_menampilkan_diskon(): void
    {
        $hasil = InvoiceDiskon::hitung(1650000, 0);

        $this->assertFalse($hasil['berlaku']);
        $this->assertSame(0, $hasil['bulan_diskon']);
        $this->assertSame(1650000, $hasil['subtotal']);
        $this->assertSame(1650000, $hasil['total']);
    }

    public function test_biaya_kosong_tidak_menampilkan_diskon(): void
    {
        $hasil = InvoiceDiskon::hitung(1650000, null);

        $this->assertFalse($hasil['berlaku']);
        $this->assertSame(1650000, $hasil['subtotal']);
    }

    public function test_jumlah_bukan_kelipatan_biaya_tidak_menampilkan_diskon(): void
    {
        // 1.650.100 tidak habis dibagi 150.000 -> sisa 100
        $hasil = InvoiceDiskon::hitung(1650100, 150000);

        $this->assertFalse($hasil['berlaku']);
        $this->assertSame(0, $hasil['m']);
        $this->assertSame(0, $hasil['bulan_diskon']);
        $this->assertSame(1650100, $hasil['subtotal']);
        $this->assertSame(1650100, $hasil['total']);
    }

    public function test_biaya_bertipe_varchar_dan_nilai_desimal_aman(): void
    {
        // Kolom usaha.biaya bertipe varchar; pastikan tidak terkena galat float.
        $hasil = InvoiceDiskon::hitung(1650000, '150000.00');

        $this->assertTrue($hasil['berlaku']);
        $this->assertSame(11, $hasil['m']);
        $this->assertSame(1, $hasil['bulan_diskon']);
        $this->assertSame(150000, $hasil['diskon']);
        $this->assertSame(1800000, $hasil['subtotal']);
    }

    public function test_tidak_ada_galat_floating_point(): void
    {
        // 0.1 + 0.2 != 0.3 secara float; pembagian harus tetap presisi.
        $hasil = InvoiceDiskon::hitung(330000, 30000);

        $this->assertTrue($hasil['berlaku']);
        $this->assertSame(11, $hasil['m']);
        $this->assertSame(1, $hasil['bulan_diskon']);
        $this->assertSame(30000, $hasil['diskon']);
        $this->assertSame(360000, $hasil['subtotal']);
    }

    public function test_total_selalu_konsisten_dengan_jumlah(): void
    {
        foreach (self::kasusBulanan() as $label => [$jumlah, $biaya, $m, $n, $bulanDiskon]) {
            $hasil = InvoiceDiskon::hitung($jumlah, $biaya);

            $this->assertSame(
                $jumlah,
                $hasil['total'],
                "Total harus selalu sama dengan jumlah pada kasus: {$label}"
            );
            $this->assertSame(
                $hasil['subtotal'] - $hasil['diskon'],
                $hasil['total'],
                "Sub Total - Diskon harus sama dengan Total pada kasus: {$label}"
            );
        }
    }

    /**
     * Baris item di PDF menampilkan nilai penuh sebelum diskon (biaya x n)
     * dan harus menjumlah ke Sub Total.
     *
     * @dataProvider kasusBulanan
     */
    public function test_baris_item_sama_dengan_biaya_kali_bulan(int $jumlah, int $biaya, int $m, int $n, int $bulanDiskon): void
    {
        $hasil = InvoiceDiskon::hitung($jumlah, $biaya);

        $this->assertSame(
            $biaya * $n,
            $hasil['subtotal'],
            'Baris item harus sama dengan Sub Total'
        );
        $this->assertSame(
            $hasil['total'] + $hasil['diskon'],
            $hasil['subtotal'],
            'Baris item + Diskon harus sama dengan Sub Total'
        );
    }

    public function test_baris_item_sama_dengan_jumlah_saat_tanpa_diskon(): void
    {
        // Tidak berlaku -> item = jumlah, Sub Total = jumlah, tanpa diskon.
        foreach ([[1650100, 150000], [1650000, 0], [1650000, null]] as [$jumlah, $biaya]) {
            $hasil = InvoiceDiskon::hitung($jumlah, $biaya);

            $this->assertFalse($hasil['berlaku']);
            $this->assertSame($jumlah, $hasil['subtotal']);
            $this->assertSame(0, $hasil['diskon']);
            $this->assertSame($jumlah, $hasil['total']);
        }
    }

    public function test_nilai_kembalian_selalu_lengkap(): void
    {
        $hasil = InvoiceDiskon::hitung(1650000, 150000);

        foreach (['m', 'bulan_diskon', 'n', 'diskon', 'subtotal', 'total'] as $kunci) {
            $this->assertArrayHasKey($kunci, $hasil);
            $this->assertIsInt($hasil[$kunci]);
        }
        $this->assertIsBool($hasil['berlaku']);
    }
}
