<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Atividade;
use App\Models\Roteiro;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AtividadeController extends Controller
{
    /**
     * Listar todas as atividades de um roteiro.
     */
    public function index(string $roteiro_id)
    {
        try {
            $roteiro = Roteiro::where('user_id', Auth::id())->findOrFail($roteiro_id);
            $atividades = $roteiro->atividades()->with('user')->get();
            
            return response()->json([
                'success' => true,
                'data' => $atividades
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao listar atividades:', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Roteiro não encontrado ou acesso não autorizado',
                'error' => $e->getMessage()
            ], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Armazenar uma nova atividade.
     */
    public function store(Request $request, string $roteiro_id)
    {
        try {
            $roteiro = Roteiro::where('user_id', Auth::id())->findOrFail($roteiro_id);

            // Mapear campos do formato camelCase para snake_case
            $data = $request->all();
            
            // Log para debug
            Log::info('Dados recebidos (atividade):', $data);
            
            // Verificar se existem chaves estranhas e converter para snake_case
            foreach ($data as $key => $value) {
                // Se a chave contém camelCase, converte para snake_case
                $snakeKey = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $key));
                if ($key !== $snakeKey && !isset($data[$snakeKey])) {
                    $data[$snakeKey] = $value;
                    Log::info("Convertido: {$key} -> {$snakeKey}");
                }
            }
            
            // Título
            if (isset($data['title']) && !isset($data['titulo'])) {
                $data['titulo'] = $data['title'];
            }
            
            // Descrição
            if (isset($data['description']) && !isset($data['descricao'])) {
                $data['descricao'] = $data['description'];
            }
            
            // Data Hora Início
            if (isset($data['dataHoraInicio']) && !isset($data['data_hora_inicio'])) {
                $data['data_hora_inicio'] = $data['dataHoraInicio'];
            } elseif (isset($data['startDateTime']) && !isset($data['data_hora_inicio'])) {
                $data['data_hora_inicio'] = $data['startDateTime'];
            } elseif (isset($data['startTime']) && !isset($data['data_hora_inicio'])) {
                $data['data_hora_inicio'] = $data['startTime'];
            } elseif (isset($data['start_time']) && !isset($data['data_hora_inicio'])) {
                $data['data_hora_inicio'] = $data['start_time'];
            } elseif (isset($data['inicio']) && !isset($data['data_hora_inicio'])) {
                $data['data_hora_inicio'] = $data['inicio'];
            }
            
            // Data Hora Fim
            if (isset($data['dataHoraFim']) && !isset($data['data_hora_fim'])) {
                $data['data_hora_fim'] = $data['dataHoraFim'];
            } elseif (isset($data['endDateTime']) && !isset($data['data_hora_fim'])) {
                $data['data_hora_fim'] = $data['endDateTime'];
            } elseif (isset($data['endTime']) && !isset($data['data_hora_fim'])) {
                $data['data_hora_fim'] = $data['endTime'];
            } elseif (isset($data['end_time']) && !isset($data['data_hora_fim'])) {
                $data['data_hora_fim'] = $data['end_time'];
            } elseif (isset($data['fim']) && !isset($data['data_hora_fim'])) {
                $data['data_hora_fim'] = $data['fim'];
            }
            
            // Local
            if (isset($data['location']) && !isset($data['local'])) {
                $data['local'] = $data['location'];
            }
            
            // Custo
            if (isset($data['cost']) && !isset($data['custo'])) {
                $data['custo'] = $data['cost'];
            }
            
            // Status
            if (isset($data['state']) && !isset($data['status'])) {
                $data['status'] = $data['state'];
            }
            
            // Log para debug
            Log::info('Dados processados (atividade):', $data);

            $validated = Validator::make($data, [
                'titulo' => 'required|string|max:255',
                'descricao' => 'nullable|string',
                'data_hora_inicio' => 'required|date',
                'data_hora_fim' => 'nullable|date|after_or_equal:data_hora_inicio',
                'local' => 'nullable|string|max:255',
                'custo' => 'nullable|numeric|min:0',
                'status' => 'sometimes|in:pendente,confirmada,concluida,cancelada',
            ])->validate();

            // Adicionar o roteiro_id e user_id ao array validado
            $validated['roteiro_id'] = $roteiro->id;
            $validated['user_id'] = Auth::id();

            $atividade = Atividade::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Atividade criada com sucesso',
                'data' => $atividade
            ], Response::HTTP_CREATED);
        } catch (ValidationException $e) {
            Log::error('Erro de validação (atividade):', $e->errors());
            return response()->json([
                'success' => false,
                'message' => 'Erro de validação',
                'errors' => $e->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            Log::error('Erro ao criar atividade:', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar atividade',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Exibir uma atividade específica.
     */
    public function show(string $roteiro_id, string $id)
    {
        try {
            $roteiro = Roteiro::where('user_id', Auth::id())->findOrFail($roteiro_id);
            $atividade = $roteiro->atividades()->with('user')->findOrFail($id);
            
            return response()->json([
                'success' => true,
                'data' => $atividade
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao buscar atividade:', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Atividade não encontrada ou acesso não autorizado',
                'error' => $e->getMessage()
            ], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Atualizar uma atividade específica.
     */
    public function update(Request $request, string $roteiro_id, string $id)
    {
        try {
            $roteiro = Roteiro::where('user_id', Auth::id())->findOrFail($roteiro_id);
            $atividade = $roteiro->atividades()->findOrFail($id);

            // Mapear campos do formato camelCase para snake_case
            $data = $request->all();
            
            // Verificar se existem chaves estranhas e converter para snake_case
            foreach ($data as $key => $value) {
                // Se a chave contém camelCase, converte para snake_case
                $snakeKey = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $key));
                if ($key !== $snakeKey && !isset($data[$snakeKey])) {
                    $data[$snakeKey] = $value;
                    Log::info("Convertido: {$key} -> {$snakeKey}");
                }
            }
            
            // Título
            if (isset($data['title']) && !isset($data['titulo'])) {
                $data['titulo'] = $data['title'];
            }
            
            // Descrição
            if (isset($data['description']) && !isset($data['descricao'])) {
                $data['descricao'] = $data['description'];
            }
            
            // Data Hora Início
            if (isset($data['dataHoraInicio']) && !isset($data['data_hora_inicio'])) {
                $data['data_hora_inicio'] = $data['dataHoraInicio'];
            } elseif (isset($data['startDateTime']) && !isset($data['data_hora_inicio'])) {
                $data['data_hora_inicio'] = $data['startDateTime'];
            } elseif (isset($data['startTime']) && !isset($data['data_hora_inicio'])) {
                $data['data_hora_inicio'] = $data['startTime'];
            } elseif (isset($data['start_time']) && !isset($data['data_hora_inicio'])) {
                $data['data_hora_inicio'] = $data['start_time'];
            } elseif (isset($data['inicio']) && !isset($data['data_hora_inicio'])) {
                $data['data_hora_inicio'] = $data['inicio'];
            }
            
            // Data Hora Fim
            if (isset($data['dataHoraFim']) && !isset($data['data_hora_fim'])) {
                $data['data_hora_fim'] = $data['dataHoraFim'];
            } elseif (isset($data['endDateTime']) && !isset($data['data_hora_fim'])) {
                $data['data_hora_fim'] = $data['endDateTime'];
            } elseif (isset($data['endTime']) && !isset($data['data_hora_fim'])) {
                $data['data_hora_fim'] = $data['endTime'];
            } elseif (isset($data['end_time']) && !isset($data['data_hora_fim'])) {
                $data['data_hora_fim'] = $data['end_time'];
            } elseif (isset($data['fim']) && !isset($data['data_hora_fim'])) {
                $data['data_hora_fim'] = $data['fim'];
            }
            
            // Local
            if (isset($data['location']) && !isset($data['local'])) {
                $data['local'] = $data['location'];
            }
            
            // Custo
            if (isset($data['cost']) && !isset($data['custo'])) {
                $data['custo'] = $data['cost'];
            }
            
            // Status
            if (isset($data['state']) && !isset($data['status'])) {
                $data['status'] = $data['state'];
            }

            $validated = Validator::make($data, [
                'titulo' => 'sometimes|string|max:255',
                'descricao' => 'nullable|string',
                'data_hora_inicio' => 'sometimes|date',
                'data_hora_fim' => 'nullable|date|after_or_equal:data_hora_inicio',
                'local' => 'nullable|string|max:255',
                'custo' => 'nullable|numeric|min:0',
                'status' => 'sometimes|in:pendente,confirmada,concluida,cancelada',
            ])->validate();

            $atividade->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Atividade atualizada com sucesso',
                'data' => $atividade
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro de validação',
                'errors' => $e->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar atividade:', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Atividade não encontrada ou erro ao atualizar',
                'error' => $e->getMessage()
            ], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Remover uma atividade específica.
     */
    public function destroy(string $roteiro_id, string $id)
    {
        try {
            $roteiro = Roteiro::where('user_id', Auth::id())->findOrFail($roteiro_id);
            $atividade = $roteiro->atividades()->findOrFail($id);
            $atividade->delete();

            return response()->json([
                'success' => true,
                'message' => 'Atividade excluída com sucesso'
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao excluir atividade:', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Atividade não encontrada ou erro ao excluir',
                'error' => $e->getMessage()
            ], Response::HTTP_NOT_FOUND);
        }
    }
} 