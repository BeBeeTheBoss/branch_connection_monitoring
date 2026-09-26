# NetPulse — Network IP Monitoring

Production-oriented Laravel 12, Vue 3, Inertia, Redis, and Tailwind application for server-side ICMP monitoring. It monitors IP addresses, not websites. Checks are dispatched in queue jobs and executed with Symfony Process argument arrays after strict IPv4/IPv6 validation. Targets never enter a shell command.

## Features

- Admin and monitoring-user roles, CSRF protection, Sanctum API auth, rate limiting, and audit logs
- IPv4/IPv6 hosts and groups with per-host interval, timeout, latency threshold, retry count, and enable switch
- Queue-based ICMP checks with locks, ACTIVE / UNSTABLE / DOWN / UNKNOWN state, latency, and packet loss
- Indexed history, range charts, history-derived uptime, and incident recovery lifecycle
- Encrypted Telegram configuration and extensible notification logs
- Responsive dashboard with 8-second partial polling, pagination, and scheduled retention

## Requirements

PHP 8.2+, Composer, Node 20+, MySQL 8 or PostgreSQL 15+, Redis, and an ICMP ping binary. The worker OS account must be allowed to send ICMP packets, normally through the packaged ping binary capability.

## Installation

    cp .env.example .env
    composer install
    npm install
    php artisan key:generate
    # configure DB and Redis in .env
    php artisan migrate --seed
    npm run build
    php artisan serve

In separate supervised processes:

    php artisan queue:work redis --queue=monitoring,default --tries=2 --timeout=40
    php artisan schedule:work

Demo accounts after seeding are admin@example.com and monitor@example.com, both with ChangeMe123!. Change them immediately. Override them with DEMO_ADMIN_PASSWORD and DEMO_MONITOR_PASSWORD while seeding.

For SQLite development, set DB_CONNECTION=sqlite and create database/database.sqlite. Production should use MySQL or PostgreSQL.

## Operations and scaling

The scheduler runs monitoring:dispatch every ten seconds. It scans enabled hosts in chunks, dispatches only due targets, and workers acquire a per-host cache lock. Each cycle executes all configured ping attempts. Partial responses or high latency are UNSTABLE; no replies after the attempts is DOWN.

Useful commands:

    php artisan monitoring:dispatch
    php artisan monitoring:prune --days=30
    php artisan queue:work redis --queue=monitoring --sleep=1 --tries=2 --timeout=40
    php artisan schedule:work

Scale by adding monitoring queue workers under Supervisor or systemd. Run one scheduler. Redis provides distributed locks, queueing, sessions, and cache.

## Telegram

As an administrator, open Settings, enter a bot token and chat ID, and enable Telegram. Credentials use Laravel encrypted casts. Keep APP_KEY stable and backed up. Failed sends are logged and do not crash the worker.

## API

Sanctum-authenticated and rate-limited endpoints under /api provide host CRUD, history, incidents, dashboard summary, and groups. Provision tokens only from a trusted administrative workflow.

## Production deployment

1. Serve public/ behind a TLS reverse proxy and deny access to environment and source files.
2. Set APP_ENV=production, APP_DEBUG=false, a unique APP_KEY, secure DB and Redis credentials, and secure cookies.
3. Run composer install --no-dev --optimize-autoloader, npm ci, npm run build, and php artisan migrate --force.
4. Cache configuration, routes, and views.
5. Supervise multiple queue workers plus one scheduler.
6. Configure backups, log rotation, /up health checks, failed-job alerts, and least-privilege network rules.
7. Confirm PING_BINARY and the minimum ICMP capability. Never run workers as root.

Release checks:

    php artisan test
    ./vendor/bin/pint --test
    npm run build

## Remote-agent extension

Hosts and normalized results are separated from execution. A future authenticated remote agent can claim due host IDs and submit the same result shape without changing dashboards, incidents, uptime, or notifications.
