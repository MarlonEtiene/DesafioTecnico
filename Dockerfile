FROM php:7.3-apache

# Instala pacotes necessários e extensões PHP
RUN apt-get update && apt-get install -y \
    zip unzip git curl libzip-dev libpng-dev libonig-dev libicu-dev \
    && docker-php-ext-install pdo pdo_mysql zip gd

# Instala o Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Ativa o mod_rewrite do Apache
RUN a2enmod rewrite

# Define o Document Root do Apache
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf

# Instala timezone e define fuso horário
RUN apt-get install -y tzdata
ENV TZ=America/Sao_Paulo

# Atualiza canal PECL e instala Redis compatível com PHP 7.3
RUN pecl channel-update pecl.php.net \
    && pecl install redis-5.3.7 \
    && docker-php-ext-enable redis

# Define o diretório de trabalho
WORKDIR /var/www/html

# Copia os arquivos de configuração para a instalação de dependências
COPY composer.json composer.lock ./
COPY package.json package-lock.json ./

# Instala as dependências do Composer e do Node
RUN composer install --no-interaction --no-dev --prefer-dist
RUN npm install

# Copia o restante da aplicação
COPY . .

# Cria as pastas de cache e storage e ajusta as permissões
RUN mkdir -p bootstrap/cache storage && chmod -R ug+rwx bootstrap storage

# Compila os assets do frontend
RUN npm run build
