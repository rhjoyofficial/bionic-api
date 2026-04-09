<?php

namespace App\Domains\Customer\Controllers;

use App\Domains\Customer\Resources\AdminCustomerResource;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminCustomerController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = User::role('Customer')
            ->withCount('orders')
            ->withSum('orders', 'grand_total');

        if ($q = $request->input('q')) {
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%")
                    ->orWhere('phone', 'like', "%{$q}%");
            });
        }

        if ($request->filled('status')) {
            match ($request->input('status')) {
                'active'   => $query->where('is_active', true)->where('is_guest', false),
                'inactive' => $query->where('is_active', false),
                'guest'    => $query->where('is_guest', true),
                default    => null,
            };
        }

        $customers = $query->latest()->paginate(20);

        return ApiResponse::paginated(AdminCustomerResource::collection($customers));
    }

    public function show(User $user): JsonResponse
    {
        $user->loadCount('orders');
        $user->loadSum('orders', 'grand_total');
        $user->load([
            'orders' => fn($q) => $q->latest()->limit(10)->with('items'),
        ]);

        return ApiResponse::success(new AdminCustomerResource($user));
    }

    public function toggleActive(User $user): JsonResponse
    {
        $user->update(['is_active' => ! $user->is_active]);

        return ApiResponse::success(['is_active' => $user->is_active]);
    }
}
