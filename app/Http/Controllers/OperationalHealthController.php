<?php

namespace App\Http\Controllers;

use App\Services\OperationalHealthService;
use Illuminate\Http\JsonResponse;

class OperationalHealthController extends Controller
{
    public function public(OperationalHealthService $health): JsonResponse
    {
        $status = $health->publicStatus();

        return response()->json($status, $status['status'] === 'ok' ? 200 : 503);
    }

    public function diagnosis(OperationalHealthService $health): JsonResponse
    {
        $status = $health->diagnosis();

        return response()->json($status, $status['status'] === 'ok' ? 200 : 503);
    }
}
