package main

import (
	"fmt"
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
			pidStatCmd := exec.Command("/usr/bin/pidstat", "-H", "-h", "-r", "-s", "-d", "-u", "-w", "-v", "-p", payload["pid"], "1", "600")

			f, err := os.Create(fmt.Sprintf("./monit-%s.txt", payload["processname"]))
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
				fmt.Printf("Command finished with error: %v", err)
				// @TODO : run csv extract
			}()
		}
	})
	handler.Start()
}
