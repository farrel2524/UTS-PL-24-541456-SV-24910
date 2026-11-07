<?php

namespace App\Http\Controllers;

use App\Support\LogContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class HealthController extends Controller
{

    public function __invoke(): JsonResponse
    {
        $checks = [
            'database' => $this->probe('database', 'cannot connect to or query the database', function () {
                DB::select('select 1');
            }),
            'cache' => $this->probe('cache', 'cannot write to or read from the cache store', function () {
                $key = 'health-probe:'.Str::random(16);
                $value = Str::random(16);

                Cache::put($key, $value, 10);
                $readBack = Cache::get($key);
                Cache::forget($key);

                if ($readBack !== $value) {
                    throw new RuntimeException('cache read-back did not match the value that was written');
                }
            }),
        ];

        $failing = array_keys(array_filter($checks, fn (array $check) => $check['status'] === 'down'));
        $healthy = $failing === [];

        return response()->json([
            'status' => $healthy ? 'healthy' : 'unhealthy',
            'failing' => $failing,
            'checks' => $checks,
            'timestamp' => now()->toIso8601String(),
        ], $healthy ? 200 : 503)->header('Cache-Control', 'no-store');
    }

    /**
     * Runs one check. A failure is logged as an error with its reason and
     * reported to the caller only as "down" for that component.
     *
     * @param  callable(): void  $check
     * @return array{status: string, error?: string}
     */
    private function probe(string $component, string $failureMessage, callable $check): array
    {
        try {
            $check();

            return ['status' => 'up'];
        } catch (Throwable $e) {
            Log::error('health.check.failed', [
                'who' => LogContext::who(),
                'what' => "health probe of the {$component} failed",
                'why' => LogContext::safeMessage($e),
                'component' => $component,
                'exception_class' => get_class($e),
            ]);

            return ['status' => 'down', 'error' => $failureMessage];
        }
    }
}
