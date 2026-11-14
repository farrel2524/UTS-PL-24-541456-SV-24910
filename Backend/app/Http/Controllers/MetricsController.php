<?php

namespace App\Http\Controllers;

use App\Support\HttpMetrics;
use Illuminate\Http\Response;

class MetricsController extends Controller
{
    public function __invoke(): Response
    {
        return response(HttpMetrics::render(), 200, [
            // Prometheus 3 rejects scrapes that do not declare a valid content type.
            'Content-Type' => 'text/plain; version=0.0.4; charset=utf-8',
            'Cache-Control' => 'no-store',
        ]);
    }
}
