FROM golang:1.11-alpine AS build-env
RUN apk add --update git && rm -rf /var/cache/apk/*
ADD supervisordstat /src
RUN cd /src && go get && go build -o supervisordstat

FROM php:7.4-cli
RUN apt-get update && apt install -y supervisor sysstat
RUN docker-php-ext-configure sockets && docker-php-ext-install sockets
COPY ./config/supervisord.conf /etc/supervisor/conf.d/chatbot.conf
COPY --from=build-env /src/supervisordstat /usr/bin/supervisordstat
WORKDIR /usr/src/chatbot
CMD [ "/usr/bin/supervisord" , "-n"]
