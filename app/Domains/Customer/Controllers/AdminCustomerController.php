<?php

namespace App\Domains\Customer\Controllers;

use App\Domains\ActivityLog\Services\AdminLogger;
use App\Domains\Customer\Resources\AdminCustomerResource;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

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

    public function store(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'name'     => 'required|string|max:191',
                'email'    => 'required|email|max:191|unique:users,email',
                'phone'    => 'nullable|string|max:20',
                'password' => 'required|string|min:8',
            ]);

            $user = User::create([
                'name'           => $data['name'],
                'email'          => $data['email'],
                'phone'          => $data['phone'] ?? null,
                'password'       => Hash::make($data['password']),
                'is_active'      => true,
                'is_guest'       => false,
                'referral_code'  => strtoupper(Str::random(8)),
            ]);

            $user->assignRole('Customer');

            AdminLogger::log('customers', "Customer {$user->name} created", $user, ['email' => $user->email], 'created');

            return ApiResponse::success(new AdminCustomerResource($user), 'Customer created', 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::error('Validation failed', $e->errors(), 422);
        } catch (Exception $e) {
            return $this->handleError($e, 'Failed to create customer');
        }
    }

    public function update(Request $request, User $user): JsonResponse
    {
        try {
            $data = $request->validate([
                'name'  => 'sometimes|required|string|max:191',
                'email' => "sometimes|required|email|max:191|unique:users,email,{$user->id}",
                'phone' => 'nullable|string|max:20',
            ]);

            $oldData = $user->only(['name', 'email', 'phone']);
            $user->update($data);
            $newData = $user->only(['name', 'email', 'phone']);

            AdminLogger::log('customers', "Customer {$user->name} updated", $user, [
                'old' => $oldData,
                'new' => $newData,
            ], 'updated');

            return ApiResponse::success(new AdminCustomerResource($user), 'Customer updated');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::error('Validation failed', $e->errors(), 422);
        } catch (Exception $e) {
            return $this->handleError($e, 'Failed to update customer');
        }
    }

    public function destroy(User $user): JsonResponse
    {
        try {
            if ($user->orders()->exists()) {
                return ApiResponse::error(
                    'Cannot delete customer with existing orders. Deactivate instead.',
                    null,
                    422
                );
            }

            $name = $user->name;
            $user->delete();

            AdminLogger::log('customers', "Customer {$name} deleted", null, ['name' => $name, 'id' => $user->id], 'deleted');

            return ApiResponse::success(null, 'Customer deleted');
        } catch (Exception $e) {
            return $this->handleError($e, 'Failed to delete customer');
        }
    }

    public function changePassword(Request $request, User $user): JsonResponse
    {
        try {
            $data = $request->validate([
                'password'              => 'required|string|min:8|confirmed',
                'password_confirmation' => 'required|string',
            ]);

            $user->update(['password' => Hash::make($data['password'])]);

            AdminLogger::log('customers', "Customer {$user->name} password changed", $user, [], 'password_changed');

            return ApiResponse::success(null, 'Password changed successfully');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::error('Validation failed', $e->errors(), 422);
        } catch (Exception $e) {
            return $this->handleError($e, 'Failed to change password');
        }
    }

    public function toggleActive(User $user): JsonResponse
    {
        $user->update(['is_active' => ! $user->is_active]);

        AdminLogger::log('customers', "Customer {$user->name} status changed to " . ($user->is_active ? 'Active' : 'Inactive'), $user, ['is_active' => $user->is_active], 'status_changed');

        return ApiResponse::success(['is_active' => $user->is_active]);
    }

    private function handleError(Exception $e, string $msg, int $code = 500): JsonResponse
    {
        Log::error("{$msg}: {$e->getMessage()}", [
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
