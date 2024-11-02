<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponseHelper;
use App\Http\Requests\AuthUserRequest;
use App\Http\Requests\RegisterUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Log;

class JWTAuthController extends Controller
{
    public function register(RegisterUserRequest $request)
    {
        try {
            $data = $request->validated();
            $data['password'] = bcrypt($data['password']);
            DB::beginTransaction();
            $user = User::create($data);
            if (!$user) {
                DB::rollBack();
                return ApiResponseHelper::sendError(null, 'Error al crear el usuario', 500);
            }
            $token = JWTAuth::fromUser($user);
            DB::commit();
            return ApiResponseHelper::sendResponse([
                'user' => new UserResource($user),
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => JWTAuth::factory()->getTTL() * 60
            ], 'Usuario creado exitosamente', 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponseHelper::rollback($e, 'Error en el proceso de creación del usuario');
        }
    }

    public function login(AuthUserRequest $request)
    {
        $credentials = $request->only('dni', 'password');



        try {
            if (! $token = JWTAuth::attempt($credentials)) {
                return ApiResponseHelper::sendError('Invalid credentials', 401);
            }

            // Get the authenticated user.
            $user = auth()->user();

            // (optional) Attach the role to the token.
            $token = JWTAuth::claims(['role' => $user->role])->fromUser($user);

            return ApiResponseHelper::sendResponse([
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => $user
            ], 'Autenticación exitosa');
        } catch (JWTException $e) {
            return ApiResponseHelper::sendError('Could not create token', 500);
        }
    }

    public function logout()
    {
        auth()->logout(true);
        return ApiResponseHelper::sendResponse(null, 'Successfully logged out', 200);
    }

    public function refresh()
    {
        return ApiResponseHelper::sendResponse([
            'access_token' => JWTAuth::refresh(),
            'token_type' => 'Bearer',
        ], 'Token refreshed successfully');
    }
}