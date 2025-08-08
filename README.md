# Guia de Instalação Completa

Este documento contém o guia passo a passo para configurar e iniciar o projeto `DesafioTecnico` usando Docker, Docker Compose.

### Visão Geral

Este guia foi revisado e validado para resolver problemas comuns de ordem de execução, permissões e dependências. Siga os passos na sequência exata para garantir uma instalação sem problemas.

**Observação Importante:** Caso você já tenha tentado executar o projeto anteriormente e encontrado problemas, é **fundamental** que você execute o **Passo 1** para limpar o ambiente e evitar conflitos com contêineres e volumes antigos.

### Recursos Implementados

O projeto atende a todos os requisitos funcionais e técnicos solicitados, incluindo os itens desejáveis (bônus), conforme o seguinte resumo:

* **Autenticação**: Registro e login com autenticação JWT.

* **Multitenancy**: Suporte a múltiplas empresas, com isolamento de dados de tarefas e usuários.

* **Gerenciamento de Tarefas**: CRUD completo para tarefas, com campos como título, descrição, status, prioridade e data limite.

* **Notificações por E-mail**: Envio de e-mails para notificações ao criar ou concluir tarefas.

* **Comando Interativo para a Primeira Empresa**: Um comando `artisan` interativo para cadastrar a primeira empresa e o primeiro usuário de forma rápida.

* **Filas (Redis)**: Implementação de filas para o envio assíncrono de e-mails, utilizando o Redis como driver de fila.

* **Exportação para CSV**: Funcionalidade para exportar a lista de tarefas em um arquivo CSV, demonstrando a geração de arquivos.

* **Docker**: O projeto é completamente containerizado, utilizando Docker para facilitar a configuração e execução.

### Passo 1: Limpeza do Ambiente (Se Necessário)

Execute este comando para remover todos os contêineres e volumes associados a uma instalação anterior.

```
USER_ID=$(id -u) GROUP_ID=$(id -g) docker compose down -v
```

### Passo 2: Clonar o Repositório e Configurar `.env`

Inicie clonando o projeto e configurando o arquivo de variáveis de ambiente.

```
# Clona o repositório
git clone git@github.com:MarlonEtiene/DesafioTecnico.git

# Navega para o diretório do projeto
cd DesafioTecnico

# Copia o arquivo de exemplo para .env
cp app/laravel/.env.example app/laravel/.env
```

*Abra o arquivo `app/laravel/.env` em seu editor de texto preferido e faça as alterações necessárias.*

**Aviso:** Por padrão, a configuração de e-mail está definida para o modo `log`, o que significa que todos os e-mails enviados pela aplicação serão registrados no arquivo de log do Laravel, em vez de serem enviados de fato. Para habilitar o envio de e-mails, é necessário configurar as variáveis `MAIL_MAILER`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME` e `MAIL_PASSWORD` no arquivo `.env` com as credenciais de um serviço de e-mail.

**Atenção:** É importante notar que a fila de trabalho (`queue:work`), mesmo que esteja rodando, não processará e-mails de forma assíncrona se o `MAIL_MAILER` estiver definido como `log`. Para que a fila funcione corretamente com e-mails, você precisa de um serviço de e-mail configurado.

### Passo 3: Construir e Iniciar os Contêineres

Este comando irá construir a imagem do serviço `app` e iniciar todos os contêineres, ignorando o cache para garantir que a imagem mais recente seja utilizada.

```
USER_ID=$(id -u) GROUP_ID=$(id -g) docker compose up --build --force-recreate -d
```

### Passo 4: Criar Pastas e Ajustar Permissões

Com os contêineres em execução, é essencial garantir que as permissões e a estrutura de pastas estejam corretas para o `Laravel`.

```
docker exec laravel8-php73 bash -c "mkdir -p /var/www/html/storage/framework/{sessions,views,cache} /var/www/html/bootstrap/cache && chmod -R 777 /var/www/html/storage"
```

### Passo 5: Instalar Dependências e Compilar Assets

Agora é o momento de instalar as dependências do back-end (Composer) e compilar os assets do front-end (npm).

**Instalar dependências do Composer:**

```
docker exec laravel8-php73 composer install
```

**Instalar e compilar os assets do frontend:**

```
docker exec vue2-node npm install
docker exec vue2-node npm run prod
```

### Passo 6: Executar os Comandos de Runtime do Laravel

Finalmente, execute os comandos do Laravel para configurar o ambiente e o banco de dados.

**Publicar o provedor JWT:**

```
docker exec laravel8-php73 php artisan vendor:publish --provider="Tymon\JWTAuth\Providers\LaravelServiceProvider"
```

**Gerar a chave JWT:**

```
docker exec laravel8-php73 php artisan jwt:secret
```

**Gerar a chave da aplicação:**

```
docker exec laravel8-php73 php artisan key:generate
```

**Rodar as migrações e seeders:**

```
docker exec laravel8-php73 php artisan migrate --seed
```

**Criar a primeira empresa:**
*Este é um comando interativo, por isso a flag `-it` é essencial.*

```
docker exec -it laravel8-php73 php artisan company:create-first
```

**Iniciar a fila de trabalho (em segundo plano):**

```
docker exec -d laravel8-php73 php artisan queue:work
```

Após seguir todos esses passos, sua aplicação estará configurada e funcionando.
