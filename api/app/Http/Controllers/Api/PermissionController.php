<?php

namespace App\Http\Controllers\Api;

use App\Models\Permission;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class PermissionController extends Controller
{
    /**
     * Lista todas as permissões em formato hierárquico
     */
    public function index(): JsonResponse
    {
        $permissions = Permission::with('children')
            ->whereNull('parent_id')
            ->get()
            ->map(function ($permission) {
                return [
                    'id' => $permission->id,
                    'name' => $permission->name,
                    'description' => $permission->description,
                    'children' => $permission->children->map(function ($child) {
                        return [
                            'id' => $child->id,
                            'name' => $child->name,
                            'description' => $child->description,
                        ];
                    }),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $permissions,
        ]);
    }
}
