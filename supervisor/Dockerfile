FROM php:8.4-cli

RUN apt-get update && apt-get install -y \
    supervisor \
    libpq-dev \
    zip \
    unzip \
    && docker-php-ext-install pdo pdo_pgsql pcntl \
    && apt-get clean

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

RUN mkdir -p /var/www/storage/logs

RUN groupadd -g 1000 www \
    && useradd -u 1000 -ms /bin/bash -g www www

CMD ["/usr/bin/supervisord", "-n", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
