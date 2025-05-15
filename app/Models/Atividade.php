<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Atividade extends Model
{
    use HasFactory;

    /**
     * Os atributos que são atribuíveis em massa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'titulo',
        'descricao',
        'data_hora_inicio',
        'data_hora_fim',
        'local',
        'custo',
        'status',
        'roteiro_id',
        'user_id'
    ];

    /**
     * Os atributos que devem ser convertidos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'data_hora_inicio' => 'datetime',
        'data_hora_fim' => 'datetime',
        'custo' => 'decimal:2',
    ];

    /**
     * Obter o roteiro ao qual esta atividade pertence.
     */
    public function roteiro(): BelongsTo
    {
        return $this->belongsTo(Roteiro::class);
    }

    /**
     * Obter o usuário que registrou esta atividade.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
