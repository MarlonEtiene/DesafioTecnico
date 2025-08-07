### `README.md`

# Projeto Desafio Técnico

Este projeto é uma aplicação web construída com Laravel e Vue.js, gerenciada por containers Docker. Este guia irá auxiliá-lo na instalação e execução do projeto em sua máquina local.

## Pré-requisitos

Certifique-se de que você tem o seguinte software instalado em sua máquina:

  - **Docker**
  - **Docker Compose**
  - **Git**

-----

## 🚀 Instalação e Configuração

Siga os passos abaixo para preparar e rodar a aplicação:

### Passo 1: Clonar o Repositório

Abra seu terminal e clone o repositório do projeto para a sua máquina:

```bash
git clone git@github.com:MarlonEtiene/DesafioTecnico.git
cd [nome-da-pasta-do-projeto]
```

### Passo 2: Configurar o Ambiente

O projeto usa um arquivo `.env` para gerenciar as variáveis de ambiente. Crie-o a partir do arquivo de exemplo:

```bash
cp .env.example .env
```

Abra o novo arquivo `.env` e ajuste as variáveis necessárias. Por padrão, as credenciais do banco de dados já devem estar configuradas para o Docker:

```ini
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=laravel
DB_PASSWORD=secret
```

### Passo 3: Subir os Containers do Docker

Execute o comando a seguir para construir as imagens e iniciar todos os serviços (Apache, PHP, MySQL e Node):

```bash
USER_ID=$(id -u) GROUP_ID=$(id -g) docker compose up --build -d
```

*A flag `--build` garante que as imagens sejam construídas. `-d` roda os containers em segundo plano.*

### Passo 4: Instalar as Dependências

Como as dependências não são instaladas durante o build, você precisa executá-las manualmente nos containers:

**Instalar Dependências do PHP (Laravel):**

```bash
docker exec laravel8-php73 composer install
```

**Instalar Dependências do Frontend (Vue.js):**

```bash
docker exec vue2-node npm install
```

### Passo 5: Executar as Migrações e Inicializar o Banco de Dados

Agora, configure a aplicação e o banco de dados:

```bash
# Gera a chave da aplicação
docker exec laravel8-php73 php artisan key:generate

# Roda as migrações e seeds do banco de dados
docker exec laravel8-php73 php artisan migrate --seed

# Cria a primeira empresa e o usuário administrador
docker exec laravel8-php73 php artisan company:create-first
```

### Passo 6: Construir o Frontend

Para um ambiente de distribuição, é melhor gerar os arquivos estáticos de produção.

```bash
docker exec vue2-node npm run build
```

### Passo 7: Iniciar o Serviço de Fila (Queue Worker)

O seu sistema utiliza o serviço de fila para enviar e-mails. Para que as notificações funcionem, você precisa iniciá-lo em segundo plano:

```bash
docker exec -d laravel8-php73 php artisan queue:work
```

-----

## 🌐 Acessar a Aplicação

A sua aplicação agora está rodando e pode ser acessada no navegador:

```
http://localhost:8080/
```

**Observação:** Se a porta `8080` estiver em uso em seu sistema, verifique o seu `docker-compose.yml` para a porta mapeada do container do frontend.