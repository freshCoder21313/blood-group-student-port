<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AspSyncController extends Controller
{
    /**
     * Ping endpoint to verify connectivity and auth.
     */
    public function ping(): JsonResponse
    {
        return response()->json(['message' => 'pong']);
    }
}
