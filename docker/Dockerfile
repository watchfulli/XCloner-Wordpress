FROM wordpress:latest

RUN apt-get update && \
    apt-get install -y inotify-tools rsync less wait-for-it && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/*

COPY docker/php/post_max_size.ini /usr/local/etc/php/conf.d/post_max_size.ini
COPY docker/php/upload_max_file_size.ini /usr/local/etc/php/conf.d/upload_max_file_size.ini

RUN curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar && \
    chmod +x wp-cli.phar && \
    mv wp-cli.phar /usr/local/bin/wp

RUN chown -R www-data:www-data /var/www/html

COPY docker/entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/entrypoint.sh

USER www-data

ENTRYPOINT ["entrypoint.sh"]
CMD ["apache2-foreground"]
