async function main() {
  const ee = new EventSource('http://localhost:8081/.well-known/mercure?topic=https://twitch.tv/s0yuk4')
	let t0 = performance.now();
  let t1
  let interval
  let num = 0

  ee.onmessage = (d) => {
    num++
    t1 = performance.now()
    interval = (t1 - t0) / 1000

    if (interval >= 1) {
      console.log('Processed %d in %d', num, interval)
      t0 = t1
      num = 0
    }
  }
}

main()
