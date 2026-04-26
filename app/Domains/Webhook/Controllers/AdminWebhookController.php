<?php

namespace App\Domains\Webhook\Controllers;

use App\Domains\Webhook\Models\Webhook;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Jobs\SendWebhookJob;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdminWebhookController extends Controller
{
    /** Supported event names — matches what listeners dispatch. */
    private const EVENTS = [
        'order.created',
        'order.status_changed',
        'order.payment_updated',
        'coupon.expired',
        'customer.registered',
        'shipment.status_updated',
    ];

    public function index(): JsonResponse
    {
        try {
            $webhooks = Webhook::orderBy('event')->orderByDesc('created_at')->get()
                ->map(fn($w) => $this->format($w));

            return ApiResponse::success([
                'webhooks'       => $webhooks,
                'allowed_events' => self::EVENTS,
            ]);
        } catch (Exception $e) {
            return $this->handleError($e, 'Failed to load webhooks');
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'event'  => ['required', 'string', 'in:' . implode(',', self::EVENTS)],
                'url'    => ['required', 'url', 'max:500'],
                'secret' => ['required', 'string', 'min:16', 'max:255'],
            ]);

            $webhook = Webhook::create([
                'event'     => $data['event'],
                'url'       => $data['url'],
                'secret'    => $data['secret'],
                'is_active' => true,
            ]);

            return ApiResponse::success($this->format($webhook), 'Webhook registered', 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::error('Validation failed', $e->errors(), 422);
        } catch (Exception $e) {
            return $this->handleError($e, 'Failed to create webhook');
        }
    }

    public function toggle(Webhook $webhook): JsonResponse
    {
        try {
            $webhook->update(['is_active' => ! $webhook->is_active]);

            return ApiResponse::success(
                ['is_active' => $webhook->is_active],
                $webhook->is_active ? 'Webhook enabled' : 'Webhook disabled'
            );
        } catch (Exception $e) {
            return $this->handleError($e, 'Failed to toggle webhook');
        }
    }

    public function test(Webhook $webhook): JsonResponse
    {
        try {
            if (! $webhook->is_active) {
                return ApiResponse::error('Cannot test an inactive webhook. Enable it first.', null, 422);
            }

            SendWebhookJob::dispatch($webhook->event, [
                'test'       => true,
                'event'      => $webhook->event,
                'message'    => 'This is a test ping from ' . config('app.name'),
                'webhook_id' => $webhook->id,
                'fired_at'   => now()->toISOString(),
            ]);

            return ApiResponse::success(null, 'Test ping queued for dispatch');
        } catch (Exception $e) {
            return $this->handleError($e, 'Failed to queue test');
        }
    }

    public function destroy(Webhook $webhook): JsonResponse
    {
        try {
            $webhook->delete();

            return ApiResponse::success(null, 'Webhook deleted');
        } catch (Exception $e) {
            return $this->handleError($e, 'Failed to delete webhook');
        }
    }

    private function format(Webhook $w): array
    {
        return [
            'id'         => $w->id,
            'event'      => $w->event,
            'url'        => $w->url,
            'is_active'  => $w->is_active,
            'secret_hint'=> $w->secret ? ('••••' . substr($w->secret, -4)) : null,
            'created_at' => $w->created_at?->toISOString(),
        ];
    }

    private function handleError(Exception $e, string $msg, int $code = 500): JsonResponse
    {
        Log::error("{$msg}: {$e->getMessage()}", ['file' => $e->getFile(), 'line' => $e->getLine()]);

        return ApiResponse::error($msg, config('app.debug') ? $e->getMessage() : null, $code);
    }
}
