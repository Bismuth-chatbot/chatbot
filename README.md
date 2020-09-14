# Bizmuth Bot

Bizmuth Bot is an HTTP-based framework to run usual chatbots using the transport of your choice (twitch, youtube, slack).

It's architecture uses Mercure as a central part of the application. This would probably also work using RabbitMQ although this served as an experiment around the Symfony [EventSourceHttpClient](). 

![schema](./doc/schema.jpg)

The asynchronous framework [DriftPHP](https://driftphp.io/) is used to handle asynchronous connections to chat providers (eg: twitch irc) and and access to these providers through an HTTP API. 

Part of this project basis comes from [@flugv1 twitch chatbot](https://github.com/flug/chatbot-twitch).

## Run

Commands run through supervisord, with docker you have supervisord and mercure pre-configured:

```
docker-compose up --build
```

For production we use [mercure.rocks](https://mercure.rocks) managed hub.
