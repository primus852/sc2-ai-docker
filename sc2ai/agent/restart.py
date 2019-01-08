import os
import time

while 1:
    grep = os.system("ps -ef | grep pysc2.bin.agent")
    if grep == 0:
        os.system("cd /sc2ai/agent && python3 -m pysc2.bin.agent --map Simple64 --agent agent.refined.SparseAgent --agent_race terran --norender")
    time.sleep(1)
