import os
import time

while 1:
    grep = os.system("ps -ef | grep pysc2.bin.agent")
    if grep == 0:
        # os.system("python3 -m pysc2.bin.agent --map Simple64 --agent agent.refined.SparseAgent --agent_race terran --norender")
        print('Not Running')
    else:
        print('Grep: '+grep)
    time.sleep(1)
