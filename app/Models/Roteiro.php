<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Roteiro extends Model
{
    use HasFactory;

    /**
     * Os atributos que são atribuíveis em massa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nome',
        'descricao',
        'data_inicio',
        'data_fim',
        'destino',
        'status',
        'orcamento_total',
        'user_id'
    ];

    /**
     * Os atributos que devem ser convertidos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'data_inicio' => 'date',
        'data_fim' => 'date',
        'orcamento_total' => 'decimal:2',
    ];

    /**
     * Obter o usuário proprietário do roteiro.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Obter os colaboradores participantes do roteiro.
     */
    public function colaboradores(): BelongsToMany
    {
        return $this->belongsToMany(Colaborador::class, 'colaborador_roteiro')
                    ->withPivot('contribuicao')
                    ->withTimestamps();
    }

    /**
     * Obter os gastos associados ao roteiro.
     */
    public function gastos(): HasMany
    {
        return $this->hasMany(Gasto::class);
    }

    /**
     * Calcular o total gasto até o momento.
     */
    public function getTotalGastoAttribute(): float
    {
        return $this->gastos()->sum('valor');
    }

    /**
     * Calcular o saldo disponível.
     */
    public function getSaldoDisponiveAttribute(): float
    {
        return $this->orcamento_total - $this->total_gasto;
    }
}
