package main

import (
	"fmt"
	"time"
	"os"
	"os/exec"

	eventhandler "github.com/mtyurt/supervisor-event-handler"
)

func main() {
	handler := eventhandler.New()
	handler.HandleEvent("PROCESS_STATE", func(header eventhandler.HeaderTokens, payload map[string]string) {
		if header.EventName == "PROCESS_STATE_RUNNING" {
			if payload["pid"] == fmt.Sprint(os.Getpid()) {
				return
			}

			// 10 minutes pidstat, it'll exit if process exit
			// -r memory
			// -s stack utilization
			// -d io
			// -u cpu
			// -w task switch kernel
			// -v kernel tables
			// -I so that -u values are divided by the number of cpus
			pidStatCmd := exec.Command("/usr/bin/pidstat", "-I", "-H", "-h", "-r", "-d", "-u", "-p", payload["pid"], "1")

			f, err := os.Create(fmt.Sprintf("./monit-%s-%s.txt", payload["processname"], fmt.Sprint(time.Now().Unix())))
			if err != nil {
				panic(err)
			}

			pidStatCmd.Stdout = f
			err = pidStatCmd.Start()
			if err != nil {
				panic(err)
			}

			go func() {
				err = pidStatCmd.Wait()
				// fmt.Printf("Command finished with error: %v", err)
				fmt.Fprintf(os.Stderr, "Command finished with error: %v", err)
				// @TODO : run csv extract
			}()
		}
	})
	handler.Start()
}
