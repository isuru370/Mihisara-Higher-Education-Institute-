<?php

namespace App\Http\Controllers\API\Parent\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Parent\Auth\ChangePasswordRequest;
use App\Http\Requests\Parent\Auth\LoginRequest;
use App\Http\Requests\Parent\Auth\LogoutRequest;
use App\Services\Parent\Auth\LoginService;
use Illuminate\Http\JsonResponse;

class ParentAuthController extends Controller
{
    public function __construct(
        private LoginService $loginService
    ) {}

    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->loginService->login(
            $request->username,
            $request->password
        );

        return response()->json(
            $result,
            $result['status'] ? 200 : 401
        );
    }

    public function logout(LogoutRequest $request): JsonResponse
    {
        $result = $this->loginService->logout(
            $request->student_id,
            $request->device_id
        );

        return response()->json(
            $result,
            $result['status'] ? 200 : 400
        );
    }

    public function changePassword(
        ChangePasswordRequest $request
    ): JsonResponse {

        $result = $this->loginService->changePassword(
            $request->validated()
        );

        return response()->json(
            $result,
            $result['status'] ? 200 : 400
        );
    }
}
