FROM node:22-bookworm-slim AS node
FROM composer:2.8 AS composer
FROM php:8.2-cli-bookworm

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        ca-certificates curl git rsync unzip zip \
        libcurl4-openssl-dev libonig-dev libxml2-dev libzip-dev \
    && docker-php-ext-install -j"$(nproc)" \
        curl dom mbstring simplexml xml xmlwriter zip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=node /usr/local/bin/node /usr/local/bin/node
COPY --from=node /usr/local/lib/node_modules /usr/local/lib/node_modules
RUN ln -s /usr/local/lib/node_modules/npm/bin/npm-cli.js /usr/local/bin/npm \
    && ln -s /usr/local/lib/node_modules/npm/bin/npx-cli.js /usr/local/bin/npx
COPY --from=composer /usr/bin/composer /usr/local/bin/composer
ENV XCLONER_BUILD_MEMORY_LIMIT=2G
COPY php-build.ini /usr/local/etc/php/conf.d/zz-build.ini
RUN chmod 0644 /usr/local/etc/php/conf.d/zz-build.ini
COPY run.sh /usr/local/bin/xcloner-build
RUN chmod 0755 /usr/local/bin/xcloner-build

ENTRYPOINT ["bash", "/usr/local/bin/xcloner-build"]
