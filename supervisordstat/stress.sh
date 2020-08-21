#!/bin/bash
set -xe

cat supervisordstat/vegeta | vegeta attack -format=http -rate 1 -connections 1 -duration 10s | vegeta report
sleep 20s
cat supervisordstat/vegeta | vegeta attack -format=http -rate 50 -connections 1 -duration 30s | vegeta report
sleep 20s
cat supervisordstat/vegeta | vegeta attack -format=http -rate 50 -connections 100 -duration 1m | vegeta report
sleep 1m 
cat supervisordstat/vegeta | vegeta attack -format=http -rate 50 -connections 10000 -duration 5m | vegeta report
sleep 1m
cat supervisordstat/vegeta | vegeta attack -format=http -rate 100 -connections 50000 -duration 5m | vegeta report

# 49510
