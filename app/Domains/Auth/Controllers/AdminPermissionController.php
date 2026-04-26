<?php

namespace App\Domains\Auth\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Permission;

class AdminPermissionController extends Controller
{
    /**
     * Permissions that cannot be renamed or deleted through the UI.
     * These are core system permissions referenced in code / middleware.
     */
    protected const PROTECTED = [
        'category.view', 'category.create', 'category.update', 'category.delete',
        'product.view', 'product.create', 'product.update', 'product.delete',
        'order.view', 'order.create', 'order.update', 'order.export',
        'coupon.view', 'coupon.create', 'coupon.update', 'coupon.delete',
        'shipping.view', 'shipping.create', 'shipping.update', 'shipping.delete',
        'customer.view', 'customer.create', 'customer.update', 'customer.delete',
        'customer.deactivate', 'customer.change_password',
        'notification.view', 'notification.send', 'notification.manage',
        'system.settings', 'system.webhooks', 'system.activity_log',
        'analytics.view',
        'role.manage', 'permission.manage',
        'staff.create', 'staff.update', 'staff.delete',
        'landing-pages.view', 'landing-pages.create', 'landing-pages.update', 'landing-pages.delete',
        'hero.view', 'hero.create', 'hero.update', 'hero.delete',
    ];

    public function index(): JsonResponse
    {
        try {
            $roleUsage = DB::table('role_has_permissions')
                ->groupBy('permission_id')
                ->pluck(DB::raw('COUNT(*)'), 'permission_id');

            $permissions = Permission::orderBy('name')
                ->get()
                ->map(fn($p) => [
                    'id'           => $p->id,
                    'name'         => $p->name,
                    'is_protected' => in_array($p->name, self::PROTECTED),
                    'roles_count'  => (int) ($roleUsage[$p->id] ?? 0),
                    'created_at'   => $p->created_at?->toISOString(),
                ]);

            return ApiResponse::success(['permissions' => $permissions]);
        } catch (Exception $e) {
            return $this->handleError($e, 'Failed to load permissions');
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'name' => 'required|string|max:100|regex:/^[a-z0-9._\-]+$/|unique:permissions,name',
            ]);

            $permission = Permission::create([
                'name'       => $data['name'],
                'guard_name' => 'web',
            ]);

            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

            return ApiResponse::success([
                'id'           => $permission->id,
                'name'         => $permission->name,
                'is_protected' => false,
                'roles_count'  => 0,
            ], 'Permission created', 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::error('Validation failed', $e->errors(), 422);
        } catch (Exception $e) {
            return $this->handleError($e, 'Failed to create permission');
        }
    }

    public function update(Request $request, Permission $permission): JsonResponse
    {
        try {
            if (in_array($permission->name, self::PROTECTED)) {
                return ApiResponse::error("'{$permission->name}' is a system permission and cannot be renamed.", null, 422);
            }

            $data = $request->validate([
                'name' => "required|string|max:100|regex:/^[a-z0-9._\-]+$/|unique:permissions,name,{$permission->id}",
            ]);

            $permission->update(['name' => $data['name']]);

            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

            return ApiResponse::success(
                ['id' => $permission->id, 'name' => $permission->name],
                'Permission renamed'
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::error('Validation failed', $e->errors(), 422);
        } catch (Exception $e) {
            return $this->handleError($e, 'Failed to rename permission');
        }
    }

    public function destroy(Permission $permission): JsonResponse
    {
        try {
            if (in_array($permission->name, self::PROTECTED)) {
                return ApiResponse::error("'{$permission->name}' is a system permission and cannot be deleted.", null, 422);
            }

            $roleCount = DB::table('role_has_permissions')
                ->where('permission_id', $permission->id)
                ->count();

            if ($roleCount > 0) {
                return ApiResponse::error(
                    "Cannot delete permission — it is assigned to {$roleCount} role(s). Remove it from all roles first.",
                    null,
                    422
                );
            }

            $permission->delete();

            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

            return ApiResponse::success(null, "Permission '{$permission->name}' deleted");
        } catch (Exception $e) {
            return $this->handleError($e, 'Failed to delete permission');
        }
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
