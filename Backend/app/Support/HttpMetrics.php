<?php

namespace App\Support;

class HttpMetrics
{
    private const METRIC = 'http_requests_total';

    public static function path(): string
    {
        return storage_path('framework/cache/http_metrics.json');
    }

    public static function record(string $method, string $route, int $status): void
    {
        $handle = @fopen(self::path(), 'c+');

        if ($handle === false) {
            return;
        }

        try {
            if (! flock($handle, LOCK_EX)) {
                return;
            }

            $counts = json_decode((string) stream_get_contents($handle), true);
            $counts = is_array($counts) ? $counts : [];

            $key = json_encode([$method, $route, (string) $status]);
            $counts[$key] = ($counts[$key] ?? 0) + 1;

            ftruncate($handle, 0);
            rewind($handle);
            fwrite($handle, json_encode($counts));
            fflush($handle);
            flock($handle, LOCK_UN);
        } finally {
            fclose($handle);
        }
    }


    public static function render(): string
    {
        $counts = [];

        $handle = @fopen(self::path(), 'r');

        if ($handle !== false) {
            if (flock($handle, LOCK_SH)) {
                $decoded = json_decode((string) stream_get_contents($handle), true);
                $counts = is_array($decoded) ? $decoded : [];
                flock($handle, LOCK_UN);
            }

            fclose($handle);
        }

        ksort($counts);

        $lines = [
            '# HELP '.self::METRIC.' Total HTTP requests handled by the Laravel backend.',
            '# TYPE '.self::METRIC.' counter',
        ];

        foreach ($counts as $key => $value) {
            $labels = json_decode((string) $key, true);

            if (! is_array($labels) || count($labels) !== 3) {
                continue;
            }

            [$method, $route, $status] = $labels;

            $lines[] = sprintf(
                '%s{method="%s",route="%s",status="%s"} %d',
                self::METRIC,
                self::escape((string) $method),
                self::escape((string) $route),
                self::escape((string) $status),
                (int) $value,
            );
        }

        return implode("\n", $lines)."\n";
    }

    private static function escape(string $value): string
    {
        return str_replace(['\\', '"', "\n"], ['\\\\', '\\"', '\\n'], $value);
    }
}
