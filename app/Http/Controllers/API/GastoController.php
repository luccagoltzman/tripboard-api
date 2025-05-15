<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Gasto;
use App\Models\Roteiro;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class GastoController extends Controller
{
    /**
     * Listar todos os gastos de um roteiro.
     */
    public function index(string $roteiro_id)
    {
        try {
            $roteiro = Roteiro::where('user_id', Auth::id())->findOrFail($roteiro_id);
            $gastos = $roteiro->gastos()->with('user')->get();
            
            return response()->json([
                'success' => true,
                'data' => $gastos
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Roteiro não encontrado ou acesso não autorizado',
                'error' => $e->getMessage()
            ], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Armazenar um novo gasto.
     */
    public function store(Request $request, string $roteiro_id)
    {
        try {
            $roteiro = Roteiro::where('user_id', Auth::id())->findOrFail($roteiro_id);

            $validated = Validator::make($request->all(), [
                'descricao' => 'required|string|max:255',
                'valor' => 'required|numeric|min:0',
                'data' => 'required|date',
                'categoria' => 'required|in:hospedagem,alimentacao,transporte,passeios,compras,outros',
                'comprovante' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:2048',
                'aprovado' => 'sometimes|boolean',
            ])->validate();

            // Processar o upload do comprovante, se fornecido
            if ($request->hasFile('comprovante')) {
                $comprovantePath = $request->file('comprovante')->store('comprovantes', 'public');
                $validated['comprovante_url'] = Storage::url($comprovantePath);
            }

            // Adicionar o roteiro_id e user_id ao array validado
            $validated['roteiro_id'] = $roteiro->id;
            $validated['user_id'] = Auth::id();

            $gasto = Gasto::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Gasto registrado com sucesso',
                'data' => $gasto
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
                'message' => 'Erro ao registrar gasto',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Exibir um gasto específico.
     */
    public function show(string $roteiro_id, string $id)
    {
        try {
            $roteiro = Roteiro::where('user_id', Auth::id())->findOrFail($roteiro_id);
            $gasto = $roteiro->gastos()->with('user')->findOrFail($id);
            
            return response()->json([
                'success' => true,
                'data' => $gasto
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gasto não encontrado ou acesso não autorizado',
                'error' => $e->getMessage()
            ], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Atualizar um gasto específico.
     */
    public function update(Request $request, string $roteiro_id, string $id)
    {
        try {
            $roteiro = Roteiro::where('user_id', Auth::id())->findOrFail($roteiro_id);
            $gasto = $roteiro->gastos()->findOrFail($id);

            $validated = Validator::make($request->all(), [
                'descricao' => 'sometimes|string|max:255',
                'valor' => 'sometimes|numeric|min:0',
                'data' => 'sometimes|date',
                'categoria' => 'sometimes|in:hospedagem,alimentacao,transporte,passeios,compras,outros',
                'comprovante' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:2048',
                'aprovado' => 'sometimes|boolean',
            ])->validate();

            // Processar o upload do comprovante, se fornecido
            if ($request->hasFile('comprovante')) {
                // Remover o comprovante antigo, se existir
                if ($gasto->comprovante_url) {
                    $oldPath = str_replace('/storage/', '', $gasto->comprovante_url);
                    Storage::disk('public')->delete($oldPath);
                }

                $comprovantePath = $request->file('comprovante')->store('comprovantes', 'public');
                $validated['comprovante_url'] = Storage::url($comprovantePath);
            }

            $gasto->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Gasto atualizado com sucesso',
                'data' => $gasto
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
                'message' => 'Gasto não encontrado ou erro ao atualizar',
                'error' => $e->getMessage()
            ], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Remover um gasto específico.
     */
    public function destroy(string $roteiro_id, string $id)
    {
        try {
            $roteiro = Roteiro::where('user_id', Auth::id())->findOrFail($roteiro_id);
            $gasto = $roteiro->gastos()->findOrFail($id);

            // Remover o comprovante, se existir
            if ($gasto->comprovante_url) {
                $path = str_replace('/storage/', '', $gasto->comprovante_url);
                Storage::disk('public')->delete($path);
            }

            $gasto->delete();

            return response()->json([
                'success' => true,
                'message' => 'Gasto excluído com sucesso'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gasto não encontrado ou erro ao excluir',
                'error' => $e->getMessage()
            ], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Aprovar ou rejeitar um gasto.
     */
    public function aprovarGasto(Request $request, string $roteiro_id, string $id)
    {
        try {
            $roteiro = Roteiro::where('user_id', Auth::id())->findOrFail($roteiro_id);
            $gasto = $roteiro->gastos()->findOrFail($id);

            $validated = Validator::make($request->all(), [
                'aprovado' => 'required|boolean',
            ])->validate();

            $gasto->update(['aprovado' => $validated['aprovado']]);

            $mensagem = $validated['aprovado'] ? 'Gasto aprovado com sucesso' : 'Gasto rejeitado com sucesso';

            return response()->json([
                'success' => true,
                'message' => $mensagem,
                'data' => $gasto
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
                'message' => 'Gasto não encontrado ou erro ao aprovar/rejeitar',
                'error' => $e->getMessage()
            ], Response::HTTP_NOT_FOUND);
        }
    }
} 