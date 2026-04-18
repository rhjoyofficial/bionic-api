<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to {{ config('app.name') }}</title>
    <style>
        /* ── Reset ─────────────────────────────────────────── */
        * { box-sizing: border-box; }
        body  { margin: 0; padding: 0; background: #f0f5f1; font-family: 'Helvetica Neue', Arial, sans-serif; color: #1a1a1a; -webkit-font-smoothing: antialiased; }
        a     { color: inherit; text-decoration: none; }
        img   { display: block; border: 0; }

        /* ── Wrapper ────────────────────────────────────────── */
        .wrapper {
            max-width: 600px;
            margin: 32px auto;
            background: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            border: 1px solid #e5e7eb;
        }

        /* ── Header ─────────────────────────────────────────── */
        .header {
            background: linear-gradient(135deg, #1a5c2e70 0%, #2d7a45 50%, #1a5c2e75 100%);
            padding: 48px 32px 36px;
            text-align: center;
            position: relative;
        }
        .header-badge {
            display: inline-block;
            background: rgba(255,255,255,0.15);
            color: rgba(255,255,255,0.9);
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            padding: 4px 14px;
            border-radius: 999px;
            margin-bottom: 16px;
        }
        .header h1 {
            margin: 0 0 8px;
            color: #ffffff;
            font-size: 28px;
            font-weight: 800;
            letter-spacing: -0.02em;
            line-height: 1.2;
        }
        .header p {
            margin: 0;
            color: rgba(255,255,255,0.78);
            font-size: 14px;
            line-height: 1.6;
        }
        .welcome-emoji {
            font-size: 48px;
            line-height: 1;
            display: block;
            margin-bottom: 16px;
        }

        /* ── Body ───────────────────────────────────────────── */
        .body { padding: 36px 32px 28px; }

        .greeting {
            font-size: 16px;
            font-weight: 600;
            color: #111827;
            margin: 0 0 6px;
        }
        .intro {
            font-size: 14px;
            color: #6b7280;
            line-height: 1.7;
            margin: 0 0 28px;
        }

        /* ── Feature list ───────────────────────────────────── */
        .features {
            background: #f9fafb;
            border-radius: 12px;
            padding: 20px 24px;
            margin-bottom: 28px;
        }
        .features p {
            margin: 0 0 12px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #9ca3af;
        }
        .feature-item {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 10px;
        }
        .feature-item:last-child { margin-bottom: 0; }
        .feature-icon {
            width: 28px;
            height: 28px;
            border-radius: 8px;
            background: #dcfce7;
            color: #15803d;
            font-size: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-weight: 700;
        }
        .feature-text .title  { font-size: 13px; font-weight: 600; color: #111827; margin: 0 0 1px; }
        .feature-text .desc   { font-size: 12px; color: #9ca3af; margin: 0; }

        /* ── Coupon block ───────────────────────────────────── */
        .coupon-wrap {
            text-align: center;
            margin-bottom: 28px;
        }
        .coupon-label {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: #9ca3af;
            margin-bottom: 12px;
        }
        .coupon-box {
            display: inline-block;
            width: 100%;
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
            border: 2px dashed #86efac;
            border-radius: 14px;
            padding: 22px 24px;
        }
        .coupon-discount {
            font-size: 38px;
            font-weight: 800;
            color: #15803d;
            letter-spacing: -0.02em;
            line-height: 1;
            margin-bottom: 4px;
        }
        .coupon-sub {
            font-size: 13px;
            color: #4b7c5a;
            margin-bottom: 16px;
        }
        .coupon-code-wrap {
            display: inline-block;
            background: #ffffff;
            border: 1.5px solid #bbf7d0;
            border-radius: 8px;
            padding: 8px 20px;
            margin-bottom: 10px;
        }
        .coupon-code {
            font-family: 'Courier New', Courier, monospace;
            font-size: 18px;
            font-weight: 800;
            color: #166534;
            letter-spacing: 0.14em;
        }
        .coupon-validity {
            font-size: 12px;
            color: #6b7280;
        }
        .coupon-validity strong { color: #374151; }

        /* ── Divider ────────────────────────────────────────── */
        .divider {
            border: none;
            border-top: 1px solid #f3f4f6;
            margin: 24px 0;
        }

        /* ── CTA button ─────────────────────────────────────── */
        .cta { text-align: center; margin: 28px 0 8px; }
        .cta a {
            display: inline-block;
            background: #1a5c2e;
            color: #ffffff !important;
            text-decoration: none;
            padding: 14px 36px;
            border-radius: 999px;
            font-size: 14px;
            font-weight: 700;
            letter-spacing: 0.01em;
        }
        .cta-sub {
            text-align: center;
            font-size: 12px;
            color: #9ca3af;
            margin-top: 10px;
        }

        /* ── Footer ─────────────────────────────────────────── */
        .footer {
            text-align: center;
            padding: 20px 32px 32px;
            font-size: 12px;
            color: #9ca3af;
            line-height: 1.7;
        }
        .footer a { color: #16a34a; }

        /* ── Mobile ─────────────────────────────────────────── */
        @media only screen and (max-width: 600px) {
            .wrapper  { margin: 0; border-radius: 0; }
            .header   { padding: 36px 20px 28px; }
            .body     { padding: 28px 20px 20px; }
            .coupon-discount { font-size: 30px; }
        }
    </style>
</head>
<body>
<div @class(['wrapper'])>

    {{-- Header --}}
    <div @class(['header'])>
        <span @class(['welcome-emoji'])><img src="{{ asset('assets/images/bionic-logo.png') }}" alt="Logo"></span>
        <div @class(['header-badge'])>New Member</div>
        <h1>Welcome to {{ config('app.name') }}!</h1>
        <p>Your account has been created successfully.<br>We're thrilled to have you.</p>
    </div>

    <div @class(['body'])>

        {{-- Greeting --}}
        <p @class(['greeting'])>Hi {{ $user->name }},</p>
        <p @class(['intro'])>
            Thank you for joining {{ config('app.name') }}. We're a brand committed to
            100% natural, lab-tested health products delivered straight to your door.
            Here's a little gift to get you started!
        </p>

        {{-- Coupon --}}
        @if ($coupon)
        <p @class(['coupon-label'])>Your exclusive welcome offer</p>
        <div @class(['coupon-wrap'])>
            <div @class(['coupon-box'])>
                <div @class(['coupon-discount'])>
                    {{ $coupon->type === 'percentage'
                        ? $coupon->value . '% OFF'
                        : '৳' . number_format($coupon->value, 0) . ' OFF' }}
                </div>
                <p @class(['coupon-sub'])>
                    {{ $coupon->type === 'percentage'
                        ? 'on your first order'
                        : 'flat discount on your first order' }}
                    @if ($coupon->min_purchase)
                        &nbsp;· Min. purchase ৳{{ number_format($coupon->min_purchase, 0) }}
                    @endif
                </p>
                <div @class(['coupon-code-wrap'])>
                    <span @class(['coupon-code'])>{{ $coupon->code }}</span>
                </div>
                <p @class(['coupon-validity'])>
                    @if ($coupon->end_date)
                        Valid until <strong>{{ $coupon->end_date->format('d M Y') }}</strong> · Single use
                    @else
                        Limited time offer · Single use
                    @endif
                </p>
            </div>
        </div>
        @endif

        <hr @class(['divider'])>

        {{-- Why us --}}
        <div @class(['features'])>
            <p>Why you'll love us</p>
            <div @class(['feature-item'])>
                <div @class(['feature-icon'])>✓</div>
                <div @class(['feature-text'])>
                    <p @class(['title'])>100% Natural Ingredients</p>
                    <p @class(['desc'])>No preservatives, no artificial additives — ever.</p>
                </div>
            </div>
            <div @class(['feature-item'])>
                <div @class(['feature-icon'])>✓</div>
                <div @class(['feature-text'])>
                    <p @class(['title'])>Lab Certified Quality</p>
                    <p @class(['desc'])>Every product batch is independently tested and certified.</p>
                </div>
            </div>
            <div @class(['feature-item'])>
                <div @class(['feature-icon'])>✓</div>
                <div @class(['feature-text'])>
                    <p @class(['title'])>Fast Doorstep Delivery</p>
                    <p @class(['desc'])>Delivered across Bangladesh within 1–3 business days.</p>
                </div>
            </div>
        </div>

        {{-- CTA --}}
        <div @class(['cta'])>
            <a href="{{ config('app.url') }}/products">Shop Now</a>
        </div>
        <p @class(['cta-sub'])>
            Or <a href="{{ config('app.url') }}/account/dashboard" style="color:#16a34a;">visit your account</a>
            to track orders and manage your profile.
        </p>

    </div>

    {{-- Footer --}}
    <div @class(['footer'])>
        <p>© {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        <p>
            You received this email because you created an account with us.<br>
            If this wasn't you, please <a href="mailto:{{ config('mail.from.address') }}">contact support</a>.
        </p>
    </div>

</div>
</body>
</html>
