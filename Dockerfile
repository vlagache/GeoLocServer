# php renseigné dans le composer.json
FROM php:7.1

# Installer les dépendances nécessaires pour Composer
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    neovim \
    git \
    && docker-php-ext-install zip pdo_mysql

# Composer de 2019... on y croit.
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer --version=1.9.0
RUN curl -sS https://get.symfony.com/cli/installer | bash \
    && mv /root/.symfony*/bin/symfony /usr/local/bin/symfony

WORKDIR /app

COPY . . 

RUN php -d memory_limit=-1 /usr/local/bin/composer install --no-scripts

CMD ["symfony", "server:start"]

