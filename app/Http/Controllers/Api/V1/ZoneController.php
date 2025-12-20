<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Zone;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ZoneController extends BaseController
{
    /**
     * List all zones
     */
    public function index(Request $request): JsonResponse
    {
        $query = Zone::where('is_active', true)->orderBy('name');

        $zones = $query->get();

        return $this->successResponse($zones);
    }

    /**
     * Get zone details with commands
     */
    public function show(Request $request, $id): JsonResponse
    {
        $zone = Zone::with(['activeCommands'])->findOrFail($id);

        return $this->successResponse($zone);
    }
}
