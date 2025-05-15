<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Colaborador;
use App\Models\Roteiro;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ColaboradorController extends Controller
{
    /**
     * Listar todos os colaboradores.
     */
    public function index()
    {
        $colaboradores = Colaborador::all();
        
        return response()->json([
            'success' => true,
            'data' => $colaboradores
        ]);
    }

    /**
     * Armazenar um novo colaborador.
     */
    public function store(Request $request)
    {
        try {
            $validated = Validator::make($request->all(), [
                'nome' => 'required|string|max:255',
                'email' => 'required|email|unique:colaboradors,email',
                'telefone' => 'nullable|string|max:20',
                'cpf' => 'nullable|string|max:14|unique:colaboradors,cpf',
                'tipo' => 'sometimes|in:amigo,familiar,colega,outro',
                'observacoes' => 'nullable|string',
            ])->validate();

            $colaborador = Colaborador::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Colaborador criado com sucesso',
                'data' => $colaborador
            ], Response::HTTP_CREATED);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro de validação',
                'errors' => $e->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar colaborador',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Exibir um colaborador específico.
     */
    public function show(string $id)
    {
        try {
            $colaborador = Colaborador::with('roteiros')->findOrFail($id);
            
            return response()->json([
                'success' => true,
                'data' => $colaborador
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Colaborador não encontrado'
            ], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Atualizar um colaborador específico.
     */
    public function update(Request $request, string $id)
    {
        try {
            $colaborador = Colaborador::findOrFail($id);

            $validated = Validator::make($request->all(), [
                'nome' => 'sometimes|string|max:255',
                'email' => 'sometimes|email|unique:colaboradors,email,' . $id,
                'telefone' => 'nullable|string|max:20',
                'cpf' => 'nullable|string|max:14|unique:colaboradors,cpf,' . $id,
                'tipo' => 'sometimes|in:amigo,familiar,colega,outro',
                'observacoes' => 'nullable|string',
            ])->validate();

            $colaborador->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Colaborador atualizado com sucesso',
                'data' => $colaborador
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro de validação',
                'errors' => $e->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Colaborador não encontrado ou erro ao atualizar',
                'error' => $e->getMessage()
            ], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Remover um colaborador específico.
     */
    public function destroy(string $id)
    {
        try {
            $colaborador = Colaborador::findOrFail($id);
            $colaborador->delete();

            return response()->json([
                'success' => true,
                'message' => 'Colaborador excluído com sucesso'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Colaborador não encontrado ou erro ao excluir',
                'error' => $e->getMessage()
            ], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Adicionar um colaborador a um roteiro.
     */
    public function adicionarAoRoteiro(Request $request)
    {
        try {
            $validated = Validator::make($request->all(), [
                'colaborador_id' => 'required|exists:colaboradors,id',
                'roteiro_id' => 'required|exists:roteiros,id',
                'contribuicao' => 'required|numeric|min:0',
            ])->validate();

            $roteiro = Roteiro::findOrFail($validated['roteiro_id']);
            
            // Verificar se o roteiro pertence ao usuário atual
            if ($roteiro->user_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Você não tem permissão para modificar este roteiro'
                ], Response::HTTP_FORBIDDEN);
            }

            $colaborador = Colaborador::findOrFail($validated['colaborador_id']);
            
            // Verificar se o colaborador já está no roteiro
            if ($roteiro->colaboradores()->where('colaborador_id', $colaborador->id)->exists()) {
                // Atualizar a contribuição
                $roteiro->colaboradores()->updateExistingPivot($colaborador->id, [
                    'contribuicao' => $validated['contribuicao']
                ]);
            } else {
                // Adicionar o colaborador ao roteiro
                $roteiro->colaboradores()->attach($colaborador->id, [
                    'contribuicao' => $validated['contribuicao']
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Colaborador adicionado ao roteiro com sucesso',
                'data' => $roteiro->load('colaboradores')
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro de validação',
                'errors' => $e->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao adicionar colaborador ao roteiro',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remover um colaborador de um roteiro.
     */
    public function removerDoRoteiro(Request $request)
    {
        try {
            $validated = Validator::make($request->all(), [
                'colaborador_id' => 'required|exists:colaboradors,id',
                'roteiro_id' => 'required|exists:roteiros,id',
            ])->validate();

            $roteiro = Roteiro::findOrFail($validated['roteiro_id']);
            
            // Verificar se o roteiro pertence ao usuário atual
            if ($roteiro->user_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Você não tem permissão para modificar este roteiro'
                ], Response::HTTP_FORBIDDEN);
            }

            $colaborador = Colaborador::findOrFail($validated['colaborador_id']);
            
            // Remover o colaborador do roteiro
            $roteiro->colaboradores()->detach($colaborador->id);

            return response()->json([
                'success' => true,
                'message' => 'Colaborador removido do roteiro com sucesso',
                'data' => $roteiro->load('colaboradores')
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro de validação',
                'errors' => $e->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao remover colaborador do roteiro',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
} 