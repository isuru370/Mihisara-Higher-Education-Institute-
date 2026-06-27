<?php

namespace App\Services\Client;

use App\Models\Institute;
use App\Models\MobileApp;
use Illuminate\Http\JsonResponse;

class InstituteInventoryService
{
    public function getAll(): JsonResponse
    {
        $institutes = Institute::with('mobileApp')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Institutes retrieved successfully',
            'data' => $institutes
        ]);
    }

    public function getById(int $id): JsonResponse
    {
        $institute = Institute::with('mobileApp')
            ->find($id);

        if (!$institute) {
            return response()->json([
                'success' => false,
                'message' => 'Institute not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Institute retrieved successfully',
            'data' => $institute
        ]);
    }

    public function getDashboardData(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'total_institutes' => Institute::count(),
                'active_institutes' => Institute::where('status', 'active')->count(),
                'inactive_institutes' => Institute::where('status', 'inactive')->count(),
                'suspended_institutes' => Institute::where('status', 'suspended')->count(),
                'total_apps' => MobileApp::count(),
                'active_apps' => MobileApp::where('status', 'active')->count(),
            ]
        ]);
    }

    public function getAppDetails(int $instituteId): JsonResponse
    {
        $app = MobileApp::where('institute_id', $instituteId)
            ->first();

        if (!$app) {
            return response()->json([
                'success' => false,
                'message' => 'Mobile app not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $app
        ]);
    }

    public function getUpdateInfo(string $packageName): JsonResponse
    {
        $app = MobileApp::where('package_name', $packageName)
            ->first();

        if (!$app) {
            return response()->json([
                'success' => false,
                'message' => 'Application not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'latest_version' => $app->latest_version ?? $app->current_version,
                'build_number' => $app->build_number,
                'force_update' => $app->force_update,
                'release_notes' => $app->release_notes,
                'apk_url' => $app->apk_url,
                'min_supported_version' => $app->min_supported_version,
            ]
        ]);
    }
}