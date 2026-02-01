<?php

namespace App\Http\Middleware;

use App\Models\AccessLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $request->user() || $request->ajax() || $request->method() !== 'GET') {
            return $response;
        }

        try {
            AccessLog::create([
                'user_id' => $request->user()->id,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'url' => $request->fullUrl(),
                'method' => $request->method(),
            ]);
        } catch (\Throwable $e) {
            report($e);
        }

        return $response;
    }
}
