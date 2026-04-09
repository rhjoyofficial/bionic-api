<?php

namespace App\Domains\Coupon\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CouponResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'             => $this->id,
            'code'           => $this->code,
            'type'           => $this->type,
            'value'          => (float) $this->value,
            'min_purchase'   => $this->min_purchase ? (float) $this->min_purchase : null,
            'usage_limit'    => $this->usage_limit,
            'used_count'     => $this->used_count,
            'limit_per_user' => $this->limit_per_user,
            'start_date'     => $this->start_date?->toDateTimeString(),
            'end_date'       => $this->end_date?->toDateTimeString(),
            'is_active'      => $this->is_active,
            'is_valid'       => $this->isValid(),
            'created_at'     => $this->created_at?->toDateTimeString(),

            // Loaded via withCount('usages') + withSum('usages','discount_amount')
            'usages_count'   => $this->when(
                isset($this->usages_count),
                $this->usages_count
            ),
            'total_discount' => $this->when(
                isset($this->usages_sum_discount_amount),
                fn() => (float) ($this->usages_sum_discount_amount ?? 0)
            ),

            // Recent usage history — loaded via with('usages.user','usages.order')
            'recent_usages'  => $this->when(
                $this->relationLoaded('usages'),
                fn() => $this->usages->map(fn($u) => [
                    'id'              => $u->id,
                    'discount_amount' => (float) $u->discount_amount,
                    'created_at'      => $u->created_at?->toDateTimeString(),
                    'user'            => $u->relationLoaded('user') ? [
                        'id'    => $u->user?->id,
                        'name'  => $u->user?->name,
                        'phone' => $u->user?->phone,
                    ] : null,
                    'order_number'    => $u->relationLoaded('order')
                        ? $u->order?->order_number
                        : null,
                ])
            ),
        ];
    }
}
