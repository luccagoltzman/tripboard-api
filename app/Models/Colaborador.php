<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Colaborador extends Model
{
    use HasFactory;

    /**
     * Os atributos que são atribuíveis em massa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nome',
        'email',
        'telefone',
        'cpf',
        'tipo',
        'observacoes'
    ];

    /**
     * Obter os roteiros em que o colaborador participa.
     */
    public function roteiros(): BelongsToMany
    {
        return $this->belongsToMany(Roteiro::class, 'colaborador_roteiro')
                    ->withPivot('contribuicao')
                    ->withTimestamps();
    }

    /**
     * Calcular o total de contribuições do colaborador.
     */
    public function getTotalContribuicoesAttribute(): float
    {
        return $this->roteiros()->sum('contribuicao');
    }
}
