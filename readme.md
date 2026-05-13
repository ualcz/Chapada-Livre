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

[![React](https://img.shields.io/badge/React-18.x-61DAFB?style=flat-square&logo=react&logoColor=black)](https://react.dev/)
[![TypeScript](https://img.shields.io/badge/TypeScript-5.x-3178C6?style=flat-square&logo=typescript&logoColor=white)](https://www.typescriptlang.org/)
[![Laravel](https://img.shields.io/badge/Laravel-10.x-FF2D20?style=flat-square&logo=laravel&logoColor=white)](https://laravel.com/)
[![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=flat-square&logo=mysql&logoColor=white)](https://www.mysql.com/)
[![Docker](https://img.shields.io/badge/Docker-Compose-2496ED?style=flat-square&logo=docker&logoColor=white)](https://www.docker.com/)

<br/>

[🌐 Acessar o Site](https://chapadalivre.com.br) &nbsp;·&nbsp;
[📖 Guia de Configuração (Docker+React)](./DOCKER.md) &nbsp;·&nbsp;
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

O projeto possui uma **arquitetura moderna desacoplada**: a interface do usuário (Frontend) é uma **SPA construída em React**, enquanto a base de dados e a lógica de negócios (Backend API) operam sobre **Laravel** containerizado com Docker.

---

## ✨ Funcionalidades

| Módulo | Descrição |
|---|---|
| 📢 **Anúncios** | Criação, edição e exclusão de anúncios com upload de fotos |
| 🔍 **Busca avançada** | Filtros por categoria, cidade, faixa de preço e palavra-chave |
| 🗂️ **Categorias** | Automóveis, Imóveis, Eletrônicos, Serviços, Móveis e mais |
| 📍 **Geolocalização** | Filtragem por cidade ou região dentro da Chapada Diamantina |
| 👤 **Autenticação** | Login seguro via Backend |
| 💾 **Favoritos** | Salvar e gerenciar anúncios de interesse |
| 🔒 **Anti-Golpe** | Guia de segurança integrado para transações confiáveis |
| 📱 **Responsivo** | Interface moderna e rápida (React) adaptada para qualquer dispositivo |

---

## 🛠 Stack Tecnológica

**Frontend (SPA)**
- React 18
- Vite
- TypeScript
- TailwindCSS e Shadcn UI
- React Query & React Router

**Backend (API)**
- Laravel 10 (PHP 8.2)
- MySQL 8.0
- Redis (Cache e Filas)
- Servidor Web Nginx (Alpine)
- Ambiente containerizado (Docker Compose)

---

## 🚀 Início Rápido

O ambiente de desenvolvimento está dividido em duas partes. Para rodar a plataforma, você precisará iniciar o Backend e o Frontend.

### 1. Iniciar o Backend (API)

```bash
# Clone o repositório
git clone https://github.com/ualcz/Chapada-LIvre.git
cd Chapada-LIvre

# Configure as variáveis de ambiente do backend
cp .env.example .env

# Suba os containers (Laravel, MySQL, Redis, Nginx)
docker-compose up -d --build
```
*Aguarde a primeira execução concluir. A API ficará disponível em `http://localhost:8000`.*

### 2. Iniciar o Frontend (React SPA)

Abra um novo terminal na pasta raiz do projeto:

```bash
# Acesse a pasta do frontend
cd react-app

# Instale as dependências Node
npm install

# Inicie o ambiente de desenvolvimento local
npm run dev
```
*A interface estará acessível em `http://localhost:5173`.*

> **Configuração detalhada** — Para instruções completas, conexão entre as partes, variáveis e comandos, consulte o **[Guia de Configuração (Docker + React) →](./DOCKER.md)**

---

## 🗂 Estrutura do Projeto

Abaixo a visão geral da separação das responsabilidades:

```
Chapada-LIvre/
│
├── react-app/                  # ⚛️ FRONTEND EM REACT (SPA)
│   ├── src/                    # Código-fonte principal (Components, Pages, Hooks)
│   ├── public/                 # Assets estáticos
│   ├── package.json            # Dependências Node.js
│   ├── tailwind.config.ts      # Configurações do Tailwind
│   └── vite.config.ts          # Configurações do compilador Vite
│
├── app/                        # ⚙️ BACKEND EM LARAVEL (API)
│   ├── Http/Controllers/       # Lógica da API
│   └── Models/                 # Modelos do Banco de Dados
│
├── database/                   # Migrations e Seeders do MySQL
├── routes/                     # Definições de Rotas HTTP da API
├── docker/                     # Scripts de inicialização e configurações (Nginx, MySQL)
│
├── Dockerfile                  # Imagem PHP FPM para a API
├── docker-compose.yml          # Orquestração do Backend
├── DOCKER.md                   # Documentação detalhada do ambiente local
└── README.md                   # Este arquivo
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
   git commit -m "feat: adiciona skeleton loading no frontend"
   git commit -m "fix: corrige rota de favoritos na api"
   ```
4. Envie sua branch e abra um **Pull Request** descrevendo as mudanças

---

## 🔒 Segurança

Transações online exigem atenção. Antes de fechar qualquer negócio pela plataforma:

- Nunca realize pagamentos antecipados sem verificar o produto presencialmente
- Prefira negociar em locais públicos e movimentados
- Desconfie de valores muito abaixo do mercado
- Em caso de suspeita, reporte o anúncio diretamente na plataforma

---

## 📄 Licença

Distribuído sob licença proprietária.  
Todos os direitos reservados © 2026 **Chapada Livre**.