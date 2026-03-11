# CostaoTic

Projeto PHP com páginas em `src/`.

Contexto
- Projeto desenvolvido originalmente para um resort.
- O desenvolvimento foi interrompido por falta de avanço/definições da equipe.

## Requisitos
- PHP 8.1+ instalado e acessível no terminal (`php -v`)
- Opcional: Node.js 18+ se for compilar CSS/JS via Gulp

## Executar servidor de desenvolvimento
1. Abra um terminal na raiz do projeto
2. Rode:

```bash
php -S localhost:8000 -t src
```

- Acesse `http://localhost:8000/`
- Para trocar a porta, ajuste o comando: `php -S localhost:8080 -t src`

## Banco de Dados (MySQL/MariaDB — CAMP/XAMPP)
- O banco recomendado para este projeto é MySQL/MariaDB (CAMP/XAMPP).
- Modo demo: o app roda sem banco (BANCO_MOCK=1 no `.env`) usando JSON em `database/mock/`.
- Schema MySQL/MariaDB pronto em `database/schema_mysql.sql`.

### MySQL/MariaDB — Passo a passo (configuração)
1. Inicie o MySQL/MariaDB no seu CAMP/XAMPP.
2. Crie o banco e importe o schema:
   ```sql
   -- no cliente mysql
   CREATE DATABASE costao_control CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   USE costao_control;
   SOURCE /c/Users/willi/Downloads/CostaoControlNEW/database/schema_mysql.sql;
   ```
3. Configure o `.env` para usar o banco (desligar mock):
   ```
   BANCO_MOCK=0
   BANCO_DRIVER=mysql
   DB_DSN="mysql:host=127.0.0.1;dbname=costao_control;charset=utf8mb4"
   DB_USER="root"
   DB_PASS=""
   APP_NAME="Costão Control"
   APP_ENV=local
   APP_DEBUG=true
   APP_URL=http://localhost:8000
   ```
   - Defina DB_USER/DB_PASS conforme sua instalação.
   - Observação: não versionamos credenciais; mantenha-as apenas no `.env`.

4. Inicie o servidor PHP:
   ```bash
   php -S localhost:8000 -t src
   ```
5. Acesse o sistema em `http://localhost:8000/`.

### Como tirar o mock e usar o banco
- Altere `BANCO_MOCK` para `0` no `.env`.
- Preencha `BANCO_DRIVER=mysql`, `DB_DSN`, `DB_USER`, `DB_PASS`.
- Garanta que o schema foi importado (arquivo `database/schema_mysql.sql`).
- Reinicie o servidor PHP.

## Login (demo)
- E-mail: `admin@empresa.com`
- Senha: `mudar123`
- Autenticação local (mock) ativa para demonstração

## Estrutura do Projeto
- `src/` Páginas PHP e parciais
  - `partials/` Topbar, navegação, offcanvas de configurações
  - `services/` Camada de dados e utilitários
    - `banco.php` (camada de acesso – mock JSON ou endpoint real)
    - `store.php` (regras de negócio)
    - `auth.php` (login e cadastro)
  - `assets/` CSS/JS/Imagens
- `database/`
  - `mock/` Dados de demonstração (JSON)
  - `schema_mysql.sql` (MySQL/MariaDB)
  - `schema_banco.sql` (PostgreSQL)
- `.env` Configurações de ambiente (não versionado)

## Modo Demo (Mock)
- Com `BANCO_MOCK=1`, os dados são lidos/escritos em `database/mock/*.json`.
- Uploads de arquivos são gravados em `src/assets/uploads/`.
- Ideal para testar a interface sem infraestrutura.

## Produção (Banco Real)
- Defina `BANCO_MOCK=0` no `.env`.
- Implemente/integre o conector real no `services/banco.php` (mantendo a mesma API: `banco_get`, `banco_insert`, etc.).
- Evite expor chaves. Use variáveis de ambiente e um cofre/secret manager quando necessário.

## Recursos já prontos
- Inventário de equipamentos com categorias, filtros e edição.
- Termos (Entrega/Devolução) com anexos de assinaturas.
- Checklists vinculados a equipamentos.
- Log de atividades (offcanvas no topo).
- Perfil e atualização de avatar (uploads em `assets/uploads/`).

## Dados de Demonstração
- Equipamentos/categorias/colaboradores iniciais em `database/mock/`.
- Termos, atividades, checklists e anexos de exemplo adicionados.

## Scripts de Build (opcional)
Se precisar compilar SCSS/JS do tema:
```bash
npm install
gulp
```
Os arquivos compilados já estão presentes; build só é necessário ao alterar fontes.

## Notas de Segurança
- `.env` está no `.gitignore`.
- Todas as credenciais externas (LDAP, e-mail, serviços de terceiros) foram removidas.
- Não commitamos chaves de API. Configure-as apenas no ambiente.

## Estrutura
- `src/` páginas PHP, parciais e assets compilados
- `src/assets/css/` estilos (inclui `dashboard-ia-custom.css` overrides)
- `src/assets/js/` scripts de tema e configurações

## Build de assets (opcional)
Se precisar compilar SCSS/JS do tema:

```bash
npm install
gulp
```

> Observação: o projeto atual serve os arquivos já compilados. O build só é necessário ao alterar fontes SCSS/JS.

## Dicas
- Para ambiente móvel, o menu pode entrar em modo `hidden` (controlado por `src/assets/js/config.js`).
- A logo da sidebar está centralizada nos modos `sm-hover` e `sm-hover-active` via `dashboard-ia-custom.css`.
