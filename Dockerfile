FROM php:7.4-cli
RUN apt-get update && apt install -y supervisor
RUN docker-php-ext-configure sockets && docker-php-ext-install sockets
COPY ./config/supervisord.conf /etc/supervisor/conf.d/chatbot.conf
WORKDIR /usr/src/chatbot
CMD [ "/usr/bin/supervisord" , "-n"]
