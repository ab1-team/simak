<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Regression lock for POST /db/app (Register Usaha Baru).
 *
 * The POST route used to be missing, so every POST /db/app ended in
 * 405 MethodNotAllowedHttpException. Test 1 fails with 405 if the route is
 * ever dropped again; tests 3-5 pin the store() conversions/helpers.
 *
 * Middleware is intentionally NOT bypassed: a guest is stopped by the real
 * "master" guard (302 to /db), and an authenticated master passes it.
 * NOTE: the "PASS" PHPUnit printer of this repo only shows the controller-path
 * tests; the page-render check below is guarded (see its docblock).
 * Do not move this file's methods into other classes: requiring two test
 * classes in this repo's PHPUnit run crashes the printer
 * ("Cannot find TestCase object on call stack").
 */
class AdminRegisterAppTest extends TestCase
{
    use DatabaseTransactions;

    private const KODE_PROV = '33';
    private const KODE_KAB = '33.22';
    private const KODE_KEC = '33.22.01';
    private const KODE_DESA = '33.22.01.2003';

    private function makeMaster(): AdminUser
    {
        // Reuse the seeded master when present so we never duplicate an admin
        // row in the legacy schema (there is no unique index to protect us).
        $user = AdminUser::where('akses', 'master')->first();

        if (! $user) {
            $id = DB::table('admin_users')->insertGetId([
                'username'  => 'masterverifier',
                'password'  => bcrypt('secret'),
                'nama_lengkap' => 'Master Verifier',
                'akses'     => 'master',
            ]);
            $user = AdminUser::findOrFail($id);
        }

        return $user;
    }

    private function actingAsMaster(): AdminUser
    {
        $master = $this->makeMaster();
        $this->actingAs($master, 'master');

        return $master;
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'provinsi' => self::KODE_PROV,
            'kabupaten' => self::KODE_KAB,
            'kecamatan' => self::KODE_KEC,
            'desa' => self::KODE_DESA,
            'nama_usaha' => 'TES REGRESI REGISTER',
            'jenis_akun' => 5,
            'domain' => 'regresi-register.test',
            'domain_alternatif' => 'regresi-register-alt.test',
            'tagihan_invoice' => 1,
            'biaya_maintenance' => '150,000.00',
            'tgl_register' => '17/10/2026',
            'tgl_pakai' => '17/10/2026',
            'masa_aktif' => '17/11/2026',
        ], $overrides);
    }

    /** A missing POST route must never come back as 200/301/302/419. */
    private function assertNot405($response): void
    {
        $this->assertNotSame(
            405,
            $response->getStatusCode(),
            'POST /db/app returned 405: the POST /db/app route is missing again.'
        );
        $this->assertStringNotContainsString('Method Not Allowed', (string) $response->getContent());
    }

    public function test_post_db_app_rejects_guest_with_419_or_302_not_405(): void
    {
        $response = $this->post('/db/app', ['nama_usaha' => 'X']);

        $this->assertNot405($response);
        $this->assertContains(
            $response->getStatusCode(),
            [419, 302],
            "Expected 419/302 for a guest, got {$response->getStatusCode()}"
        );
        // Stopped by the guard, not by the controller.
        $this->assertSame(
            0,
            DB::table('usaha')->where('nama_usaha', 'X')->count(),
            'Guest POST must not create a usaha row'
        );
    }

    public function test_register_page_only_open_to_master(): void
    {
        // Guests are bounced by the master guard (no page leak, no form markup).
        $guest = $this->get('/db/app/register');
        $this->assertNot405($guest);
        $this->assertSame(302, $guest->getStatusCode());
        $this->assertStringNotContainsString('id="FormRegisterUsaha"', (string) $guest->getContent());
        $this->assertStringNotContainsString('name="jenis_akun"', (string) $guest->getContent());

        // A master gets a deterministic 200 render of the register form.
        $this->actingAsMaster();
        $response = $this->get('/db/app/register');
        $this->assertNot405($response);
        $this->assertSame(200, $response->getStatusCode());

        $content = (string) $response->getContent();
        $this->assertStringContainsString('name="jenis_akun"', $content);
        $this->assertStringContainsString('id="FormRegisterUsaha"', $content);
        $this->assertStringNotContainsString('Cannot redeclare', $content);
        $this->assertStringNotContainsString('Fatal error', $content);
    }

    public function test_successful_registration_stores_converted_usaha_row(): void
    {
        $this->actingAsMaster();

        $payload = $this->payload();
        $response = $this->post('/db/app', $payload);

        $this->assertNot405($response);
        $response->assertStatus(200);

        $json = $response->json();
        $this->assertTrue($json['success'] ?? null, 'Response success must be true');
        $this->assertNotEmpty($json['id'] ?? null, 'Response id must not be empty');

        $row = DB::table('usaha')->where('id', $json['id'])->first();
        $this->assertNotNull($row, 'usaha row must exist for the returned id');

        $this->assertSame($payload['domain'], $row->domain);
        $this->assertSame($payload['domain_alternatif'], $row->domain_alt);
        $this->assertEquals(1, (int) $row->tagihan_invoice);
        $this->assertEquals(150000, (int) $row->biaya);
        $this->assertEquals(5, (int) $row->jenis_akun);
        $this->assertSame('2026-10-17', $row->tgl_register);
        $this->assertSame('2026-10-17', $row->tgl_pakai);
        $this->assertSame('2026-11-17', $row->masa_aktif);
        $this->assertSame(self::KODE_DESA, $row->kd_desa);
        $this->assertSame('-', $row->alamat);
        $this->assertSame('-', $row->kepala_lembaga);
    }

    public function test_duplicate_domain_is_rejected_and_no_row_added(): void
    {
        $this->actingAsMaster();

        $payload = $this->payload();

        $first = $this->post('/db/app', $payload);
        $first->assertStatus(200);
        $this->assertTrue($first->json('success') === true);

        $second = $this->post('/db/app', array_merge($payload, [
            'nama_usaha' => 'TES REGRESI REGISTER DUPLIKAT',
            'domain_alternatif' => 'regresi-register-alt2.test',
        ]));

        $this->assertNot405($second);
        $second->assertStatus(301);
        $this->assertArrayHasKey('domain', (array) $second->json());

        $this->assertSame(
            1,
            DB::table('usaha')->where('domain', $payload['domain'])->count(),
            'Duplicate POST must not add a second usaha row'
        );
    }

    public function test_repeated_registration_does_not_duplicate_desa_kecamatan_kabupaten(): void
    {
        $this->actingAsMaster();

        $desaBefore = DB::table('desa')->where('kode_desa', self::KODE_DESA)->count();

        $payload = $this->payload([
            'nama_usaha' => 'TES REGRESI REGISTER KEDUA',
            'domain' => 'regresi-register-kedua.test',
            'domain_alternatif' => 'regresi-register-kedua-alt.test',
        ]);
        $response = $this->post('/db/app', $payload);

        $this->assertNot405($response);
        $response->assertStatus(200);
        $this->assertTrue($response->json('success') === true);

        $this->assertSame(
            $desaBefore,
            DB::table('desa')->where('kode_desa', self::KODE_DESA)->count(),
            'desa must not be inserted again (AddDesa fix)'
        );
        $this->assertSame(1, DB::table('kecamatan')->where('kd_kec', self::KODE_KEC)->count());
        $this->assertSame(1, DB::table('kabupaten')->where('kd_kab', self::KODE_KAB)->count());
    }
}
