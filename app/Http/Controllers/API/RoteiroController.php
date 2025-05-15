<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Roteiro;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
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
            // Mapear campos do formato camelCase para snake_case
            $data = $request->all();
            
            // Log para debug
            Log::info('Dados recebidos (roteiro):', $data);
            
            // Nome
            if (isset($data['name']) && !isset($data['nome'])) {
                $data['nome'] = $data['name'];
            }
            
            // Descrição - corrigido para evitar auto-verificação
            if (isset($data['description']) && !isset($data['descricao'])) {
                $data['descricao'] = $data['description'];
            }
            
            // Data Início - corrigido para evitar auto-verificação
            if (isset($data['dataInicio']) && !isset($data['data_inicio'])) {
                $data['data_inicio'] = $data['dataInicio'];
            } elseif (isset($data['startDate']) && !isset($data['data_inicio'])) {
                $data['data_inicio'] = $data['startDate'];
            }
            
            // Data Fim - corrigido para evitar auto-verificação
            if (isset($data['dataFim']) && !isset($data['data_fim'])) {
                $data['data_fim'] = $data['dataFim'];
            } elseif (isset($data['endDate']) && !isset($data['data_fim'])) {
                $data['data_fim'] = $data['endDate'];
            }
            
            // Destino
            if (isset($data['destination']) && !isset($data['destino'])) {
                $data['destino'] = $data['destination'];
            }
            
            // Status
            if (isset($data['state']) && !isset($data['status'])) {
                $data['status'] = $data['state'];
            }
            
            // Orçamento Total
            if (isset($data['orcamentoTotal']) && !isset($data['orcamento_total'])) {
                $data['orcamento_total'] = $data['orcamentoTotal'];
            } elseif (isset($data['totalBudget']) && !isset($data['orcamento_total'])) {
                $data['orcamento_total'] = $data['totalBudget'];
            } elseif (isset($data['budget']) && !isset($data['orcamento_total'])) {
                $data['orcamento_total'] = $data['budget'];
            }
            
            // Log para debug
            Log::info('Dados processados (roteiro):', $data);

            $validated = Validator::make($data, [
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
            Log::error('Erro de validação (roteiro):', $e->errors());
            return response()->json([
                'success' => false,
                'message' => 'Erro de validação',
                'errors' => $e->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            Log::error('Erro ao criar roteiro:', $e->getMessage());
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

            // Mapear campos do formato camelCase para snake_case
            $data = $request->all();
            
            // Nome
            if (isset($data['name']) && !isset($data['nome'])) {
                $data['nome'] = $data['name'];
            }
            
            // Descrição - corrigido para evitar auto-verificação
            if (isset($data['description']) && !isset($data['descricao'])) {
                $data['descricao'] = $data['description'];
            }
            
            // Data Início - corrigido para evitar auto-verificação
            if (isset($data['dataInicio']) && !isset($data['data_inicio'])) {
                $data['data_inicio'] = $data['dataInicio'];
            } elseif (isset($data['startDate']) && !isset($data['data_inicio'])) {
                $data['data_inicio'] = $data['startDate'];
            }
            
            // Data Fim - corrigido para evitar auto-verificação
            if (isset($data['dataFim']) && !isset($data['data_fim'])) {
                $data['data_fim'] = $data['dataFim'];
            } elseif (isset($data['endDate']) && !isset($data['data_fim'])) {
                $data['data_fim'] = $data['endDate'];
            }
            
            // Destino
            if (isset($data['destination']) && !isset($data['destino'])) {
                $data['destino'] = $data['destination'];
            }
            
            // Status
            if (isset($data['state']) && !isset($data['status'])) {
                $data['status'] = $data['state'];
            }
            
            // Orçamento Total
            if (isset($data['orcamentoTotal']) && !isset($data['orcamento_total'])) {
                $data['orcamento_total'] = $data['orcamentoTotal'];
            } elseif (isset($data['totalBudget']) && !isset($data['orcamento_total'])) {
                $data['orcamento_total'] = $data['totalBudget'];
            } elseif (isset($data['budget']) && !isset($data['orcamento_total'])) {
                $data['orcamento_total'] = $data['budget'];
            }

            $validated = Validator::make($data, [
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