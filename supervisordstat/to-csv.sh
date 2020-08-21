#!/bin/bash

temp=${1/txt/temp}
tab=${1/txt/tab}
csv=${1/txt/csv}
json=${1/txt/json}
head -n 3 $1 | tail -n 1 | sed 's/# //' > $temp
sed '1d' $1 >> $temp
csvtk space2tab $temp > $tab
csvtk tab2csv $tab > $csv
csvtk csv2json $csv > $json
rm $temp $tab
