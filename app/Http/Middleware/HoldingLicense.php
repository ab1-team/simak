<?php

namespace App\Http\Middleware;

use App\Models\License;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HoldingLicense
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->header('X-Holding-Token');
        $slug  = $request->header('X-Holding-Tenant');

        if (! $token || ! $slug) {
            return response()->json([
                'success' => false,
                'message' => 'Token tidak valid.',
            ], 401);
        }

        $license = License::with('usaha')
            ->where('api_secret', $token)
            ->where('is_active', 1)
            ->where(function ($q) {
                $q->whereNull('expired_at')->orWhere('expired_at', '>', now());
            })
            ->whereHas('usaha', function ($q) use ($slug) {
                $q->where('domain', $slug)->orWhere('domain_alt', $slug);
            })
            ->first();

        if (! $license) {
            return response()->json([
                'success' => false,
                'message' => 'Token tidak valid.',
            ], 401);
        }

        // Set tenant context untuk downstream (model & Keuangan baca session('lokasi'))
        session(['lokasi' => $license->usaha_id]);
        session(['jenis_akun' => $license->usaha->jenis_akun ?? null]);

        $request->attributes->set('holding_license', $license);
        $request->attributes->set('holding_usaha', $license->usaha);

        return $next($request);
    }

    public function terminate(Request $request, Response $response): void
    {
        // Bersihkan tenant context agar tidak bocor ke request lain
        session()->forget(['lokasi', 'jenis_akun']);
    }
}