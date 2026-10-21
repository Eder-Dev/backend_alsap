FROM php:8.2-apache

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

RUN apt-get update \
	&& apt-get install -y --no-install-recommends libonig-dev \
	&& docker-php-ext-install mbstring pdo_mysql \
	&& a2enmod rewrite \
	&& sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
	&& printf '<Directory /var/www/html/public>\n    AllowOverride All\n    Require all granted\n</Directory>\n' > /etc/apache2/conf-available/alsap.conf \
	&& a2enconf alsap \
	&& rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html
COPY . .

EXPOSE 80

