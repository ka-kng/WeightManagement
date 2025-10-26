# ベースイメージ：PHP 8.4 + FPM
FROM php:8.4-fpm

# 作業ディレクトリ
WORKDIR /var/www/html

# 必要パッケージと PHP 拡張をインストール
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    curl \
    libzip-dev \
    zip \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    npm \
    && docker-php-ext-install pdo_mysql mbstring zip exif pcntl bcmath gd

# Composer インストール
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
RUN docker-php-ext-enable pdo_mysql

# 依存ファイルだけ先にコピー（キャッシュ活用）
COPY composer.json composer.lock ./
COPY package*.json ./

# npm / composer 依存関係インストール
RUN npm install
RUN composer install --no-dev --optimize-autoloader

# アプリコードをコピー
COPY . .

# npm ビルド
RUN npm run build

# Laravel キャッシュ生成
RUN php artisan config:cache
RUN php artisan route:cache
RUN php artisan view:cache

# 権限設定
RUN chown -R www-data:www-data /var/www/html

# ポート開放
EXPOSE 8080

# Render 本番用起動コマンド
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8080"]
