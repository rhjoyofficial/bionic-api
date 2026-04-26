<?php

namespace App\Domains\Coupon\Controllers;

use App\Domains\ActivityLog\Services\AdminLogger;
use App\Domains\Coupon\Models\Coupon;
use App\Domains\Coupon\Requests\BulkGenerateCouponRequest;
use App\Domains\Coupon\Requests\StoreCouponRequest;
use App\Domains\Coupon\Requests\UpdateCouponRequest;
use App\Domains\Coupon\Resources\CouponResource;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AdminCouponController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Coupon::withCount('usages')
                ->withSum('usages', 'discount_amount');

            if ($q = $request->input('q')) {
                $query->where('code', 'like', "%{$q}%");
            }

            if ($request->filled('status')) {
                match ($request->input('status')) {
                    'active'   => $query->where('is_active', true)
                                        ->where(fn($q) =>
                                            $q->whereNull('end_date')
                                              ->orWhere('end_date', '>=', now())
                                        ),
                    'inactive' => $query->where('is_active', false),
                    'expired'  => $query->where('end_date', '<', now()),
                    default    => null,
                };
            }

            $coupons = $query->latest()->paginate(15);

            return ApiResponse::paginated(CouponResource::collection($coupons));
        } catch (Exception $e) {
            return $this->handleError($e, 'Failed to retrieve coupons');
        }
    }

    public function show(Coupon $coupon): JsonResponse
    {
        try {
            $coupon->loadCount('usages');
            $coupon->loadSum('usages', 'discount_amount');
            $coupon->load(['usages' => fn($q) => $q->latest()->limit(20)->with(['user', 'order'])]);

            return ApiResponse::success(new CouponResource($coupon));
        } catch (Exception $e) {
            return $this->handleError($e, 'Failed to retrieve coupon');
        }
    }

    public function stats(): JsonResponse
    {
        try {
            $stats = [
                'total'          => Coupon::count(),
                'active'         => Coupon::where('is_active', true)
                    ->where(fn($q) => $q->whereNull('end_date')->orWhere('end_date', '>=', now()))
                    ->count(),
                'expired'        => Coupon::where('end_date', '<', now())->count(),
                'total_discount' => (float) DB::table('coupon_usages')->sum('discount_amount'),
                'total_usages'   => DB::table('coupon_usages')->count(),
            ];

            return ApiResponse::success($stats);
        } catch (Exception $e) {
            return $this->handleError($e, 'Failed to retrieve coupon stats');
        }
    }

    public function store(StoreCouponRequest $request): JsonResponse
    {
        try {
            $data         = $request->validated();
            $data['code'] = strtoupper($data['code']);

            $coupon = Coupon::create($data);

            AdminLogger::log('coupons', "Coupon {$coupon->code} created", $coupon, [], 'created');

            return ApiResponse::success(new CouponResource($coupon), 'Coupon created successfully', 201);
        } catch (Exception $e) {
            return $this->handleError($e, 'Failed to create coupon');
        }
    }

    public function update(UpdateCouponRequest $request, Coupon $coupon): JsonResponse
    {
        try {
            $coupon->update($request->validated());

            AdminLogger::log('coupons', "Coupon {$coupon->code} updated", $coupon, [], 'updated');

            return ApiResponse::success(new CouponResource($coupon), 'Coupon updated successfully');
        } catch (Exception $e) {
            return $this->handleError($e, 'Failed to update coupon');
        }
    }

    public function destroy(Coupon $coupon): JsonResponse
    {
        try {
            $code = $coupon->code;
            $coupon->delete();

            AdminLogger::log('coupons', "Coupon {$code} deleted", null, ['code' => $code], 'deleted');

            return ApiResponse::success(null, 'Coupon deleted successfully');
        } catch (Exception $e) {
            return $this->handleError($e, 'Failed to delete coupon');
        }
    }

    public function bulkGenerate(BulkGenerateCouponRequest $request): JsonResponse
    {
        try {
            $data   = $request->validated();
            $prefix = strtoupper($data['prefix']);
            $count  = (int) $data['count'];

            $shared = array_filter([
                'type'           => $data['type'],
                'value'          => $data['value'],
                'min_purchase'   => $data['min_purchase'] ?? null,
                'usage_limit'    => $data['usage_limit'] ?? null,
                'limit_per_user' => $data['limit_per_user'] ?? null,
                'start_date'     => $data['start_date'] ?? null,
                'end_date'       => $data['end_date'] ?? null,
                'is_active'      => $data['is_active'] ?? true,
            ], fn($v) => $v !== null);

            $codes    = [];
            $attempts = 0;
            $maxTries = $count * 5;

            while (count($codes) < $count && $attempts < $maxTries) {
                $candidate = $prefix . strtoupper(Str::random(8));
                if (! in_array($candidate, $codes)) {
                    $codes[] = $candidate;
                }
                $attempts++;
            }

            // Exclude any that already exist in DB
            $existing = Coupon::whereIn('code', $codes)->pluck('code')->toArray();
            $codes    = array_values(array_diff($codes, $existing));

            if (empty($codes)) {
                return ApiResponse::error('Could not generate unique codes. Try a different prefix.', null, 422);
            }

            $now  = now();
            $rows = array_map(fn($code) => array_merge($shared, [
                'code'       => $code,
                'used_count' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ]), $codes);

            DB::table('coupons')->insert($rows);

            AdminLogger::log('coupons', "Bulk generated {$count} coupons with prefix {$prefix}", null, [
                'prefix' => $prefix,
                'count'  => count($codes),
            ], 'bulk_generated');

            return ApiResponse::success(
                ['codes' => $codes, 'count' => count($codes)],
                count($codes) . ' coupons generated successfully',
                201
            );
        } catch (Exception $e) {
            return $this->handleError($e, 'Failed to bulk generate coupons');
        }
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
