<?php

namespace App\Domains\Admin\Controllers;

use App\Domains\Admin\Models\Setting;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AdminSettingsController extends Controller
{
    // ── Settings CRUD ─────────────────────────────────────────────────────────

    public function index(): JsonResponse
    {
        try {
            $grouped = Setting::orderBy('group')->orderBy('sort_order')->get()
                ->groupBy('group')
                ->map(fn($items) => $items->map(fn($s) => [
                    'id'          => $s->id,
                    'key'         => $s->key,
                    'type'        => $s->type,
                    'value'       => $s->value,
                    'label'       => $s->label,
                    'description' => $s->description,
                    'is_readonly' => $s->is_readonly,
                    'sort_order'  => $s->sort_order,
                ])->values());

            return ApiResponse::success(['settings' => $grouped]);
        } catch (Exception $e) {
            return $this->handleError($e, 'Failed to load settings');
        }
    }

    public function update(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'settings'         => 'required|array|min:1',
                'settings.*.key'   => 'required|string|exists:settings,key',
                'settings.*.value' => 'nullable|string|max:1000',
            ]);

            $updated = 0;
            foreach ($data['settings'] as $item) {
                $rows = Setting::where('key', $item['key'])
                    ->where('is_readonly', false)
                    ->update(['value' => $item['value'] ?? '', 'updated_at' => now()]);
                $updated += $rows;
            }

            Setting::bustCache();

            return ApiResponse::success(
                ['updated_count' => $updated],
                "{$updated} setting(s) saved successfully"
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::error('Validation failed', $e->errors(), 422);
        } catch (Exception $e) {
            return $this->handleError($e, 'Failed to update settings');
        }
    }

    // ── System Health ─────────────────────────────────────────────────────────

    public function health(): JsonResponse
    {
        try {
            $checks = [];

            // 1. Database
            try {
                $start = microtime(true);
                DB::select('SELECT 1');
                $ms = round((microtime(true) - $start) * 1000, 2);
                $checks['database'] = [
                    'status'  => 'ok',
                    'driver'  => config('database.default'),
                    'latency' => "{$ms}ms",
                    'message' => "Connected · {$ms}ms",
                ];
            } catch (Exception $e) {
                $checks['database'] = ['status' => 'error', 'driver' => config('database.default'), 'message' => $e->getMessage()];
            }

            // 2. Cache
            try {
                $token = 'health_' . mt_rand(1000, 9999);
                Cache::put($token, 'ping', 5);
                $pong = Cache::get($token);
                Cache::forget($token);
                $ok = ($pong === 'ping');
                $checks['cache'] = [
                    'status'  => $ok ? 'ok' : 'warning',
                    'driver'  => config('cache.default'),
                    'message' => $ok ? 'Read/write OK' : 'Write succeeded but read failed',
                ];
            } catch (Exception $e) {
                $checks['cache'] = ['status' => 'error', 'driver' => config('cache.default'), 'message' => $e->getMessage()];
            }

            // 3. Queue
            $qDriver   = config('queue.default');
            $pending   = 0;
            $failed    = 0;
            try {
                $failed = (int) DB::table('failed_jobs')->count();
                if ($qDriver === 'database') {
                    $pending = (int) DB::table('jobs')->count();
                }
                $checks['queue'] = [
                    'status'       => $failed > 0 ? 'warning' : 'ok',
                    'driver'       => $qDriver,
                    'pending_jobs' => $pending,
                    'failed_jobs'  => $failed,
                    'message'      => $failed > 0
                        ? "{$failed} failed job(s) need attention"
                        : ($pending > 0 ? "{$pending} pending · queue healthy" : 'Queue healthy'),
                ];
            } catch (Exception $e) {
                $checks['queue'] = ['status' => 'warning', 'driver' => $qDriver, 'message' => 'Could not read queue tables'];
            }

            // 4. Storage
            try {
                $storagePath = storage_path('app');
                $writable    = is_writable($storagePath);
                $free        = @disk_free_space($storagePath);
                $total       = @disk_total_space($storagePath);
                $usedPct     = ($total && $total > 0)
                    ? round((($total - $free) / $total) * 100, 1)
                    : null;

                $checks['storage'] = [
                    'status'    => ! $writable ? 'error' : ($usedPct !== null && $usedPct > 90 ? 'warning' : 'ok'),
                    'writable'  => $writable,
                    'free_gb'   => $free  !== false ? round($free  / 1073741824, 2) : null,
                    'total_gb'  => $total !== false ? round($total / 1073741824, 2) : null,
                    'used_pct'  => $usedPct,
                    'message'   => ! $writable
                        ? 'Storage path not writable!'
                        : ($usedPct !== null ? "Disk {$usedPct}% used" : 'Writable'),
                ];
            } catch (Exception $e) {
                $checks['storage'] = ['status' => 'error', 'message' => $e->getMessage()];
            }

            // 5. Mail
            $mailer = config('mail.default', 'log');
            $checks['mail'] = [
                'status'  => $mailer === 'log' ? 'warning' : 'ok',
                'driver'  => $mailer,
                'host'    => config('mail.mailers.smtp.host'),
                'from'    => config('mail.from.address'),
                'message' => $mailer === 'log'
                    ? 'Using log driver — emails are not sent'
                    : "Driver: {$mailer}",
            ];

            // 6. App / Environment
            $isProduction = app()->environment('production');
            $debugOn      = (bool) config('app.debug');
            $inMaintenance= app()->isDownForMaintenance();
            $checks['app'] = [
                'status'      => ($isProduction && $debugOn) ? 'warning' : ($inMaintenance ? 'warning' : 'ok'),
                'environment' => app()->environment(),
                'debug'       => $debugOn,
                'maintenance' => $inMaintenance,
                'message'     => $inMaintenance
                    ? 'Maintenance mode is ACTIVE'
                    : (($isProduction && $debugOn) ? 'Debug mode ON in production!' : 'OK'),
            ];

            // 7. Scheduled tasks — check timestamp of last run via cache
            $lastSchedule = Cache::get('scheduler_last_run');
            $checks['scheduler'] = [
                'status'   => $lastSchedule ? 'ok' : 'info',
                'last_run' => $lastSchedule,
                'message'  => $lastSchedule
                    ? 'Last run: ' . \Carbon\Carbon::parse($lastSchedule)->diffForHumans()
                    : 'No recorded run (scheduler may not be set up)',
            ];

            // 8. Runtime info
            $checks['runtime'] = [
                'status'          => 'info',
                'php_version'     => PHP_VERSION,
                'laravel_version' => app()->version(),
                'timezone'        => config('app.timezone'),
                'php_sapi'        => PHP_SAPI,
            ];

            // Overall
            $statuses        = collect($checks)->pluck('status');
            $overallStatus   = $statuses->contains('error')   ? 'error'
                : ($statuses->contains('warning') ? 'warning' : 'ok');

            return ApiResponse::success([
                'checks'         => $checks,
                'overall_status' => $overallStatus,
                'checked_at'     => now()->toISOString(),
            ]);
        } catch (Exception $e) {
            return $this->handleError($e, 'Health check failed');
        }
    }

    // ── Actions ───────────────────────────────────────────────────────────────

    public function clearCache(): JsonResponse
    {
        try {
            Artisan::call('cache:clear');
            Artisan::call('view:clear');
            Artisan::call('route:clear');
            Setting::bustCache();

            return ApiResponse::success(null, 'Application cache cleared successfully');
        } catch (Exception $e) {
            return $this->handleError($e, 'Failed to clear cache');
        }
    }

    public function toggleMaintenance(Request $request): JsonResponse
    {
        try {
            if (app()->isDownForMaintenance()) {
                Artisan::call('up');
                return ApiResponse::success(['maintenance' => false], 'Site is now live');
            }

            // Build down options
            $options = ['--refresh' => 15];
            $secret  = Setting::get('maintenance_secret') ?: $request->input('secret');
            if ($secret) {
                $options['--secret'] = $secret;
            }

            Artisan::call('down', $options);

            return ApiResponse::success(['maintenance' => true], 'Maintenance mode enabled');
        } catch (Exception $e) {
            return $this->handleError($e, 'Failed to toggle maintenance mode');
        }
    }

    public function optimizeApp(): JsonResponse
    {
        try {
            Artisan::call('optimize');
            return ApiResponse::success(null, 'Application optimized (config + route + view cached)');
        } catch (Exception $e) {
            return $this->handleError($e, 'Optimization failed');
        }
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function handleError(Exception $e, string $msg, int $code = 500): JsonResponse
    {
        Log::error("{$msg}: {$e->getMessage()}", ['file' => $e->getFile(), 'line' => $e->getLine()]);

        return ApiResponse::error($msg, config('app.debug') ? $e->getMessage() : null, $code);
    }
}
