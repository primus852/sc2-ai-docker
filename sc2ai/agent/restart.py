import os
import psutil
import time


def checkIfProcessRunning(processName):
    '''
    Check if there is any running process that contains the given name processName.
    '''
    # Iterate over the all the running process
    for proc in psutil.process_iter():
        try:
            # Check if process name contains the given name string.
            if processName.lower() in proc.name().lower():
                return True
        except (psutil.NoSuchProcess, psutil.AccessDenied, psutil.ZombieProcess):
            pass
    return False


if __name__ == '__main__':

    while 1:
        if not checkIfProcessRunning('pysc2.bin.agent'):
            print('No chrome process was running')
            # os.system("python3 -m pysc2.bin.agent --map Simple64 --agent agent.refined.SparseAgent --agent_race terran --norender")

        time.sleep(1)
