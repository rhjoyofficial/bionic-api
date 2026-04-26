<?php

namespace App\Domains\ActivityLog\Services;

use App\Domains\ActivityLog\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AdminLogger
{
    /**
     * Log an admin activity.
     *
     * @param string $logName The category of the log (e.g., 'customers', 'orders', 'products').
     * @param string $description What happened (e.g., 'Customer created').
     * @param Model|null $subject The model being manipulated.
     * @param array $properties Additional context (old/new values, IP, etc.).
     * @param string|null $event Optional event name (e.g., 'created', 'updated').
     */
    public static function log(string $logName, string $description, ?Model $subject = null, array $properties = [], ?string $event = null): void
    {
        try {
            $user = Auth::user();

            // Auto-append IP address if not already provided and if request exists
            if (!isset($properties['ip']) && request()) {
                $properties['ip'] = request()->ip();
            }

            ActivityLog::query()->create([
                'log_name'     => $logName,
                'description'  => $description,
                'subject_type' => $subject ? get_class($subject) : null,
                'subject_id'   => $subject?->getKey(),
                'causer_type'  => $user ? get_class($user) : null,
                'causer_id'    => $user?->getAuthIdentifier(),
                'event'        => $event,
                'properties'   => $properties,
            ]);
        } catch (\Throwable $e) {
            Log::warning('Admin activity logging failed', [
                'log_name'    => $logName,
                'description' => $description,
                'error'       => $e->getMessage(),
            ]);
        }
    }
}
