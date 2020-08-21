require('./persec.json').map(e => parseInt(e.received)).reduce((acc, c) => acc + c, 0) / require('./persec.json').length
