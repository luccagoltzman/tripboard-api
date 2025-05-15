<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
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
            // Mapear campos em português para inglês
            $data = $request->all();
            
            // Depurar os dados recebidos
            Log::info('Dados recebidos:', $data);
            
            // Verificar campos alternativos e mapear para nomes esperados
            if (isset($data['nome']) && !isset($data['name'])) {
                $data['name'] = $data['nome'];
            }
            
            if (isset($data['senha']) && !isset($data['password'])) {
                $data['password'] = $data['senha'];
            }
            
            // Verificar todos os possíveis nomes para confirmação de senha
            $confirmationFields = [
                'confirmar_senha',
                'confirmacao_senha',
                'senha_confirmacao',
                'senha_confirmar',
                'confirmacao',
                'confirmar',
                'senha_confirmada',
                'password_confirm',
                'confirm_password',
                'passwordConfirmation',
                'passwordConfirm',
                'senha_confirmation'
            ];
            
            foreach ($confirmationFields as $field) {
                if (isset($data[$field]) && !isset($data['password_confirmation'])) {
                    $data['password_confirmation'] = $data[$field];
                    break;
                }
            }
            
            // Se nenhum campo de confirmação for encontrado, usar o próprio password
            if (!isset($data['password_confirmation']) && isset($data['password'])) {
                $data['password_confirmation'] = $data['password'];
            }
            
            Log::info('Dados processados:', $data);

            $validated = Validator::make($data, [
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
            Log::error('Erro de validação:', $e->errors());
            return response()->json([
                'success' => false,
                'message' => 'Erro de validação',
                'errors' => $e->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            Log::error('Erro ao registrar:', $e->getMessage());
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
            // Mapear campos em português para inglês
            $data = $request->all();
            
            // Verificar campos alternativos e mapear para nomes esperados
            if (isset($data['senha']) && !isset($data['password'])) {
                $data['password'] = $data['senha'];
            }

            $validated = Validator::make($data, [
                'email' => 'required|string|email',
                'password' => 'required|string',
            ])->validate();

            if (!Auth::attempt(['email' => $validated['email'], 'password' => $validated['password']])) {
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
        if (Auth::user()) {
            Auth::user()->tokens()->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Logout realizado com sucesso',
        ]);
    }
} 