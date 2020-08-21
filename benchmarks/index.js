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
  const stressTests = [
    [0, 10],
    [30, 60],
    [80, 140],
    [200, 500],
    [560, 860]
  ]

  var options = {
    annotations: {
      position: 'front',
      // xaxis: [
      //   {
      //     x: data[stressTests[0][0]]["Time"],
      //     x2: data[stressTests[0][1]]["Time"],
      //     label: {
      //       text: '1 req/s with 1 connection'
      //     }
      //   },
      //   {
      //     x: data[stressTests[1][0]]["Time"],
      //     x2: data[stressTests[1][1]]["Time"],
      //     label: {
      //       text: '50 req/s with 1 connection'
      //     }
      //   },
      //   {
      //     x: data[stressTests[2][0]]["Time"],
      //     x2: data[stressTests[2][1]]["Time"],
      //     label: {
      //       text: '50 req/s with 100 connections'
      //     }
      //   },
      //   {
      //     x: data[stressTests[3][0]]["Time"],
      //     x2: data[stressTests[3][1]]["Time"],
      //     label: {
      //       text: '50 req/s with 10000 connections'
      //     }
      //   },
      //   {
      //     x: data[stressTests[4][0]]["Time"],
      //     x2: data[stressTests[4][1]]["Time"],
      //     label: {
      //       text: '100 req/s with 50000 connections'
      //     }
      //   }
      // ]
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
