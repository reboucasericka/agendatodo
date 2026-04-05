# BossFlowX

Sistema web de gestão de tarefas (To-Do), com autenticação e interface responsiva.

---

## Visão geral

Aplicação **Laravel** com frontend **Vue 3** e **Inertia.js**, estilizada com **Tailwind CSS**. O utilizador pode criar, filtrar, editar, concluir e eliminar tarefas; cada conta vê apenas os seus dados.

---

## Stack técnica

| Camada        | Tecnologia                          |
| ------------- | ----------------------------------- |
| Backend       | Laravel 13, PHP 8.3+                |
| Frontend      | Vue 3, Inertia.js                   |
| Estilos       | Tailwind CSS                        |
| Base de dados | MySQL (configurável via `.env`)     |
| Autenticação  | Laravel Fortify                     |
| Testes        | Pest (integração / feature)         |

---

## Funcionalidades

### Tarefas

- Criar tarefa com **título** (obrigatório), **descrição** (opcional), **data de vencimento** e **prioridade** (baixa, média, alta).
- Editar os campos acima e **marcar como concluída** ou **eliminar** a tarefa.

### Listagem e filtros

- Estado: pendente, concluída ou todas.
- Prioridade e **intervalo de datas** de vencimento.
- **Pesquisa** no título e na descrição.
- **Vista semanal** (tarefas com vencimento na semana atual).

### Segurança

- Rotas protegidas por autenticação.
- Tarefas associadas ao `user_id`; atualização e eliminação validam propriedade (**403** se não for o dono).

### Testes

- Cobertura de CRUD, filtros e isolamento entre utilizadores (`tests/Feature/TasksTest.php`).

---

## Estrutura relevante

| Caminho | Descrição |
| ------- | --------- |
| `app/Models/TaskModel.php` | Modelo Eloquent e regras de estado |
| `app/Http/Controllers/TaskController.php` | Listagem, filtros e ações HTTP |
| `resources/js/pages/Tasks/TaskApp.vue` | Página principal da app de tarefas |
| `resources/js/features/Tasks/` | Componentes (formulário, calendário, etc.) |

---

## Requisitos

- PHP **8.3+**
- Composer
- Node.js e npm (para Vite / frontend)
- Servidor MySQL (ou outro driver suportado pelo Laravel, conforme `.env`)

---

## Como executar

### 1. Clonar e entrar na pasta

```bash
git clone <url-do-repositorio>
cd bossflowx
```

### 2. Dependências PHP e Node

```bash
composer install
npm install
```

### 3. Ambiente e chave da aplicação

```bash
cp .env.example .env
php artisan key:generate
```

Editar o ficheiro `.env` e configurar a ligação à base de dados (`DB_*`).

### 4. Migrações

```bash
php artisan migrate
```

### 5. Servidor de desenvolvimento

Em **dois terminais** (ou com `concurrently`, se estiver no `package.json`):

```bash
php artisan serve
```

```bash
npm run dev
```

Abrir o URL indicado pelo Artisan (por exemplo `http://127.0.0.1:8000`).

### Testes

```bash
php artisan test
```

---

## Build para produção

```bash
npm run build
```

Garantir que o ambiente de produção aponta para os assets compilados e que `APP_ENV` / `APP_DEBUG` estão corretos.

---

## Notas

- A camada SPA usa **Inertia.js**: o Laravel devolve respostas que o Vue renderiza, sem API REST separada para estas páginas.
- Autenticação e fluxos de sessão são tratados pelo **Fortify**, alinhado às convenções Laravel.

---

**Autor:** Ericka Rebouças desenvolvido no âmbito do estágio na **Inovcorp**.
