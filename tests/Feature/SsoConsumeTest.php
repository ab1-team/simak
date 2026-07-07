<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SsoConsumeTest extends TestCase
{
    use DatabaseTransactions;

    private string $secret = 'integration-test-secret-1234567890';

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.sso_secret' => $this->secret]);
    }

    /**
     * Issue a request with a specific Host. Laravel's prepareUrlForRequest
     * builds an absolute URL from config('app.url') and parse_url() then
     * overwrites SERVER_NAME/ HTTP_HOST in Symfony Request::create, so we
     * must pass an absolute URL ourselves to win.
     */
    private function getWithHost(string $uri, string $host)
    {
        return $this->get('http://'.$host.$uri);
    }

    private function sign(array $payload): string
    {
        $payloadB64 = rtrim(strtr(base64_encode(json_encode($payload)), '+/', '-_'), '=');
        $sig = hash_hmac('sha256', $payloadB64, $this->secret, true);
        $sigB64 = rtrim(strtr(base64_encode($sig), '+/', '-_'), '=');

        return $payloadB64.'.'.$sigB64;
    }

    /**
     * Create a Usaha row and return its id. Binds the row to a deterministic
     * test host so the controller's host→Usaha lookup can find it.
     */
    private function makeUsaha(string $host): int
    {
        $id = DB::table('usaha')->insertGetId([
            'kd_desa' => 'TEST',
            'nama_usaha' => 'Test BUMDes',
            'kepala_lembaga' => '-',
            'badan_pengawas' => '-',
            'kabag_administrasi' => '-',
            'kabag_keuangan' => '-',
            'bkk_bkm_bm' => '-',
            'npwp' => '-',
            'tgl_npwp' => '2026-01-01',
            'nomor_bh' => '-',
            'alamat' => '-',
            'email' => '-',
            'telpon' => '0',
            'domain' => $host,
            'domain_alt' => $host,
            'logo' => '',
            'background' => '',
            'tgl_register' => '2026-01-01',
            'tgl_pakai' => '2026-01-01',
            'biaya' => 0,
            'peraturan_desa' => '-',
            'jenis_akun' => '1',
            'masa_aktif' => '2027-01-01',
        ]);

        return $id;
    }

    /**
     * Create a local user at level=1, jabatan=1 for the given Usaha. Returns
     * the inserted User model.
     */
    private function makeAdminUser(int $usahaId, array $overrides = []): User
    {
        $id = DB::table('users')->insertGetId(array_merge([
            'namadepan' => 'Test',
            'namabelakang' => 'Admin',
            'uname' => substr('sa_'.uniqid(), 0, 20),
            // Legacy `pass` column is varchar(50) — bcrypt (60 chars) does not
            // fit. Use a short placeholder; we don't auth through this user.
            'pass' => str_repeat('x', 50),
            'status' => '1',
            'level' => 1,
            'jabatan' => 1,
            'lokasi' => $usahaId,
            'usaha' => $usahaId,
            'pendidikan' => 1,
        ], $overrides));

        return User::find($id);
    }

    private function attachLicense(int $usahaId): void
    {
        DB::table('licenses')->insert([
            'usaha_id' => $usahaId,
            'api_secret' => 'sso-test-secret-'.uniqid(),
            'is_active' => 1,
            'expired_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_missing_token_returns_400(): void
    {
        $this->get('/auth/sso')->assertStatus(400);
    }

    public function test_invalid_signature_returns_401(): void
    {
        $token = $this->sign([
            'uid' => 1, 'tid' => 1, 'lid' => 1,
            'slug' => 'demo', 'email' => 'x@y.test', 'role' => 'tenant_owner',
            'iat' => time(), 'exp' => time() + 300,
        ]);
        // Flip one byte of signature.
        $parts = explode('.', $token);
        $parts[1] = substr($parts[1], 0, -2).'AA';
        $bad = implode('.', $parts);

        $this->get('/auth/sso?token='.urlencode($bad))->assertStatus(401);
    }

    public function test_expired_token_returns_401(): void
    {
        $token = $this->sign([
            'uid' => 1, 'tid' => 1, 'lid' => 1,
            'slug' => 'demo', 'email' => 'x@y.test', 'role' => 'tenant_owner',
            'iat' => time() - 600, 'exp' => time() - 60,
        ]);

        $this->get('/auth/sso?token='.urlencode($token))->assertStatus(401);
    }

    public function test_unknown_host_returns_403(): void
    {
        $token = $this->sign([
            'uid' => 1, 'tid' => 1, 'lid' => 1,
            'slug' => 'demo', 'email' => 'x@y.test', 'role' => 'tenant_owner',
            'iat' => time(), 'exp' => time() + 300,
        ]);

        // Force a host that we know doesn't match any Usaha.
        $this->getWithHost('/auth/sso?token='.urlencode($token), 'no-such-bumdes.test')
            ->assertStatus(403);
    }

    public function test_inactive_user_returns_403(): void
    {
        $host = 'inactive.test';
        $usahaId = $this->makeUsaha($host);
        $this->makeAdminUser($usahaId, ['status' => '0']);

        $token = $this->sign([
            'uid' => 1, 'tid' => 1, 'lid' => 1,
            'slug' => 'demo', 'email' => 'inactive@y.test', 'role' => 'tenant_owner',
            'iat' => time(), 'exp' => time() + 300,
        ]);

        $this->getWithHost('/auth/sso?token='.urlencode($token), $host)
            ->assertStatus(403);
    }

    public function test_valid_token_redirects_to_dashboard(): void
    {
        $host = 'good.test';
        $usahaId = $this->makeUsaha($host);
        $user = $this->makeAdminUser($usahaId);
        $this->attachLicense($usahaId);

        $token = $this->sign([
            'uid' => 1, 'tid' => 1, 'lid' => 1,
            'slug' => 'demo', 'email' => 'good@y.test', 'role' => 'tenant_owner',
            'iat' => time(), 'exp' => time() + 300,
        ]);

        $response = $this->getWithHost('/auth/sso?token='.urlencode($token), $host);

        $this->assertContains(
            $response->getStatusCode(),
            [200, 302],
            "Expected redirect/dashboard, got {$response->getStatusCode()}"
        );

        $this->assertAuthenticatedAs($user);
    }
}