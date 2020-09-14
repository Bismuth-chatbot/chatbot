function normalizeData(data) {
  return data.map((value) => {
    value["Time"] = new Date(value["Time"] * 1000).getTime()
    return value
  })
}

function toPercent(value) {
  return value.toFixed(2) + '%'
}

function toMB(value) {
  return (value / 1024).toFixed(2) + ' MB';
}

function getAvg(values) {
  const total = values.reduce((acc, c) => acc + c, 0);
  return total / values.length;
}

function getCpuData(data) {
  const cpuData = []
  const averageEvery = 5
  let values = []
  let k = 0 

  for (let i = 0; i < data.length - 1; i++) {
    if (i !== 0 && i % averageEvery === 0) {
      cpuData.push({x: data[k]["Time"], y: getAvg(values)})
      values = []
      k = i
      continue
    }

    values.push(parseFloat(data[i]["%CPU"]))
  }

  return cpuData
}

async function main() {
  const response = await fetch('monit-logger.json')
  const data = normalizeData(await response.json())

  var options = {
    annotations: {
      position: 'front',
    },
    chart: {
      type: 'line'
    },
    series: [
      {
        name: 'cpu%',
        data: getCpuData(data)
      },
      {
        name: 'rss',
        data: data.map((value) => ({y: parseInt(value["RSS"]), x: value["Time"]}))
      },
      {
        name: 'vsz',
        data: data.map((value) => ({y: parseInt(value["VSZ"]), x: value["Time"]}))
      },
    ],
    xaxis: {
      type: 'categories',
      tickAmount: 10,
      labels: {
        formatter: (v) => {
          const d = new Date(v)
          return `${d.getHours()}:${d.getMinutes()}:${d.getSeconds()}`
        }
      }
    },
    yaxis: [
      {"opposite": true, labels: {formatter: toPercent}, min: 0, max: 100},
      {"opposite": false, labels: {formatter: toMB}},
      {"opposite": false, seriesName: 'rss', show: false, labels: {formatter: toMB}},

    ],
    tooltip: {
      x: {
        format: 'HH:mm:ss'
      }
    },
  }

  var chart = new ApexCharts(document.querySelector("#chart"), options);

  chart.render();
}

main()
