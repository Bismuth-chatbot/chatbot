FROM php:7.4-cli-alpine
RUN apk add --no-cache --update supervisor sysstat zlib-dev acl
RUN docker-php-ext-configure sockets && docker-php-ext-install sockets pcntl
COPY ./config/supervisord.ini /etc/supervisor.d/chatbot.ini
COPY --from=composer /usr/bin/composer /usr/bin/composer
WORKDIR /usr/src/chatbot
COPY ./docker/php/entrypoint.sh /opt/entrypoint.sh
CMD [ "/usr/bin/supervisord" , "-n" ]
