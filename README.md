# TripBoard API

TripBoard é uma aplicação para gerenciamento de viagens, permitindo controle de roteiros, colaboradores e gastos.

## Requisitos

- PHP 8.1+
- Composer
- MySQL 5.7+ ou MariaDB 10.3+
- Extensões PHP: BCMath, Ctype, Fileinfo, JSON, Mbstring, OpenSSL, PDO, Tokenizer, XML

## Instalação

1. Clone o repositório:
```bash
git clone https://github.com/luccagoltzman/tripboard-api.git
cd tripboard-api
```

2. Instale as dependências:
```bash
composer install
```

3. Copie o arquivo de ambiente e gere a chave da aplicação:
```bash
cp .env.example .env
php artisan key:generate
```

4. Configure o banco de dados MySQL no arquivo `.env`:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tripboard
DB_USERNAME=root
DB_PASSWORD=sua_senha
```

5. Execute as migrações para criar as tabelas:
```bash
php artisan migrate
```

6. Instale o Laravel Sanctum para autenticação:
```bash
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
```

7. Crie um link simbólico para o armazenamento:
```bash
php artisan storage:link
```

8. Inicie o servidor de desenvolvimento:
```bash
php artisan serve
```

## Endpoints da API

### Autenticação
- `POST /api/auth/register` - Registrar novo usuário
- `POST /api/auth/login` - Login de usuário
- `GET /api/auth/me` - Obter perfil do usuário (requer autenticação)
- `POST /api/auth/logout` - Logout (requer autenticação)

### Roteiros
- `GET /api/roteiros` - Listar todos os roteiros
- `POST /api/roteiros` - Criar um novo roteiro
- `GET /api/roteiros/{id}` - Obter detalhes de um roteiro
- `PUT /api/roteiros/{id}` - Atualizar um roteiro
- `DELETE /api/roteiros/{id}` - Excluir um roteiro

### Colaboradores
- `GET /api/colaboradores` - Listar todos os colaboradores
- `POST /api/colaboradores` - Criar um novo colaborador
- `GET /api/colaboradores/{id}` - Obter detalhes de um colaborador
- `PUT /api/colaboradores/{id}` - Atualizar um colaborador
- `DELETE /api/colaboradores/{id}` - Excluir um colaborador
- `POST /api/colaboradores/adicionar-roteiro` - Adicionar colaborador a um roteiro
- `POST /api/colaboradores/remover-roteiro` - Remover colaborador de um roteiro

### Gastos
- `GET /api/roteiros/{roteiro_id}/gastos` - Listar gastos de um roteiro
- `POST /api/roteiros/{roteiro_id}/gastos` - Criar um novo gasto
- `GET /api/roteiros/{roteiro_id}/gastos/{id}` - Obter detalhes de um gasto
- `PUT /api/roteiros/{roteiro_id}/gastos/{id}` - Atualizar um gasto
- `DELETE /api/roteiros/{roteiro_id}/gastos/{id}` - Excluir um gasto
- `PATCH /api/roteiros/{roteiro_id}/gastos/{id}/aprovar` - Aprovar ou rejeitar um gasto

## Licença

Este projeto está licenciado sob a [MIT license](https://opensource.org/licenses/MIT).
