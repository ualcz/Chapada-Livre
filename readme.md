# 🏗️ Chapada Livre - Guia de Configuração Docker

Este guia explica como configurar e rodar o projeto **Chapada Livre** (baseado no LaraClassified) usando Docker.

---

## 📋 Pré-requisitos

Antes de começar, certifique-se de ter instalado:

| Software | Versão Mínima | Download |
|----------|--------------|----------|
| **Docker Desktop** | 4.0+ | [docker.com](https://www.docker.com/products/docker-desktop/) |
| **Git** | 2.0+ | [git-scm.com](https://git-scm.com/) |

> **Nota:** No Windows, o Docker Desktop já inclui o Docker Compose.

---

## 🚀 Início Rápido (3 comandos)

```bash
# 1. Clonar o repositório
git clone https://github.com/seu-usuario/Chapada-LIvre.git
cd Chapada-LIvre

# 2. Copiar o arquivo de ambiente
cp .env.example .env

# 3. Subir todos os containers
docker-compose up -d --build
```

**Pronto!** Aguarde alguns minutos para a instalação automática das dependências e acesse:

🌐 **http://localhost:8000**

---

## 🏛️ Arquitetura dos Containers

O projeto utiliza 4 containers Docker:

```
┌─────────────────────────────────────────────────┐
│                  Docker Network                  │
│                                                  │
│  ┌──────────┐    ┌──────────┐    ┌──────────┐   │
│  │  Nginx   │───▶│  PHP-FPM │───▶│  MySQL   │   │
│  │ :8000→80 │    │  (app)   │    │  :3306   │   │
│  └──────────┘    └────┬─────┘    └──────────┘   │
│                       │          ┌──────────┐   │
│                       └─────────▶│  Redis   │   │
│                                  │  :6379   │   │
│                                  └──────────┘   │
└─────────────────────────────────────────────────┘
```

| Container | Imagem | Função | Porta |
|-----------|--------|--------|-------|
| `laravel_app` | `php:8.2-fpm` | Executa o código PHP/Laravel | 9000 (interna) |
| `laravel_nginx` | `nginx:alpine` | Servidor web / proxy reverso | **8000** → 80 |
| `laravel_mysql` | `mysql:8.0` | Banco de dados | **3306** |
| `laravel_redis` | `redis:alpine` | Cache e filas | 6379 (interna) |

---

## 📁 Estrutura dos Arquivos Docker

```
Chapada-LIvre/
├── Dockerfile              # Imagem PHP com extensões e Node.js
├── docker-compose.yml      # Orquestração dos containers
├── nginx.conf              # Configuração do Nginx
├── .env                    # Variáveis de ambiente
├── .dockerignore           # Arquivos ignorados no build
└── docker/
    ├── entrypoint.sh       # Script de inicialização automática
    └── mysql/
        └── init.sql        # Permissões do MySQL (auto)
```

---

## ⚙️ O que o Entrypoint Automatiza

Quando o container `app` inicia, o script `docker/entrypoint.sh` executa automaticamente:

| Etapa | Ação | Condição |
|-------|------|----------|
| 1 | `composer install` | Só se `/vendor` não existir |
| 2 | `npm install` | Só se `/node_modules` não existir |
| 3 | `npm run production` | Só se `/public/css` ou `/public/js` não existir |
| 4 | `php artisan key:generate` | Só se `APP_KEY` não estiver definida |
| 5 | Corrigir permissões | Sempre (storage, bootstrap/cache) |

> **Resultado:** Na primeira vez que rodar, pode levar **3-5 minutos**. Nas próximas vezes, inicia em segundos.

---

## 🔧 Configuração do `.env`

O arquivo `.env` já vem pré-configurado para Docker. As configurações importantes são:

```env
# URL da aplicação (HTTP, não HTTPS!)
APP_URL=http://localhost:8000
FORCE_HTTPS=false

# Banco de dados (deve usar o nome do serviço Docker)
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=laravel
DB_PASSWORD=password

# Redis (deve usar o nome do serviço Docker)
REDIS_HOST=redis
```

> ⚠️ **Importante:** O `DB_HOST` deve ser `mysql` (nome do serviço Docker), e **não** `localhost` ou `127.0.0.1`.

---

## 🔑 Credenciais do Banco de Dados

Para o **Instalador do LaraClassified**, use estas credenciais:

| Campo | Valor |
|-------|-------|
| **Host** | `mysql` |
| **Port** | `3306` |
| **Database name** | `laravel` |
| **Database tables prefix** | `lc_` |
| **Username** | `laravel` |
| **Password** | `password` |

---

## 📖 Comandos Úteis

### Gerenciamento dos Containers

```bash
# Subir containers (primeira vez ou após mudanças no Dockerfile)
docker-compose up -d --build

# Subir containers (sem rebuild)
docker-compose up -d

# Parar todos os containers
docker-compose down

# Parar e remover volumes (⚠️ apaga o banco de dados!)
docker-compose down -v

# Ver status dos containers
docker-compose ps

# Ver logs em tempo real
docker-compose logs -f

# Ver logs de um container específico
docker-compose logs -f app
docker-compose logs -f nginx
docker-compose logs -f mysql
```

### Comandos Laravel (Artisan)

```bash
# Executar comandos artisan
docker-compose exec app php artisan migrate
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan view:clear

# Acessar o shell do container
docker-compose exec app bash
```

### Banco de Dados

```bash
# Acessar o MySQL via terminal
docker-compose exec mysql mysql -ularavel -ppassword laravel

# Fazer backup do banco
docker-compose exec mysql mysqldump -ularavel -ppassword laravel > backup.sql

# Restaurar backup
docker-compose exec -T mysql mysql -ularavel -ppassword laravel < backup.sql
```

---

## 🐛 Resolução de Problemas

### 1. Erro: "HTTPS redirect" / Página não carrega

**Causa:** O sistema tenta redirecionar para HTTPS.

**Solução:** Verificar se o `nginx.conf` contém a linha:
```nginx
fastcgi_param HTTPS off;
```

### 2. Erro: "Permission denied" no storage

**Causa:** O usuário `www-data` não tem permissão nas pastas.

**Solução:**
```bash
docker-compose exec app chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
docker-compose exec app chmod -R 775 /var/www/storage /var/www/bootstrap/cache
```

### 3. Erro: "FLUSH TABLES" / Access denied

**Causa:** O usuário MySQL não tem privilégios suficientes.

**Solução:** O arquivo `docker/mysql/init.sql` resolve isso automaticamente na **primeira criação** do container. Se o problema persistir:
```bash
docker-compose exec mysql mysql -uroot -prootpassword -e "GRANT ALL PRIVILEGES ON *.* TO 'laravel'@'%'; FLUSH PRIVILEGES;"
```

> ⚠️ **Nota:** Se o volume do MySQL já existia antes do `init.sql`, o script não será executado. Nesse caso, apague o volume e recrie:
> ```bash
> docker-compose down -v
> docker-compose up -d --build
> ```

### 4. Erro: "Connection refused" no banco

**Causa:** O container do MySQL ainda não está pronto.

**Solução:** O `docker-compose.yml` já inclui um **healthcheck** que faz o container `app` esperar o MySQL estar pronto. Se ainda ocorrer, aguarde 30 segundos e recarregue a página.

### 5. Container `app` reiniciando em loop

**Causa:** Erro no `entrypoint.sh` ou dependências faltando.

**Solução:**
```bash
# Ver o log do erro
docker-compose logs app

# Reiniciar do zero
docker-compose down
docker-compose up -d --build
```

---

## 🔄 Resetar Tudo (Instalação Limpa)

Se precisar recomeçar do zero:

```bash
# 1. Parar containers e remover volumes
docker-compose down -v

# 2. Remover dependências locais
rm -rf vendor node_modules

# 3. Rebuild e iniciar
docker-compose up -d --build
```

Depois acesse http://localhost:8000 para refazer a instalação.
