FROM php:7.2-fpm

# Copy composer.lock and composer.json
COPY composer.lock composer.json /var/www/

# Set working directory
WORKDIR /var/www

# Install dependencies
RUN apt-get update && apt-get install -y \
    curl

# Install php-psr
RUN git clone https://github.com/jbboehr/php-psr.git \
    cd php-psr \
    /usr/local/bin/phpize \
    ./configure --with-php-config=/usr/local/bin/php-config \
    make \
    make test \
    sudo make install

RUN curl -s https://packagecloud.io/install/repositories/phalcon/stable/script.deb.sh | sudo bash \
    apt-get install php7.2-phalcon

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install extensions
RUN docker-php-ext-install php_pdo pdo_mysqlnd

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

## Add user for laravel application
#RUN groupadd -g 1000 www
#RUN useradd -u 1000 -ms /bin/bash -g www www

# Copy existing application directory contents
COPY . /var/www

# Copy existing application directory permissions
COPY --chown=www:www . /var/www

# Change current user to www
USER www

# Expose port 9000 and start php-fpm server
EXPOSE 9000
CMD ["php-fpm"]