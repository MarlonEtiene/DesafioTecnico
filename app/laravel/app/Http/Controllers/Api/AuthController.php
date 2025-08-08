<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'company_name' => 'required_without:company_id|string|max:255|unique:companies,name',
            'company_id' => 'required_without:company_name|exists:companies,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $companyId = null;

        if ($request->has('company_name')) {
            $company = Company::create([
                'name' => $request->company_name,
            ]);
            $companyId = $company->id;
        } else {
            $company = Company::find($request->company_id);
            $companyId = $request->company_id;
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'company_id' => $companyId,
        ]);

        $token = JWTAuth::fromUser($user);

        $user->load('company');

        return response()->json([
            'message' => 'Usuário registrado com sucesso',
            'user' => $user,
            'company' => $company,
            'token' => $token,
        ], 201);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if (!$token = auth('api')->attempt($validator->validated())) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->createNewToken($token);
    }

    public function logout()
    {
        auth('api')->logout();

        return response()->json(['message' => 'O usuário foi desconectado com sucesso']);
    }

    public function me()
    {
        $user = auth('api')->user();
        $user->load('company');

        return response()->json($user);
    }

    protected function createNewToken($token)
    {
        $user = auth('api')->user();
        $user->load('company');

        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
            'user' => $user,
        ]);
    }
}
