#!/bin/bash
set -e

echo "============================================"
echo "  Chapada Livre - Inicialização do Container"
echo "============================================"

# --------------------------------------------------
# 1. Instalar dependências do PHP (se necessário)
# --------------------------------------------------
if [ ! -f /var/www/vendor/autoload.php ]; then
    echo "[1/5] Instalando dependências do PHP (composer install)..."
    composer install --optimize-autoloader --no-interaction --working-dir=/var/www
else
    echo "[1/5] Dependências do PHP já instaladas. Pulando..."
fi

# --------------------------------------------------
# 2. Instalar dependências do Node.js (se necessário)
# --------------------------------------------------
if [ ! -d /var/www/node_modules ]; then
    echo "[2/5] Instalando dependências do Node.js (npm install)..."
    cd /var/www && npm install
else
    echo "[2/5] Dependências do Node.js já instaladas. Pulando..."
fi

# --------------------------------------------------
# 3. Compilar assets do frontend (se necessário)
# --------------------------------------------------
if [ ! -f /var/www/public/dist/front/styles.css ]; then
    echo "[3/5] Compilando assets do frontend (npm run prod)..."
    cd /var/www && npm run prod 2>/dev/null || echo "  [AVISO] Assets não compilados. Verifique o webpack.mix.js"
else
    echo "[3/5] Assets do frontend já compilados. Pulando..."
fi

# --------------------------------------------------
# 4. Gerar APP_KEY se necessário
# --------------------------------------------------
if [ -z "$(grep 'APP_KEY=base64:' /var/www/.env 2>/dev/null)" ]; then
    echo "[4/5] Gerando APP_KEY..."
    php /var/www/artisan key:generate --force
else
    echo "[4/5] APP_KEY já configurada. Pulando..."
fi

# --------------------------------------------------
# 5. Corrigir permissões
# --------------------------------------------------
echo "[5/5] Corrigindo permissões de storage e bootstrap/cache..."
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache 2>/dev/null || true
chmod -R 775 /var/www/storage /var/www/bootstrap/cache 2>/dev/null || true

# Criar diretórios de log se não existirem
mkdir -p /var/www/storage/logs
mkdir -p /var/www/storage/framework/{sessions,views,cache}
chown -R www-data:www-data /var/www/storage 2>/dev/null || true

echo ""
echo "============================================"
echo "  Inicialização concluída!"
echo "  Acesse: http://localhost:8000"
echo "============================================"
echo ""

# Iniciar PHP-FPM
exec php-fpm
