<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Registrar um novo usuário.
     */
    public function register(Request $request)
    {
        try {
            $validated = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8|confirmed',
            ])->validate();

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Usuário registrado com sucesso',
                'data' => [
                    'user' => $user,
                    'access_token' => $token,
                    'token_type' => 'Bearer',
                ],
            ], Response::HTTP_CREATED);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro de validação',
                'errors' => $e->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao registrar usuário',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Autenticar um usuário e gerar token.
     */
    public function login(Request $request)
    {
        try {
            $validated = Validator::make($request->all(), [
                'email' => 'required|string|email',
                'password' => 'required|string',
            ])->validate();

            if (!Auth::attempt($validated)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Credenciais inválidas',
                ], Response::HTTP_UNAUTHORIZED);
            }

            $user = User::where('email', $validated['email'])->firstOrFail();
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Login realizado com sucesso',
                'data' => [
                    'user' => $user,
                    'access_token' => $token,
                    'token_type' => 'Bearer',
                ],
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro de validação',
                'errors' => $e->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao realizar login',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Obter informações do usuário autenticado.
     */
    public function me()
    {
        return response()->json([
            'success' => true,
            'data' => Auth::user(),
        ]);
    }

    /**
     * Invalidar o token e deslogar o usuário.
     */
    public function logout()
    {
        Auth::user()->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout realizado com sucesso',
        ]);
    }
} 