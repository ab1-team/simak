<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Throwable;

class HealthController extends Controller
{
    public function check(): JsonResponse
    {
        $checks = [
            'app' => 'ok',
            'database' => 'ok',
        ];

        try {
            DB::connection()->getPdo();
        } catch (Throwable $e) {
            $checks['database'] = 'error';
        }

        $healthy = ! in_array('error', $checks, true);

        return response()->json([
            'status' => $healthy ? 'ok' : 'error',
            'checks' => $checks,
        ], $healthy ? 200 : 503);
    }
}
