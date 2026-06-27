<?php

namespace App\Http\Controllers\API\Parent\FCM;

use App\Http\Controllers\Controller;
use App\Http\Requests\Parent\FCM\SaveFcmTokenRequest;
use App\Services\Parent\FCM\FcmTokenService;
use Illuminate\Http\JsonResponse;

class FcmTokenController extends Controller
{
    public function __construct(
        private FcmTokenService $service
    ) {}

    public function store(
        SaveFcmTokenRequest $request
    ): JsonResponse {

        $this->service->save(
            $request->validated()
        );

        return response()->json([
            'status' => true,
            'message' => 'FCM token saved successfully.',
        ]);
    }

    public function logout(
        SaveFcmTokenRequest $request
    ): JsonResponse {

        $this->service->logout(
            $request->validated()
        );

        return response()->json([
            'status' => true,
            'message' => 'Device logged out successfully.',
        ]);
    }
}
