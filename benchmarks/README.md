# Profiling

## Vegeta + [pidstat](http://sebastien.godard.pagesperso-orange.fr/man_pidstat.html)

Required: vegeta, pidstat (systat package), [csvtk](https://github.com/shenwei356/csvtk/)
Run a mercure server, then:

```bash
bin/console app:logger
# replace $PID by the pid from bin/console app:logger
pidstat -I -H -h -r -d -u -p $PID 1 > monit-logger.txt
# Stress test mercure
cat benchmarks/vegeta/request.vegeta |
  vegeta attack -format=http -rate 1300 -duration 1m |
  vegeta report 
```

To get readable data from the monit-logger.txt file, use:

```bash
./benchmarks/tools/pidstat-to-csv-json.sh monit-logger.txt
```

For the `persec` file to json:

```bash
csvtk csv2json -d ';' persec > persec.json
```

Then get an average request per second using:

```bash
./benchmarks/tools/average-persec.js persec.json
```

## Test with [httpie](https://httpie.org/)

```
http --form POST http://localhost:8080/.well-known/mercure Authorization:'Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJtZXJjdXJlIjp7InB1Ymxpc2giOltdfX0.GFRUFE2C1GaLTnX2WZnO3SoeOM0rrVcI0yph1K_Oo-w' topic=https://twitch.tv/s0yuk4 data='{"message": "!dice", "nickname": "s0yuk4", "channel": "s0yuk4"}'
```

## Test with [hey](https://github.com/rakyll/hey)

```
hey -n 100000 -m POST -T "application/x-www-form-urlencoded" -H "Authorization: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJtZXJjdXJlIjp7InB1Ymxpc2giOltdfX0.GFRUFE2C1GaLTnX2WZnO3SoeOM0rrVcI0yph1K_Oo-w" -d "topic=https://twitch.tv/s0yuk4&data=%7B%22message%22%3A+%22%21dice%22%2C+%22nickname%22%3A+%22s0yuk4%22%2C+%22channel%22%3A+%22s0yuk4%22%7D" -h2 http://localhost:8080/.well-known/mercure
```
