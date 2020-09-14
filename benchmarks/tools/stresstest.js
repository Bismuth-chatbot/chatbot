#!/usr/bin/env node
const { Transform } = require('stream');
const http = require('http')

function toDataString(data) {
  if (typeof data === 'object') return toDataString(JSON.stringify(data));
  return data
    .split(/\r\n|\r|\n/)
    .map(line => `data: ${line}\n`)
    .join('');
}

/**
 * Adapted from https://raw.githubusercontent.com/EventSource/node-ssestream
 * Transforms "messages" to W3C event stream content.
 * See https://html.spec.whatwg.org/multipage/server-sent-events.html
 * A message is an object with one or more of the following properties:
 * - data (String or object, which gets turned into JSON)
 * - type
 * - id
 * - retry
 *
 * If constructed with a HTTP Request, it will optimise the socket for streaming.
 * If this stream is piped to an HTTP Response, it will set appropriate headers.
 */
class SseStream extends Transform {
  lastEventId  = null;
  eventsNb = 0;

  constructor(req) {
    super({ objectMode: true });
    if (req && req.socket) {
      req.socket.setKeepAlive(true);
      req.socket.setNoDelay(true);
      req.socket.setTimeout(0);
    }
  }

  pipe(destination, options) {
    if (destination.writeHead) {
      destination.writeHead(200, {
        // Server-sent events https://developer.mozilla.org/en-US/docs/Web/API/Server-sent_events/Using_server-sent_events#Sending_events_from_the_server
        'Content-Type': 'text/event-stream',
        // Keep alive, useful only for HTTP 1 clients https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Keep-Alive
        Connection: 'keep-alive',
        // Disable cache, even for old browsers and proxies
        'Cache-Control':
          'private, no-cache, no-store, must-revalidate, max-age=0',
        'Transfer-Encoding': 'identity',
        Pragma: 'no-cache',
        Expire: '0',
        // NGINX support https://www.nginx.com/resources/wiki/start/topics/examples/x-accel/#x-accel-buffering
        'X-Accel-Buffering': 'no',
      });
      destination.flushHeaders();
    }

    destination.write(':ok\n');
    return super.pipe(destination, options);
  }

  _transform(
    message,
    encoding,
    callback
  ) {
    let data = ''
    if (message.type) data += `event: ${message.type}\n`;
    if (message.id) data += `id: ${message.id}\n`;
    if (message.retry) data += `retry: ${message.retry}\n`;
    if (message.data) data += toDataString(message.data);
    data += '\n';

    this.push(data);
    callback();
  }

  writeMessage(
    message,
    encoding,
    cb
  ) {
    if (!message.id) {
      this.lastEventId++;
      message.id = this.lastEventId.toString();
    }

    if (!this.write(message, encoding, cb)) {
      this.once('drain', cb);
    } else {
      process.nextTick(cb);
    }
  }
}

http.createServer((request, response) => {
  response.setHeader('Access-Control-Allow-Origin', '*');
	response.setHeader('Access-Control-Request-Method', '*');
	response.setHeader('Access-Control-Allow-Methods', 'OPTIONS, GET');
	response.setHeader('Access-Control-Allow-Headers', '*');

	if (request.method === 'OPTIONS') {
		response.writeHead(200);
		response.end();
		return;
	}

  const stream = new SseStream(request);
  stream.pipe(response);

  const eventLoopQueue = () => {
    return new Promise(resolve => 
      setImmediate(() => {
        stream.writeMessage({data: {message: "!dice", channel: "s0yuk4", nickname: "soyuka"}}, 'utf-8', resolve);
      })
    );
  }

  const run = async () => {
    while (true) {
      await eventLoopQueue();
    }
  }

  run().then(() => console.log('Done'));

  request.on('close', () => {
    response.end();
  });
}).listen(8081, '127.0.0.1', () => console.log('listening on 8081'));
