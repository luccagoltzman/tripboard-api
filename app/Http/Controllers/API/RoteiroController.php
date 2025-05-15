<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Roteiro;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class RoteiroController extends Controller
{
    /**
     * Listar todos os roteiros do usuário atual.
     */
    public function index()
    {
        $roteiros = Auth::user()->roteiros()->with(['gastos'])->get();
        
        return response()->json([
            'success' => true,
            'data' => $roteiros
        ]);
    }

    /**
     * Armazenar um novo roteiro.
     */
    public function store(Request $request)
    {
        try {
            $validated = Validator::make($request->all(), [
                'nome' => 'required|string|max:255',
                'descricao' => 'nullable|string',
                'data_inicio' => 'required|date',
                'data_fim' => 'required|date|after_or_equal:data_inicio',
                'destino' => 'required|string|max:255',
                'status' => 'sometimes|in:planejado,em_andamento,concluido,cancelado',
                'orcamento_total' => 'required|numeric|min:0',
            ])->validate();

            $roteiro = Auth::user()->roteiros()->create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Roteiro criado com sucesso',
                'data' => $roteiro
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
                'message' => 'Erro ao criar roteiro',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Exibir um roteiro específico.
     */
    public function show(string $id)
    {
        try {
            $roteiro = Auth::user()->roteiros()->with(['gastos', 'colaboradores'])->findOrFail($id);
            
            return response()->json([
                'success' => true,
                'data' => $roteiro
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Roteiro não encontrado'
            ], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Atualizar um roteiro específico.
     */
    public function update(Request $request, string $id)
    {
        try {
            $roteiro = Auth::user()->roteiros()->findOrFail($id);

            $validated = Validator::make($request->all(), [
                'nome' => 'sometimes|string|max:255',
                'descricao' => 'nullable|string',
                'data_inicio' => 'sometimes|date',
                'data_fim' => 'sometimes|date|after_or_equal:data_inicio',
                'destino' => 'sometimes|string|max:255',
                'status' => 'sometimes|in:planejado,em_andamento,concluido,cancelado',
                'orcamento_total' => 'sometimes|numeric|min:0',
            ])->validate();

            $roteiro->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Roteiro atualizado com sucesso',
                'data' => $roteiro
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
                'message' => 'Roteiro não encontrado ou erro ao atualizar',
                'error' => $e->getMessage()
            ], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Remover um roteiro específico.
     */
    public function destroy(string $id)
    {
        try {
            $roteiro = Auth::user()->roteiros()->findOrFail($id);
            $roteiro->delete();

            return response()->json([
                'success' => true,
                'message' => 'Roteiro excluído com sucesso'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Roteiro não encontrado ou erro ao excluir',
                'error' => $e->getMessage()
            ], Response::HTTP_NOT_FOUND);
        }
    }
} 