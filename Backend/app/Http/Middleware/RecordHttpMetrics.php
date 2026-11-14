<?php

namespace App\Http\Middleware;

use App\Support\HttpMetrics;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Counts every handled request for the "requests per second" Grafana panel.
 *
 * Runs in terminate(), i.e. after the response is built, so it adds no latency
 * to the request itself. The route is recorded as its pattern
 * (api/spddatas/{spddata}) rather than the raw URL, which keeps the number of
 * label values small.
 */
class RecordHttpMetrics
{
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }

    public function terminate(Request $request, Response $response): void
    {
        // Prometheus scrapes and Docker healthchecks are not user traffic.
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
            // Metrics must never be able to break a real request.
        }
    }
}
