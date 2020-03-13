ARG PHP_VERSION

FROM php:${PHP_VERSION}-cli-buster

# Install dependencies
RUN apt-get update && apt-get install -yqq \
    libzip-dev \
    zip \
    wget \
    gnupg \
    git \
    libsodium-dev \
    libsodium23

# Install node and yarn
RUN curl -sL https://deb.nodesource.com/setup_12.x | bash && \
    curl -sS https://dl.yarnpkg.com/debian/pubkey.gpg | apt-key add && \
    echo "deb https://dl.yarnpkg.com/debian/ stable main" | tee /etc/apt/sources.list.d/yarn.list

RUN apt-get update && apt-get install -yqq \
    nodejs \
    yarn

# Install composer & make composer global
RUN curl -s https://getcomposer.org/installer | php && \
    mv composer.phar /usr/local/bin/composer

# Create a regular user account to work from
RUN set -xe; \
    groupadd -g 1000 developer && \
    useradd -u 1000 -g developer -m developer && \
    usermod -p "*" developer -s /bin/bash

# Copy bash aliases defined on host to container
COPY .bash_aliases /home/developer/.bash_aliases

# Cleanup
RUN apt-get clean && \
    rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# Temporary fix for PHPStan Issue #2883 - Access to undefined constant PDO
RUN docker-php-ext-install pdo_mysql && \
    docker-php-ext-install sodium
RUN apt-get update && apt-get install -yqq libxslt1-dev libxslt1.1
RUN docker-php-ext-install xsl

# Add cache location for composer
RUN mkdir /.composer && chown developer:developer /.composer

# Install Symfony Installer
RUN wget https://get.symfony.com/cli/installer -O - | bash

# Make Symfony Global
RUN mv /root/.symfony/bin/symfony /usr/local/bin/symfony

# Set which account to use
USER developer

# Set working dir for container
WORKDIR /var/htdocs