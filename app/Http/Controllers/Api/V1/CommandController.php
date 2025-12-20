<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Command;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommandController extends BaseController
{
    /**
     * List all commands
     */
    public function index(Request $request): JsonResponse
    {
        $query = Command::where('is_active', true)->with('zone');

        $commands = $query->get();

        return $this->successResponse($commands);
    }

    /**
     * Get command details
     */
    public function show(Request $request, $id): JsonResponse
    {
        $command = Command::with(['areaController', 'officers'])->findOrFail($id);

        return $this->successResponse($command);
    }
}

