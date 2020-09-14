#!/usr/bin/env node
const p = require('path').join(process.cwd(), process.argv[2])
console.log(require(p).map(e => parseInt(e.received)).reduce((acc, c) => acc + c, 0) / require(p).length)
