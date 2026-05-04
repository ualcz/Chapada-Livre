<div align="center">

<br/>

<img src="https://chapadalivre.com.br/storage/app/logo/thumbnails/1500x1500-logo-69dcd992726af-201940723676.png" width="160" alt="Chapada Livre" />

<br/>
<br/>

# Chapada Livre

**O portal de classificados da Chapada Diamantina**

Conectando compradores e vendedores em 19 municípios da Bahia —  
de forma simples, rápida e segura.

<br/>

[![PHP](https://img.shields.io/badge/PHP-8.2-777BB4?style=flat-square&logo=php&logoColor=white)](https://www.php.net/)
[![Laravel](https://img.shields.io/badge/Laravel-10.x-FF2D20?style=flat-square&logo=laravel&logoColor=white)](https://laravel.com/)
[![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=flat-square&logo=mysql&logoColor=white)](https://www.mysql.com/)
[![Docker](https://img.shields.io/badge/Docker-Compose-2496ED?style=flat-square&logo=docker&logoColor=white)](https://www.docker.com/)
[![Redis](https://img.shields.io/badge/Redis-Cache-DC382D?style=flat-square&logo=redis&logoColor=white)](https://redis.io/)
[![Nginx](https://img.shields.io/badge/Nginx-Proxy-009639?style=flat-square&logo=nginx&logoColor=white)](https://nginx.org/)

<br/>

[🌐 Acessar o Site](https://chapadalivre.com.br) &nbsp;·&nbsp;
[📖 Guia Docker](./DOCKER.md) &nbsp;·&nbsp;
[🐛 Reportar Bug](https://github.com/ualcz/Chapada-Livre/issues) &nbsp;·&nbsp;
[💡 Sugerir Funcionalidade](https://github.com/ualcz/Chapada-Livre/issues)

<br/>

</div>

---

## Índice

- [Sobre o Projeto](#-sobre-o-projeto)
- [Funcionalidades](#-funcionalidades)
- [Stack Tecnológica](#-stack-tecnológica)
- [Início Rápido](#-início-rápido)
- [Estrutura do Projeto](#-estrutura-do-projeto)
- [Cidades Atendidas](#-cidades-atendidas)
- [Contribuindo](#-contribuindo)
- [Licença](#-licença)

---

## 📌 Sobre o Projeto

O **Chapada Livre** é uma plataforma de classificados online desenvolvida especificamente para a região da [Chapada Diamantina](https://pt.wikipedia.org/wiki/Chapada_Diamantina), Bahia. O projeto nasce da necessidade de um espaço digital local, onde moradores e visitantes possam anunciar e negociar produtos, imóveis, veículos e serviços com segurança e sem intermediários.

A plataforma é construída sobre o **Laravel** e containerizada com **Docker**, garantindo um ambiente de desenvolvimento consistente e um deploy simplificado.

---

## ✨ Funcionalidades

| Módulo | Descrição |
|---|---|
| 📢 **Anúncios** | Criação, edição e exclusão de anúncios com upload de fotos |
| 🔍 **Busca avançada** | Filtros por categoria, cidade, faixa de preço e palavra-chave |
| 🗂️ **Categorias** | Automóveis, Imóveis, Eletrônicos, Serviços, Móveis e mais |
| 📍 **Geolocalização** | Filtragem por cidade ou região dentro da Chapada Diamantina |
| 👤 **Autenticação** | Login com e-mail/senha ou via OAuth (Google) |
| 💾 **Favoritos** | Salvar e gerenciar anúncios de interesse |
| 🔒 **Anti-Golpe** | Guia de segurança integrado para transações confiáveis |
| 📱 **Responsivo** | Interface adaptada para mobile, tablet e desktop |

---

## 🛠 Stack Tecnológica

```
Backend          Laravel 10 (PHP 8.2)
Frontend         Blade Templates · CSS · JavaScript
Banco de Dados   MySQL 8.0
Cache & Filas    Redis
Servidor Web     Nginx (Alpine)
Containerização  Docker · Docker Compose
Autenticação     Laravel Auth · Laravel Socialite (Google OAuth)
Storage          Laravel Filesystem (S3-compatible / local)
```

---

## 🚀 Início Rápido

O ambiente de desenvolvimento é totalmente containerizado. Você precisará apenas de **Docker** e **Git** instalados.

```bash
# 1. Clone o repositório
git clone https://github.com/ualcz/Chapada-LIvre.git
cd Chapada-LIvre

# 2. Configure as variáveis de ambiente
cp .env.example .env

# 3. Suba os containers
docker-compose up -d --build
```

Aguarde alguns minutos na primeira execução — o entrypoint instala as dependências automaticamente.

```
✓  http://localhost:8000
```

> **Configuração detalhada** — Para instruções completas sobre variáveis de ambiente, credenciais do banco, comandos úteis e resolução de problemas, consulte o **[Guia de Configuração Docker →](./DOCKER.md)**

### Containers em execução

| Container | Imagem | Porta |
|---|---|---|
| `laravel_app` | `php:8.2-fpm` | `9000` (interna) |
| `laravel_nginx` | `nginx:alpine` | `8000` |
| `laravel_mysql` | `mysql:8.0` | `3306` |
| `laravel_redis` | `redis:alpine` | `6379` (interna) |

---

## 🗂 Estrutura do Projeto

```
Chapada-LIvre/
│
├── app/
│   ├── Http/
│   │   ├── Controllers/        # Lógica de negócio por recurso
│   │   └── Middleware/         # Autenticação, logging, etc.
│   ├── Models/                 # Modelos Eloquent (User, Ad, Category…)
│   └── Providers/              # Service Providers do Laravel
│
├── database/
│   ├── migrations/             # Histórico de alterações do schema
│   └── seeders/                # Dados iniciais (cidades, categorias…)
│
├── docker/
│   ├── entrypoint.sh           # Bootstrap automático do container app
│   └── mysql/
│       └── init.sql            # Grants iniciais do MySQL
│
├── resources/
│   ├── views/                  # Templates Blade
│   ├── css/                    # Estilos da aplicação
│   └── js/                     # Scripts do frontend
│
├── routes/
│   └── web.php                 # Definição de rotas HTTP
│
├── storage/                    # Uploads, logs e cache gerado
│
├── Dockerfile                  # Imagem PHP 8.2-FPM + Node.js
├── docker-compose.yml          # Orquestração dos 4 containers
├── nginx.conf                  # Configuração do servidor web
├── DOCKER.md                   # Guia completo de configuração Docker
└── .env.example                # Modelo de variáveis de ambiente
```

---

## 🌍 Cidades Atendidas

A plataforma cobre **19 municípios** da Chapada Diamantina:

<div align="center">

Seabra · Lençóis · Mucugê · Piatã · Iraquara · Andaraí · Boninal  
Abaíra · Bonito · Ibicoara · Jussiape · Morro do Chapéu · Nova Redenção  
Novo Horizonte · Souto Soares · Utinga · Wagner · Ibitiara · Barra da Estiva

</div>

---

## 🤝 Contribuindo

Contribuições são bem-vindas. Por favor, siga o fluxo abaixo:

1. Faça um **fork** do repositório
2. Crie uma branch descritiva:
   ```bash
   git checkout -b feat/nome-da-funcionalidade
   # ou
   git checkout -b fix/descricao-do-bug
   ```
3. Faça seus commits seguindo o padrão [Conventional Commits](https://www.conventionalcommits.org/):
   ```bash
   git commit -m "feat: adiciona filtro por faixa de preço"
   git commit -m "fix: corrige redirect após login social"
   ```
4. Envie sua branch e abra um **Pull Request** descrevendo as mudanças

---

## 🔒 Segurança

Transações online exigem atenção. Antes de fechar qualquer negócio pela plataforma:

- Nunca realize pagamentos antecipados sem verificar o produto presencialmente
- Prefira negociar em locais públicos e movimentados
- Desconfie de valores muito abaixo do mercado
- Em caso de suspeita, reporte o anúncio diretamente na plataforma

Consulte o [Guia Anti-Golpe completo →](https://chapadalivre.com.br/page/anti-scam)

---

## 📄 Licença

Distribuído sob licença proprietária.  
Todos os direitos reservados © 2026 **Chapada Livre**.