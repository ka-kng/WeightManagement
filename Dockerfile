# ベースイメージ：PHP + Composer + Node
FROM php:8.4-fpm

# 作業ディレクトリ
WORKDIR /var/www/html

# 必要なパッケージをインストール
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    curl \
    libzip-dev \
    zip \
    npm \
    && docker-php-ext-install pdo_mysql zip

# Composer インストール
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# PHP 拡張の確認
RUN docker-php-ext-enable pdo_mysql

# アプリコードをコピー
COPY . .

# npm パッケージをインストール & ビルド
RUN npm install
RUN npm run build

# Composer パッケージインストール
RUN composer install --no-dev --optimize-autoloader

# Laravel キャッシュ・権限設定
RUN php artisan config:cache
RUN php artisan route:cache
RUN php artisan view:cache

# ポート開放
EXPOSE 8080

# 本番用起動コマンド
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8080"]
