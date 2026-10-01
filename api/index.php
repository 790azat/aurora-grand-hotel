<?php

/*
 * Vercel serverless entry point (vercel-php runtime).
 *
 * The filesystem is read-only except /tmp, so caches, compiled views and the
 * fallback SQLite database live there. When a Postgres URL is provided (e.g.
 * by the Neon integration) it is used instead, so data persists.
 */

$env = static function (string $key, string $value): void {
    if (getenv($key) === false) {
        putenv("{$key}={$value}");
        $_ENV[$key] = $_SERVER[$key] = $value;
    }
};

$env('APP_ENV', 'production');
$env('APP_DEBUG', 'false');
$env('APP_CONFIG_CACHE', '/tmp/config.php');
$env('APP_EVENTS_CACHE', '/tmp/events.php');
$env('APP_PACKAGES_CACHE', '/tmp/packages.php');
$env('APP_ROUTES_CACHE', '/tmp/routes.php');
$env('APP_SERVICES_CACHE', '/tmp/services.php');
$env('VIEW_COMPILED_PATH', '/tmp/views');
$env('CACHE_STORE', 'array');
$env('LOG_CHANNEL', 'stderr');
$env('MAIL_MAILER', 'log');
$env('QUEUE_CONNECTION', 'sync');
$env('DEMO_AUTO_SETUP', 'true');

if ($pg = getenv('POSTGRES_URL') ?: getenv('DATABASE_URL')) {
    $env('DB_CONNECTION', 'pgsql');
    $env('DB_URL', $pg);
    $env('SESSION_DRIVER', 'database');
} else {
    $env('DB_CONNECTION', 'sqlite');
    $env('DB_DATABASE', '/tmp/aurora.sqlite');
    $env('SESSION_DRIVER', 'cookie');
    // Start from the bundled pre-seeded snapshot (dates are shifted to today on boot).
    if (! file_exists('/tmp/aurora.sqlite')) {
        $snapshot = __DIR__.'/../database/demo.sqlite';
        file_exists($snapshot) ? copy($snapshot, '/tmp/aurora.sqlite') : touch('/tmp/aurora.sqlite');
    }
}

if (! is_dir('/tmp/views')) {
    @mkdir('/tmp/views', 0777, true);
}

require __DIR__.'/../public/index.php';
