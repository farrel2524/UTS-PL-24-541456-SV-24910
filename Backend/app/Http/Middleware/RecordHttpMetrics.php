<?php

namespace App\Http\Middleware;

use App\Support\HttpMetrics;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class RecordHttpMetrics
{
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }

    public function terminate(Request $request, Response $response): void
    {
        if ($request->is('metrics', 'health', 'up')) {
            return;
        }

        try {
            HttpMetrics::record(
                $request->method(),
                $request->route()?->uri() ?? 'unmatched',
                $response->getStatusCode(),
            );
        } catch (Throwable) {
        }
    }
}
