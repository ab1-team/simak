<?php

namespace Tests\Unit;

use App\Services\SsoTokenVerifier;
use Tests\TestCase;

class SsoTokenVerifierTest extends TestCase
{
    private SsoTokenVerifier $verifier;

    protected function setUp(): void
    {
        parent::setUp();
        // Re-bind the app.sso_secret used for testing. Production uses env();
        // here we override via config() so tests don't depend on .env state.
        config(['app.sso_secret' => 'unit-test-secret-do-not-use-in-prod-12345']);
        $this->verifier = new SsoTokenVerifier();
    }

    /**
     * Build a token using the SAME algorithm as the verifier, so we can
     * isolate verifier behavior from the signer.
     */
    private function sign(array $payload): string
    {
        $payloadB64 = rtrim(strtr(base64_encode(json_encode($payload)), '+/', '-_'), '=');
        $sig = hash_hmac('sha256', $payloadB64, config('app.sso_secret'), true);
        $sigB64 = rtrim(strtr(base64_encode($sig), '+/', '-_'), '=');

        return $payloadB64.'.'.$sigB64;
    }

    public function test_valid_token_is_accepted(): void
    {
        $token = $this->sign([
            'uid' => 1, 'tid' => 1, 'lid' => 1,
            'slug' => 'demo', 'email' => 'x@y.test', 'role' => 'tenant_owner',
            'iat' => time(), 'exp' => time() + 300,
            'nonce' => 'abc',
        ]);

        $decoded = $this->verifier->decode($token);

        $this->assertIsArray($decoded);
        $this->assertSame('x@y.test', $decoded['email']);
        $this->assertSame(1, $decoded['lid']);
    }

    public function test_expired_token_is_rejected(): void
    {
        $token = $this->sign([
            'uid' => 1, 'tid' => 1, 'lid' => 1,
            'slug' => 'demo', 'email' => 'x@y.test', 'role' => 'tenant_owner',
            'iat' => time() - 600, 'exp' => time() - 60,
            'nonce' => 'abc',
        ]);

        $this->assertNull($this->verifier->decode($token));
    }

    public function test_tampered_payload_is_rejected(): void
    {
        $token = $this->sign([
            'uid' => 1, 'tid' => 1, 'lid' => 1,
            'slug' => 'demo', 'email' => 'x@y.test', 'role' => 'tenant_owner',
            'iat' => time(), 'exp' => time() + 300,
            'nonce' => 'abc',
        ]);

        // Flip one character in payload (preserving shape).
        $tampered = preg_replace('/^./', 'X', $token);

        $this->assertNull($this->verifier->decode($tampered));
    }

    public function test_wrong_secret_is_rejected(): void
    {
        config(['app.sso_secret' => 'real-secret-1234567890']);
        $token = $this->sign([
            'uid' => 1, 'tid' => 1, 'lid' => 1,
            'slug' => 'demo', 'email' => 'x@y.test', 'role' => 'tenant_owner',
            'iat' => time(), 'exp' => time() + 300,
        ]);

        // Verifier runs under a DIFFERENT secret.
        config(['app.sso_secret' => 'attacker-secret-0987654321']);

        $this->assertNull($this->verifier->decode($token));
    }

    public function test_missing_required_field_is_rejected(): void
    {
        $token = $this->sign([
            'uid' => 1, 'tid' => 1, 'lid' => 1,
            'slug' => 'demo', 'email' => 'x@y.test', // no 'role'
            'iat' => time(), 'exp' => time() + 300,
        ]);

        $this->assertNull($this->verifier->decode($token));
    }

    public function test_malformed_token_is_rejected(): void
    {
        $this->assertNull($this->verifier->decode(''));
        $this->assertNull($this->verifier->decode('only-one-part'));
        $this->assertNull($this->verifier->decode('a.b.c'));
        $this->assertNull($this->verifier->decode('!!!not-base64!!!.!!!not-base64!!!'));
    }
}