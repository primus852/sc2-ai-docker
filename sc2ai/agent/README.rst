SC2 Agent for Reinforced Learning
=================================
todo: Fill up this Readme
-------------------------

## Set up Google Cloud

1. Connect via SSH
2. `sudo apt update && sudo apt upgrade`
3. `sudo apt install git python3-pip python3-opencv screen`
4. Answer OpenCV Install Questions
5. `git clone https://github.com/primus852/sc2-ai-docker`
6. `gsutil -m rsync gs://sc2-ai-data ~/sc2-ai-docker/sc2ai/agent/agent/data`
7. `cd sc2-ai-docker/sc2ai/agent/agent && mkdir logs && cd ..`
8. `pip3 install -r requirements.txt`
9. `sudo python3 setup.py install`
10. `screen -S train`
11. `cd agent && python3 model.py`
12. Detach `ctrl + a + d`



