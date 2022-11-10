FROM wordpress:latest

RUN apt-get update
RUN apt-get install -y inotify-tools rsync wait-for-it

COPY docker/entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/entrypoint.sh

RUN curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
RUN chmod +x wp-cli.phar
RUN mv wp-cli.phar /usr/local/bin/wp

ENTRYPOINT ["entrypoint.sh"]
CMD ["apache2-foreground"]
