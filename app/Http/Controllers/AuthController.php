<?php

namespace App\Http\Controllers;

use App\Exceptions\AuthException;
use App\Http\Requests\LoginRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    public function __construct(private AuthService $authService) {}

    /**
     * @OA\Post(
     *     path="/api/auth",
     *     tags={"Auth"},
     *     summary="Autenticar usuario y generar token JWT",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "password"},
     *             @OA\Property(property="email", type="string", example="admin@example.com"),
     *             @OA\Property(property="password", type="string", example="secret")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Token JWT generado correctamente"),
     *     @OA\Response(response=401, description="Credenciales inválidas"),
     * )
     */
    public function store(LoginRequest $request): JsonResponse
    {
        try {
            return $this->authService->login($request->validated());
        } catch (AuthException $e) {
            return response()->json(['message' => $e->getMessage()], 401);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error inesperado' . $e], 500);
        }
    }

}
