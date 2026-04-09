<?php

namespace App\Domains\Notification\Controllers;

use App\Domains\Notification\Requests\SendNotificationRequest;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\AdminBroadcastNotification;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class AdminNotificationController extends Controller
{
    // ── Stats ─────────────────────────────────────────────────────────────────

    public function stats(): JsonResponse
    {
        try {
            $totalNotifications = DB::table('notifications')
                ->where('notifiable_type', User::class)
                ->count();

            $unreadNotifications = DB::table('notifications')
                ->where('notifiable_type', User::class)
                ->whereNull('read_at')
                ->count();

            $totalFailed   = DB::table('failed_jobs')->count();
            $recentFailed  = DB::table('failed_jobs')
                ->where('failed_at', '>=', now()->subDay())
                ->count();

            $todaySent = DB::table('notifications')
                ->where('notifiable_type', User::class)
                ->where('created_at', '>=', now()->startOfDay())
                ->count();

            return ApiResponse::success([
                'total_notifications'  => $totalNotifications,
                'unread_notifications' => $unreadNotifications,
                'today_sent'           => $todaySent,
                'total_failed_jobs'    => $totalFailed,
                'recent_failed_jobs'   => $recentFailed,  // last 24h
            ]);
        } catch (Exception $e) {
            return $this->handleError($e, 'Failed to retrieve notification stats');
        }
    }

    // ── Sent Notifications ────────────────────────────────────────────────────

    public function index(Request $request): JsonResponse
    {
        try {
            $query = DB::table('notifications')
                ->where('notifiable_type', User::class)
                ->orderByDesc('created_at');

            if ($q = $request->input('q')) {
                $query->where('data', 'like', "%{$q}%");
            }

            if ($request->filled('read')) {
                $request->input('read') === '1'
                    ? $query->whereNotNull('read_at')
                    : $query->whereNull('read_at');
            }

            $notifications = $query->paginate(20);

            $items = collect($notifications->items())->map(function ($n) {
                $data = is_string($n->data)
                    ? (json_decode($n->data, true) ?? [])
                    : (array) $n->data;

                // Try to resolve the recipient name
                static $userCache = [];
                $recipientName = null;
                if (isset($userCache[$n->notifiable_id])) {
                    $recipientName = $userCache[$n->notifiable_id];
                } else {
                    $user = DB::table('users')->select('name', 'email')->find($n->notifiable_id);
                    $recipientName = $user ? "{$user->name} ({$user->email})" : "User #{$n->notifiable_id}";
                    $userCache[$n->notifiable_id] = $recipientName;
                }

                return [
                    'id'             => $n->id,
                    'type'           => class_basename($n->type),
                    'notifiable_id'  => $n->notifiable_id,
                    'recipient'      => $recipientName,
                    'subject'        => $data['subject'] ?? null,
                    'message'        => $data['message'] ?? null,
                    'channel'        => $data['channel'] ?? 'database',
                    'read_at'        => $n->read_at,
                    'created_at'     => $n->created_at,
                ];
            });

            return ApiResponse::success([
                'data' => $items,
                'meta' => [
                    'current_page' => $notifications->currentPage(),
                    'last_page'    => $notifications->lastPage(),
                    'per_page'     => $notifications->perPage(),
                    'total'        => $notifications->total(),
                ],
            ]);
        } catch (Exception $e) {
            return $this->handleError($e, 'Failed to retrieve notifications');
        }
    }

    // ── Failed Jobs ───────────────────────────────────────────────────────────

    public function failedJobs(Request $request): JsonResponse
    {
        try {
            $query = DB::table('failed_jobs')->orderByDesc('failed_at');

            if ($q = $request->input('q')) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('payload', 'like', "%{$q}%")
                        ->orWhere('exception', 'like', "%{$q}%")
                        ->orWhere('queue', 'like', "%{$q}%");
                });
            }

            if ($request->filled('queue')) {
                $query->where('queue', $request->input('queue'));
            }

            $jobs = $query->paginate(15);

            $items = collect($jobs->items())->map(function ($job) {
                $payload     = json_decode($job->payload, true) ?? [];
                $displayName = $payload['displayName']
                    ?? class_basename($payload['job'] ?? 'UnknownJob');

                // Shorten the exception to first 3 lines
                $exceptionLines = explode("\n", $job->exception ?? '');
                $shortException = implode("\n", array_slice($exceptionLines, 0, 3));

                return [
                    'id'              => $job->id,
                    'uuid'            => $job->uuid,
                    'connection'      => $job->connection,
                    'queue'           => $job->queue,
                    'display_name'    => $displayName,
                    'exception_short' => trim($shortException),
                    'exception_full'  => $job->exception,
                    'failed_at'       => $job->failed_at,
                ];
            });

            // Distinct queue names for filter dropdown
            $queues = DB::table('failed_jobs')
                ->distinct()
                ->pluck('queue')
                ->values();

            return ApiResponse::success([
                'data'   => $items,
                'queues' => $queues,
                'meta'   => [
                    'current_page' => $jobs->currentPage(),
                    'last_page'    => $jobs->lastPage(),
                    'per_page'     => $jobs->perPage(),
                    'total'        => $jobs->total(),
                ],
            ]);
        } catch (Exception $e) {
            return $this->handleError($e, 'Failed to retrieve failed jobs');
        }
    }

    public function retryJob(string $uuid): JsonResponse
    {
        try {
            $job = DB::table('failed_jobs')->where('uuid', $uuid)->first();

            if (! $job) {
                return ApiResponse::error('Failed job not found', null, 404);
            }

            Artisan::call('queue:retry', ['id' => [$uuid]]);

            return ApiResponse::success(null, 'Job queued for retry successfully');
        } catch (Exception $e) {
            return $this->handleError($e, 'Failed to retry job');
        }
    }

    public function deleteFailedJob(string $uuid): JsonResponse
    {
        try {
            $deleted = DB::table('failed_jobs')->where('uuid', $uuid)->delete();

            if (! $deleted) {
                return ApiResponse::error('Failed job not found', null, 404);
            }

            return ApiResponse::success(null, 'Failed job deleted');
        } catch (Exception $e) {
            return $this->handleError($e, 'Failed to delete failed job');
        }
    }

    public function retryAllFailed(): JsonResponse
    {
        try {
            $count = DB::table('failed_jobs')->count();

            if ($count === 0) {
                return ApiResponse::error('No failed jobs to retry', null, 422);
            }

            Artisan::call('queue:retry', ['id' => ['all']]);

            return ApiResponse::success(['retried' => $count], "All {$count} failed job(s) queued for retry");
        } catch (Exception $e) {
            return $this->handleError($e, 'Failed to retry all jobs');
        }
    }

    // ── Send Notification ─────────────────────────────────────────────────────

    public function send(SendNotificationRequest $request): JsonResponse
    {
        try {
            $data       = $request->validated();
            $recipients = $this->resolveRecipients($data['recipient_type'], $data['recipient_ids'] ?? []);

            if ($recipients->isEmpty()) {
                return ApiResponse::error('No active recipients found for the given criteria', null, 422);
            }

            Notification::send(
                $recipients,
                new AdminBroadcastNotification(
                    subject:  $data['subject'],
                    message:  $data['message'],
                    channels: $data['channels'],
                )
            );

            return ApiResponse::success(
                ['recipient_count' => $recipients->count()],
                "Notification dispatched to {$recipients->count()} recipient(s)"
            );
        } catch (Exception $e) {
            return $this->handleError($e, 'Failed to send notification');
        }
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Resolve recipient users based on type and optional IDs/roles.
     */
    private function resolveRecipients(string $type, array $ids): \Illuminate\Support\Collection
    {
        return match ($type) {
            'all'      => User::where('is_active', true)->get(),
            'role'     => User::role($ids)->where('is_active', true)->get(),
            'specific' => User::whereIn('id', $ids)->where('is_active', true)->get(),
            default    => collect(),
        };
    }

    private function handleError(Exception $e, string $msg, int $code = 500): JsonResponse
    {
        Log::error($msg . ': ' . $e->getMessage(), [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ]);

        return ApiResponse::error(
            $msg,
            config('app.debug') ? $e->getMessage() : null,
            $code
        );
    }
}
