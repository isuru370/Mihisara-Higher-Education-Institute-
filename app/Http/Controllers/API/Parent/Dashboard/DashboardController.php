<?php

namespace App\Http\Controllers\API\Parent\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Parent\Dashboard\DashboardRequest;
use App\Services\Parent\Dashboard\DashboardService;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function __construct(
        private DashboardService $dashboardService
    ) {}

    public function fetchDashboardData(
        DashboardRequest $request
    ): JsonResponse {

        $result = $this->dashboardService
            ->fetchDashboardData(
                $request->student_id
            );

        return response()->json($result);
    }
}