<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Http\Exceptions\ThrottleRequestsException;

class CustomThrottleResponse
{
    public function handle(Request $request, Closure $next, $maxAttempts = 5, $decayMinutes = 1)
    {
        try {
            return app(ThrottleRequests::class)->handle($request, $next, $maxAttempts, $decayMinutes);
        } catch (ThrottleRequestsException $e) {
            return response()->json([
                'success' => false,
                'message' => 'لقد تجاوزت الحد المسموح من المحاولات، حاول مرة أخرى بعد دقيقة.',
                'data' => null
            ], 429);
        }
    }
}
