FROM php:8.2-apache

COPY deploy/security.conf /etc/apache2/conf-available/resume-security.conf

RUN apt-get update && apt-get install -y --no-install-recommends \
        git poppler-utils tesseract-ocr libicu-dev libzip-dev libcurl4-openssl-dev libonig-dev \
    && docker-php-ext-install curl mbstring pdo_mysql \
    && a2enmod rewrite headers \
    && a2enconf resume-security \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-interaction --no-progress

COPY . .
RUN mkdir -p tools uploads reports \
    && git clone --depth 1 https://github.com/tecnickcom/TCPDF.git tools/tcpdf \
    && chown -R www-data:www-data tools/tcpdf uploads reports

ENV PDFTOTEXT_PATH=/usr/bin/pdftotext \
    PDFTOPPM_PATH=/usr/bin/pdftoppm \
    TESSERACT_PATH=/usr/bin/tesseract

EXPOSE 80
