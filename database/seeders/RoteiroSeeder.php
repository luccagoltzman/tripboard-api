<?php

namespace Database\Seeders;

use App\Models\Roteiro;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoteiroSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::where('email', 'teste@example.com')->first();
        
        if ($user) {
            Roteiro::create([
                'nome' => 'Viagem para São Paulo',
                'descricao' => 'Viagem a negócios e turismo',
                'data_inicio' => '2025-07-10',
                'data_fim' => '2025-07-15',
                'destino' => 'São Paulo, SP',
                'status' => 'planejado',
                'orcamento_total' => 5000.00,
                'user_id' => $user->id
            ]);
        }
    }
}
