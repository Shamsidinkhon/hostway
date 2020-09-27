FROM php:7.2-fpm

# Copy composer.lock and composer.json
#COPY composer.lock composer.json /var/www/

# Set working directory
WORKDIR /var/www

# Install dependencies
RUN apt-get update && apt-get install -y \
    git \
    zip \
    unzip \
    curl

# Install php-psr
RUN git clone https://github.com/jbboehr/php-psr.git
RUN cd php-psr \
    phpize \
    ./configure \
    make \
    make test \
    make install

# Install extensions
RUN docker-php-ext-install pdo pdo_mysql

# Install phalcon
RUN pecl channel-update pecl.php.net && pecl install phalcon && pecl install apcu

RUN echo extension=psr.so | tee -a /usr/local/etc/php/conf.d/php.ini
RUN docker-php-ext-enable  phalcon apcu

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Add user for application
RUN groupadd -g 1000 www
RUN useradd -u 1000 -ms /bin/bash -g www www

# Copy existing application directory contents
COPY . /var/www

# Project Vendor Install
#RUN composer install

# Copy existing application directory permissions
COPY --chown=www:www . /var/www
COPY ./php/run.sh /tmp
# Change current user to www
USER www

# Expose port 9000 and start php-fpm server
EXPOSE 9000
ENTRYPOINT ["/tmp/run.sh"]
