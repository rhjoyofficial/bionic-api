<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Update</title>
    <style>
        body { margin: 0; padding: 0; background: #f0f5f1; font-family: 'Helvetica Neue', Arial, sans-serif; color: #1a1a1a; -webkit-font-smoothing: antialiased; }
        .wrapper { max-width: 600px; margin: 32px auto; background: #ffffff; border-radius: 16px; overflow: hidden; border: 1px solid #e5e7eb; }
        .header { background: #1a5c2e; padding: 36px 32px; text-align: center; }
        .header h1 { margin: 0; color: #ffffff; font-size: 24px; font-weight: 700; }
        .header p { margin: 6px 0 0; color: rgba(255,255,255,0.75); font-size: 14px; }
        .order-num { display: inline-block; margin-top: 12px; background: rgba(255,255,255,0.15); color: #ffffff; font-size: 13px; font-weight: 600; letter-spacing: 0.08em; padding: 6px 16px; border-radius: 999px; font-family: monospace; }
        .body { padding: 36px 32px; text-align: center; }
        
        .status-box {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 28px;
        }
        .status-title { font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; color: #6b7280; margin: 0 0 8px; }
        .status-badge {
            display: inline-block;
            font-size: 18px;
            font-weight: 800;
            color: #1a5c2e;
            padding: 4px 0;
            text-transform: capitalize;
        }
        
        .message-text { font-size: 15px; color: #4b5563; line-height: 1.6; margin: 0 0 24px; }
        
        .cta { text-align: center; margin: 32px 0 16px; }
        .cta a { display: inline-block; background: #1a5c2e; color: #ffffff !important; text-decoration: none; padding: 14px 36px; border-radius: 999px; font-size: 15px; font-weight: 700; }
        
        .footer { text-align: center; padding: 24px 32px 32px; font-size: 12px; color: #9ca3af; line-height: 1.6; border-top: 1px solid #f3f4f6; }
        .footer a { color: #16a34a; text-decoration: none; }
        
        @media only screen and (max-width: 600px) {
            .wrapper  { margin: 0; border-radius: 0; border: none; }
            .header   { padding: 32px 20px; }
            .body     { padding: 28px 20px; }
        }
    </style>
</head>
<body>
<div class="wrapper">

    {{-- Header --}}
    <div class="header">
        <h1>Order Update</h1>
        <p>The status of your order has changed.</p>
        <span class="order-num">#{{ $order->order_number }}</span>
    </div>

    <div class="body">
        <p class="message-text">Hi {{ $order->shippingAddress?->customer_name ?: 'Valued Customer' }},</p>
        
        <div class="status-box">
            <p class="status-title">Current Status</p>
            <div class="status-badge">{{ $newStatus }}</div>
            
            <p style="font-size: 13px; color: #6b7280; margin: 12px 0 0;">
                @if($newStatus === 'confirmed')
                    Your order has been confirmed and we are preparing it.
                @elseif($newStatus === 'processing')
                    Your order is currently being processed and packed at our facility.
                @elseif($newStatus === 'shipped')
                    Good news! Your order has been handed over to our delivery partner.
                @elseif($newStatus === 'delivered')
                    Your order has been successfully delivered. Thank you!
                @elseif($newStatus === 'cancelled')
                    Your order has been cancelled. Reach out if you need help.
                @else
                    We are tracking this change and will keep you updated.
                @endif
            </p>
        </div>

        <p class="message-text" style="font-size: 14px;">
            You can always track your complete order history and real-time status updates through your dashboard.
        </p>

        {{-- CTA --}}
        <div class="cta">
            <a href="{{ route('order.success', ['order' => $order->order_number]) }}">Track Your Order</a>
        </div>
    </div>

    <div class="footer">
        <p>© {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        <p>You received this email because of a status change on your recent order.</p>
    </div>

</div>
</body>
</html>
