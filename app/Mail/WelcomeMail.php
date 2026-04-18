<?php

namespace App\Mail;

use App\Domains\Coupon\Models\Coupon;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * WelcomeMail
 *
 * Dispatched (via queue) immediately after a customer registers.
 * Carries the new User and, optionally, a welcome Coupon to display in the email.
 *
 * Why queued?  SMTP round-trips can take 1–3 seconds. Queuing this mail ensures
 * the registration HTTP response is instant and the customer is never blocked by
 * a slow or temporarily unreachable mail server.
 */
class WelcomeMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  User       $user    The newly registered customer.
     * @param  Coupon|null $coupon  A pre-created welcome coupon (or null if none).
     */
    public function __construct(
        public readonly User $user,
        public readonly ?Coupon $coupon = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Welcome to ' . config('app.name') . '! 🎉',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.welcome',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
