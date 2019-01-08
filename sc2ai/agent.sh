#!/bin/sh

COMMAND="cd /sc2ai/agent || python3 -m pysc2.bin.agent --map Simple64 --agent agent.refined.SparseAgent --agent_race terran --norender"
LOGFILE=restart.txt

writelog() {
  now=`date`
  echo "$now $*" >> $LOGFILE
}

writelog "Starting"
while true ; do
  $COMMAND
  sleep 1
  writelog "Exited with status $?"
  writelog "Restarting"
done