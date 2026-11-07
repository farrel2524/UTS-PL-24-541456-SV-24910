<?php

namespace App\Support;

use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use PDOException;
use Throwable;

/**
 * Shared helpers so every structured log entry answers the same three questions
 * (who / what / why) and never contains secrets or private data:
 * no passwords, tokens, raw e-mail addresses, request payloads or SQL bindings.
 */
final class LogContext
{
    /** Fragments of a driver error that mean "the database could not be reached". */
    private const UNREACHABLE = [
        '[2002]', '[2003]', '[2006]', '[2013]',
        'getaddrinfo', 'Connection refused', 'timed out', 'gone away', 'Lost connection',
    ];

    private const CREDENTIALS_REJECTED = ['[1045]', 'Access denied'];

    private const UNKNOWN_DATABASE = ['[1049]', 'Unknown database'];

    /**
     * WHO triggered the event.
     *
     * Never queries the database: a user is only reported when the request was
     * already authenticated (or when the caller passes the id explicitly).
     * An e-mail address is never logged as-is, only a short keyed hash that lets
     * you correlate repeated attempts without storing the address itself.
     */
    public static function who(?int $userId = null, ?string $email = null): array
    {
        $who = [
            'user_id' => $userId ?? (Auth::hasUser() ? Auth::id() : null),
            'ip' => request()->ip(),
        ];

        if ($email !== null) {
            $who['email_hash'] = substr(
                hash_hmac('sha256', Str::lower($email), (string) config('app.key')),
                0,
                12
            );
        }

        return $who;
    }

    /**
     * WHERE in the app the event happened, as a route template ("PUT /api/spddatas/{spddata}").
     * The template is used on purpose: concrete URLs can contain ids or tokens.
     */
    public static function where(): string
    {
        $route = request()->route();

        if (! $route instanceof Route) {
            return 'outside a routed request';
        }

        return request()->method().' /'.ltrim($route->uri(), '/');
    }

    /**
     * A short explanation of a failure that is safe to write to the log.
     * Uses the innermost (driver-level) message, which for Laravel's QueryException
     * is the part WITHOUT the SQL text and bound values, and masks 'user'@'host'.
     * Only use this for probes that run fixed queries such as `select 1`.
     */
    public static function safeMessage(Throwable $e): string
    {
        $message = preg_replace("/'[^']*'@'[^']*'/", "'[redacted]'@'[redacted]'", self::rootMessage($e));

        return Str::limit($message ?? 'message unavailable', 300);
    }

    /**
     * Classifies a database exception without copying its message, because a
     * QueryException message contains the SQL with its bound values (e.g. e-mails).
     */
    public static function databaseFailure(PDOException $e): array
    {
        $message = self::rootMessage($e);

        $category = match (true) {
            Str::contains($message, self::UNREACHABLE) => 'database_unreachable',
            Str::contains($message, self::CREDENTIALS_REJECTED) => 'database_credentials_rejected',
            Str::contains($message, self::UNKNOWN_DATABASE) => 'database_not_found',
            default => 'query_failed',
        };

        return [
            'category' => $category,
            'sqlstate' => preg_match('/SQLSTATE\[(\w+)\]/', $message, $found) === 1 ? $found[1] : null,
            'exception_class' => get_class($e),
        ];
    }

    private static function rootMessage(Throwable $e): string
    {
        while ($e->getPrevious() !== null) {
            $e = $e->getPrevious();
        }

        return $e->getMessage();
    }
}
