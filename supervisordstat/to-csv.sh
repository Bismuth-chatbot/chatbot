#!/bin/bash

temp=${1/txt/temp}
csv=${1/txt/csv}
head -n 3 $1 | tail -n 1 | sed 's/# //' > $temp
sed '1d' $1 >> $temp
csvtk space2tab $temp > $csv
rm $temp
