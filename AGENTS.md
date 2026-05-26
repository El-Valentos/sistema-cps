# AGENTS.md — Sistema Integral de Pago CPS

## Stack
- Laravel 12, PHP 8.2+, MySQL 8.0+ (prod), SQLite `:memory:` (tests)
- Vite + Tailwind CSS 3 (custom green `primary` palette) + Alpine.js + Chart.js
- spatie/laravel-permission (8 roles), barryvdh/laravel-dompdf (PDF)

## Commands
- `composer setup` — full install (deps, migrate, build)
- `composer dev` — concurrent: server, queue:listen, pail, vite
- `composer test` — runs `artisan config:clear` then `artisan test`
- `php artisan test --filter=TestName` — single test
- `php artisan optimize:clear` — clear all cache
- `php artisan migrate:fresh --seed` — reset + seed DB

## Roles & workflow
8 roles: Super Admin, Tesorería, Financiera, Contabilidad, Presupuesto, Administración, Caja, Archivos.
Super Admin bypasses all role checks (custom `RoleMiddleware`).

State flow (`ordenes_pago.estado`):
Tesorería → Financiera → Contabilidad → (Archivos) → Presupuesto → Financiera (cheque) → Administración → Caja → Entregado → Cerrado

## Test users
| Email | Password | Role |
|-------|----------|------|
| admin@cps.bo | Admin1234! | Super Admin |
| tesoreria@cps.bo | Tesorer1a! | Tesorería |
| contabilidad@cps.bo | Contab1l! | Contabilidad |
| caja@cps.bo | Caja123! | Caja |
| financiera@cps.bo | Financ13! | Financiera |
| presupuesto@cps.bo | Presup12! | Presupuesto |
| administracion@cps.bo | Admin123! | Administración |
| archivos@cps.bo | Archiv12! | Archivos |

## Key env defaults (`.env.example`)
- `APP_CIUDAD=Cochabamba`, `FILESYSTEM_DISK=public`, `QUEUE_CONNECTION=sync`
- DB `cps_pagos` on MySQL 3306, root with no password

## Architecture notes
- OrdenPago auto-numbers as `OP-YYYY-XXXXX` via `creating` boot event
- Cheque model references `orden_pago_id` (belongsTo), User has `activo` boolean + `area_id`
- Routes use spatie's `role` middleware alias (not the unused `app/Http/Middleware/RoleMiddleware.php`)
- PDF via `PDFGeneratorService.php` (dompdf), tracking via `TrackingService.php`, workflows via `WorkflowOrchestratorService.php`
- Test users seeded in `DatabaseSeeder.php`; no external services required for tests
- `composer.json` scripts are the source of truth for dev/test commands
