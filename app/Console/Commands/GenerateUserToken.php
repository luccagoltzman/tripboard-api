<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class GenerateUserToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:user-token {email?} {name=api_token}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gerar um token de acesso para um usuário específico';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        $tokenName = $this->argument('name');

        if (!$email) {
            $email = $this->ask('Qual é o email do usuário?');
        }

        try {
            $user = User::where('email', $email)->firstOrFail();
            
            // Remover tokens antigos
            $user->tokens()->delete();
            
            // Criar um novo token
            $token = $user->createToken($tokenName)->plainTextToken;
            
            $this->info('Token gerado com sucesso!');
            $this->info("Nome do usuário: {$user->name}");
            $this->info("Email do usuário: {$user->email}");
            $this->info("Token: {$token}");
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Erro ao gerar token: {$e->getMessage()}");
            Log::error('Erro ao gerar token de usuário:', ['error' => $e->getMessage()]);
            
            return Command::FAILURE;
        }
    }
}
