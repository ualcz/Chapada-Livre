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
    libicu-dev \
    libwebp-dev \
    libavif-dev \
    libmagickwand-dev \
    libxpm-dev \
    libzip-dev \
    libcurl4-openssl-dev \
    pkg-config \
    libssl-dev \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# ============================================
# Extensões PHP
# ============================================
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp --with-avif --with-xpm
RUN docker-php-ext-configure intl && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip curl ftp fileinfo intl
RUN pecl install redis imagick && docker-php-ext-enable redis imagick

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