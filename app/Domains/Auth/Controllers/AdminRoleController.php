<?php

namespace App\Domains\Auth\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AdminRoleController extends Controller
{
    /** Roles that cannot be renamed or deleted. */
    protected const PROTECTED = ['Super Admin', 'Admin', 'Customer'];

    /**
     * Canonical permission grouping for the matrix UI.
     * Order here controls column order in the matrix.
     */
    protected const GROUPS = [
        'category'     => ['category.view', 'category.create', 'category.update', 'category.delete'],
        'product'      => ['product.view', 'product.create', 'product.update', 'product.delete'],
        'order'        => ['order.view', 'order.update', 'order.export'],
        'coupon'       => ['coupon.view', 'coupon.create', 'coupon.update', 'coupon.delete'],
        'shipping'     => ['shipping.view', 'shipping.create', 'shipping.update', 'shipping.delete'],
        'customer'     => ['customer.view', 'customer.update', 'customer.deactivate'],
        'landing'     =>  ['landing-pages.view', 'landing-pages.create', 'landing-pages.update', 'landing-pages.delete'],
        'notification' => ['notification.view', 'notification.send', 'notification.manage'],
        'system'       => ['system.settings', 'system.webhooks', 'system.activity_log'],
        'analytics'    => ['analytics.view'],
        'role'         => ['role.manage', 'permission.manage'],
        'staff'        => ['staff.create', 'staff.update', 'staff.delete'],
        'customer_mgmt' => ['customer.create', 'customer.delete', 'customer.change_password'],
    ];

    // ── Role List ─────────────────────────────────────────────────────────────

    public function index(): JsonResponse
    {
        try {
            $userCounts = DB::table('model_has_roles')
                ->where('model_type', User::class)
                ->groupBy('role_id')
                ->pluck(DB::raw('COUNT(*)'), 'role_id');

            $roles = Role::with('permissions:id,name')
                ->orderByRaw("FIELD(name, 'Super Admin', 'Admin') DESC")
                ->orderBy('name')
                ->get()
                ->map(fn($r) => [
                    'id'                => $r->id,
                    'name'              => $r->name,
                    'is_protected'      => in_array($r->name, self::PROTECTED),
                    'users_count'       => (int) ($userCounts[$r->id] ?? 0),
                    'permissions_count' => $r->permissions->count(),
                    'permissions'       => $r->permissions->pluck('name')->values(),
                ]);

            return ApiResponse::success(['roles' => $roles]);
        } catch (Exception $e) {
            return $this->handleError($e, 'Failed to load roles');
        }
    }

    // ── Permission Matrix ─────────────────────────────────────────────────────

    public function matrix(): JsonResponse
    {
        try {
            $userCounts = DB::table('model_has_roles')
                ->where('model_type', User::class)
                ->groupBy('role_id')
                ->pluck(DB::raw('COUNT(*)'), 'role_id');

            // Exclude storefront Customer role from the matrix
            $roles = Role::with('permissions:id,name')
                ->where('name', '!=', 'Customer')
                ->orderByRaw("FIELD(name, 'Super Admin', 'Admin') DESC")
                ->orderBy('name')
                ->get();

            // Build matrix: { roleId: { permName: bool } }
            $matrix = [];
            $allGroupedPerms = array_merge(...array_values(self::GROUPS));

            foreach ($roles as $role) {
                $granted = $role->permissions->pluck('name')->flip(); // O(1) lookup
                $matrix[$role->id] = [];
                foreach ($allGroupedPerms as $perm) {
                    $matrix[$role->id][$perm] = isset($granted[$perm]);
                }
            }

            $rolesData = $roles->map(fn($r) => [
                'id'           => $r->id,
                'name'         => $r->name,
                'is_protected' => in_array($r->name, self::PROTECTED),
                'users_count'  => (int) ($userCounts[$r->id] ?? 0),
            ]);

            return ApiResponse::success([
                'roles'  => $rolesData,
                'groups' => self::GROUPS,
                'matrix' => $matrix,
            ]);
        } catch (Exception $e) {
            return $this->handleError($e, 'Failed to load permission matrix');
        }
    }

    // ── Role CRUD ─────────────────────────────────────────────────────────────

    public function store(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'name' => 'required|string|max:100|unique:roles,name',
            ]);

            $role = Role::create(['name' => trim($data['name']), 'guard_name' => 'web']);

            return ApiResponse::success([
                'id'                => $role->id,
                'name'              => $role->name,
                'is_protected'      => false,
                'users_count'       => 0,
                'permissions_count' => 0,
                'permissions'       => [],
            ], 'Role created successfully', 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::error('Validation failed', $e->errors(), 422);
        } catch (Exception $e) {
            return $this->handleError($e, 'Failed to create role');
        }
    }

    public function update(Request $request, Role $role): JsonResponse
    {
        try {
            if (in_array($role->name, self::PROTECTED)) {
                return ApiResponse::error("'{$role->name}' is a system role and cannot be renamed.", null, 422);
            }

            $data = $request->validate([
                'name' => "required|string|max:100|unique:roles,name,{$role->id}",
            ]);

            $role->update(['name' => trim($data['name'])]);

            return ApiResponse::success(
                ['id' => $role->id, 'name' => $role->name],
                'Role renamed successfully'
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::error('Validation failed', $e->errors(), 422);
        } catch (Exception $e) {
            return $this->handleError($e, 'Failed to rename role');
        }
    }

    public function destroy(Role $role): JsonResponse
    {
        try {
            if (in_array($role->name, self::PROTECTED)) {
                return ApiResponse::error("'{$role->name}' is a system role and cannot be deleted.", null, 422);
            }

            $userCount = DB::table('model_has_roles')
                ->where('role_id', $role->id)
                ->where('model_type', User::class)
                ->count();

            if ($userCount > 0) {
                return ApiResponse::error(
                    "Cannot delete role — {$userCount} user(s) currently assigned. Reassign them first.",
                    null,
                    422
                );
            }

            $role->delete();

            return ApiResponse::success(null, "Role '{$role->name}' deleted");
        } catch (Exception $e) {
            return $this->handleError($e, 'Failed to delete role');
        }
    }

    // ── Sync Permissions ──────────────────────────────────────────────────────

    public function syncPermissions(Request $request, Role $role): JsonResponse
    {
        try {
            $data = $request->validate([
                'permissions'   => 'present|array',
                'permissions.*' => 'string|exists:permissions,name',
            ]);

            // Super Admin always gets all permissions — enforce invariant
            if ($role->name === 'Super Admin') {
                $all = Permission::pluck('name')->toArray();
                $role->syncPermissions($all);
                return ApiResponse::success(
                    ['role_id' => $role->id, 'permissions_count' => count($all)],
                    'Super Admin always has all permissions — no change needed'
                );
            }

            $role->syncPermissions($data['permissions']);

            // Clear Spatie's permission cache
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

            return ApiResponse::success([
                'role_id'           => $role->id,
                'permissions_count' => count($data['permissions']),
                'permissions'       => $data['permissions'],
            ], "Permissions updated for '{$role->name}'");
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::error('Validation failed', $e->errors(), 422);
        } catch (Exception $e) {
            return $this->handleError($e, 'Failed to sync permissions');
        }
    }

    // ── Role Users ────────────────────────────────────────────────────────────

    public function users(Role $role, Request $request): JsonResponse
    {
        try {
            $users = User::role($role->name)
                ->select('id', 'name', 'email', 'phone', 'is_active', 'last_login_at', 'created_at')
                ->orderByDesc('created_at')
                ->paginate(15);

            return ApiResponse::success([
                'role' => ['id' => $role->id, 'name' => $role->name],
                'data' => $users->items(),
                'meta' => [
                    'current_page' => $users->currentPage(),
                    'last_page'    => $users->lastPage(),
                    'total'        => $users->total(),
                ],
            ]);
        } catch (Exception $e) {
            return $this->handleError($e, 'Failed to load role users');
        }
    }

    // ── Admin Users (non-Customer) ────────────────────────────────────────────

    public function adminUsers(Request $request): JsonResponse
    {
        try {
            // Users who are NOT purely Customer role
            $query = User::whereHas('roles', fn($q) => $q->where('name', '!=', 'Customer'))
                ->orDoesntHave('roles')
                ->with('roles:id,name')
                ->select('id', 'name', 'email', 'phone', 'is_active', 'last_login_at', 'created_at');

            if ($q = $request->input('q')) {
                $query->where(
                    fn($sub) =>
                    $sub->where('name', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%")
                );
            }

            $users = $query->orderByDesc('created_at')->paginate(20);

            $items = collect($users->items())->map(fn($u) => [
                'id'            => $u->id,
                'name'          => $u->name,
                'email'         => $u->email,
                'phone'         => $u->phone,
                'is_active'     => $u->is_active,
                'last_login_at' => $u->last_login_at?->toISOString(),
                'created_at'    => $u->created_at?->toISOString(),
                'roles'         => $u->roles->pluck('name')->values(),
                'primary_role'  => $u->roles->first()?->name,
            ]);

            $allRoles = Role::where('name', '!=', 'Customer')
                ->orderByRaw("FIELD(name, 'Super Admin', 'Admin') DESC")
                ->orderBy('name')
                ->pluck('name');

            return ApiResponse::success([
                'data'      => $items,
                'all_roles' => $allRoles,
                'meta'      => [
                    'current_page' => $users->currentPage(),
                    'last_page'    => $users->lastPage(),
                    'total'        => $users->total(),
                ],
            ]);
        } catch (Exception $e) {
            return $this->handleError($e, 'Failed to load admin users');
        }
    }

    public function assignRole(Request $request, User $user): JsonResponse
    {
        try {
            $data = $request->validate([
                'role' => 'required|string|exists:roles,name',
            ]);

            if ($data['role'] === 'Customer') {
                return ApiResponse::error("Cannot assign 'Customer' role through admin panel.", null, 422);
            }

            // Preserve Customer role if user has it (unlikely for admins but safe)
            $currentRoles   = $user->roles->pluck('name')->toArray();
            $customerRoles  = array_filter($currentRoles, fn($r) => $r === 'Customer');
            $newRoles       = array_values(array_unique(array_merge([$data['role']], $customerRoles)));

            $user->syncRoles($newRoles);

            // Clear Spatie cache
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

            return ApiResponse::success([
                'user_id'      => $user->id,
                'primary_role' => $data['role'],
            ], "'{$data['role']}' assigned to {$user->name}");
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::error('Validation failed', $e->errors(), 422);
        } catch (Exception $e) {
            return $this->handleError($e, 'Failed to assign role');
        }
    }

    // ── Admin Staff CRUD ──────────────────────────────────────────────────────

    public function storeAdmin(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'name'     => 'required|string|max:191',
                'email'    => 'required|email|max:191|unique:users,email',
                'phone'    => 'nullable|string|max:20',
                'password' => 'required|string|min:8',
                'role'     => 'required|string|exists:roles,name',
            ]);

            if ($data['role'] === 'Customer') {
                return ApiResponse::error("Cannot create admin staff with 'Customer' role.", null, 422);
            }

            $user = User::create([
                'name'          => $data['name'],
                'email'         => $data['email'],
                'phone'         => $data['phone'] ?? null,
                'password'      => Hash::make($data['password']),
                'is_active'     => true,
                'is_guest'      => false,
                'referral_code' => strtoupper(Str::random(8)),
            ]);

            $user->assignRole($data['role']);

            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

            return ApiResponse::success([
                'id'           => $user->id,
                'name'         => $user->name,
                'email'        => $user->email,
                'phone'        => $user->phone,
                'is_active'    => $user->is_active,
                'primary_role' => $data['role'],
                'roles'        => [$data['role']],
                'created_at'   => $user->created_at?->toISOString(),
            ], 'Admin staff created', 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::error('Validation failed', $e->errors(), 422);
        } catch (Exception $e) {
            return $this->handleError($e, 'Failed to create admin staff');
        }
    }

    public function updateAdmin(Request $request, User $user): JsonResponse
    {
        try {
            $data = $request->validate([
                'name'  => 'sometimes|required|string|max:191',
                'email' => "sometimes|required|email|max:191|unique:users,email,{$user->id}",
                'phone' => 'nullable|string|max:20',
            ]);

            $user->update($data);

            return ApiResponse::success([
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
            ], 'Admin staff updated');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::error('Validation failed', $e->errors(), 422);
        } catch (Exception $e) {
            return $this->handleError($e, 'Failed to update admin staff');
        }
    }

    public function destroyAdmin(Request $request, User $user): JsonResponse
    {
        try {
            // Prevent self-deletion
            if ($request->user()?->id === $user->id) {
                return ApiResponse::error('You cannot delete your own account.', null, 422);
            }

            // Prevent deleting Super Admin users
            if ($user->hasRole('Super Admin')) {
                return ApiResponse::error('Super Admin accounts cannot be deleted.', null, 422);
            }

            $user->delete();

            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

            return ApiResponse::success(null, "Admin staff '{$user->name}' deleted");
        } catch (Exception $e) {
            return $this->handleError($e, 'Failed to delete admin staff');
        }
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

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
