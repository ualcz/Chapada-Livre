FROM php:8.2-fpm

# ============================================
# Dependências do sistema
# ============================================
RUN apt-get update && apt-get install -y \
    build-essential \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    locales \
    zip \
    jpegoptim optipng pngquant gifsicle \
    vim \
    unzip \
    git \
    curl \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libcurl4-openssl-dev \
    pkg-config \
    libssl-dev \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# ============================================
# Extensões PHP
# ============================================
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip curl ftp fileinfo
RUN pecl install redis && docker-php-ext-enable redis

# ============================================
# Composer
# ============================================
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# ============================================
# Node.js 18
# ============================================
RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash - \
    && apt-get install -y nodejs

# ============================================
# Diretório de trabalho
# ============================================
WORKDIR /var/www

# ============================================
# Script de inicialização
# ============================================
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 9000

ENTRYPOINT ["entrypoint.sh"]