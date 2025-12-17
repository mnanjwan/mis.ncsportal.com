<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoleController extends BaseController
{
    /**
     * List all roles
     */
    public function index(Request $request): JsonResponse
    {
        $roles = Role::all();

        return $this->successResponse($roles);
    }
}

