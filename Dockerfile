FROM golang:1.11-alpine AS build-env
RUN apk add --update git && rm -rf /var/cache/apk/*
ADD supervisordstat /src
RUN cd /src && go get && go build -o supervisordstat

FROM php:7.4-cli-alpine
RUN apk add --update supervisor sysstat && rm -rf /var/cache/apk/*
RUN docker-php-ext-configure sockets && docker-php-ext-install sockets
COPY ./config/supervisord.ini /etc/supervisor.d/chatbot.ini
COPY --from=build-env /src/supervisordstat /usr/bin/supervisordstat
WORKDIR /usr/src/chatbot
CMD [ "/usr/bin/supervisord" , "-n"]
