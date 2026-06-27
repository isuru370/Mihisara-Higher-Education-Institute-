<?php

namespace App\Http\Controllers\API\Parent\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Parent\Auth\LoginRequest;
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
}
