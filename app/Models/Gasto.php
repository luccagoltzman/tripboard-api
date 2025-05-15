<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Gasto extends Model
{
    use HasFactory;

    /**
     * Os atributos que são atribuíveis em massa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'descricao',
        'valor',
        'data',
        'categoria',
        'comprovante_url',
        'aprovado',
        'roteiro_id',
        'user_id'
    ];

    /**
     * Os atributos que devem ser convertidos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'valor' => 'decimal:2',
        'data' => 'date',
        'aprovado' => 'boolean',
    ];

    /**
     * Obter o roteiro ao qual este gasto pertence.
     */
    public function roteiro(): BelongsTo
    {
        return $this->belongsTo(Roteiro::class);
    }

    /**
     * Obter o usuário que registrou este gasto.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
