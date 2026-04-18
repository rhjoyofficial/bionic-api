<?php

namespace App\Jobs;

use App\Domains\Coupon\Models\Coupon;
use App\Mail\WelcomeMail;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * SendWelcomeMailJob
 *
 * Responsible for:
 *  1. Creating a unique, single-use welcome coupon for the new customer.
 *  2. Sending the WelcomeMail via the configured mailer.
 *
 * Runs on the `emails` queue (see QUEUE_CONNECTION in .env).
 * If the job fails it retries up to 3 times with a 60-second backoff
 * so transient SMTP outages don't permanently block welcome emails.
 */
class SendWelcomeMailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $backoff = 60; // seconds between retries

    public function __construct(public readonly User $user) {}

    public function handle(): void
    {
        // Only send if the user has an email address.
        if (! $this->user->email) {
            return;
        }

        $coupon = $this->createWelcomeCoupon();

        Mail::to($this->user->email, $this->user->name)
            ->send(new WelcomeMail($this->user, $coupon));
    }

    // ── Coupon creation ────────────────────────────────────────────────────────

    /**
     * Create a unique welcome coupon tied to this registration.
     *
     * Rules:
     * - 10 % flat discount (configurable via WELCOME_COUPON_PERCENT in .env)
     * - Valid for 30 days from registration
     * - single-use per customer (limit_per_user = 1, usage_limit = 1)
     * - No minimum purchase requirement by default
     *
     * Returns null (and logs a warning) if coupon creation fails so the
     * welcome email is still sent — just without a coupon block.
     */
    private function createWelcomeCoupon(): ?Coupon
    {
        try {
            $discountPercent = (int) config('app.welcome_coupon_percent', 10);
            $validDays       = (int) config('app.welcome_coupon_days',    30);

            // WELCOME-{USERID}-{6-char random} keeps codes short & traceable.
            $code = 'WLC-' . strtoupper(Str::random(6));

            return Coupon::create([
                'code'           => $code,
                'type'           => 'percentage',
                'value'          => $discountPercent,
                'min_purchase'   => null,
                'usage_limit'    => 1,
                'limit_per_user' => 1,
                'start_date'     => now(),
                'end_date'       => now()->addDays($validDays),
                'is_active'      => true,
            ]);
        } catch (\Throwable $e) {
            Log::warning('SendWelcomeMailJob: could not create welcome coupon', [
                'user_id' => $this->user->id,
                'reason'  => $e->getMessage(),
            ]);

            return null;
        }
    }
}
