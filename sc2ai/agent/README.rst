SC2 Agent for Reinforced Learning
=================================
todo: Fill up this Readme
-------------------------

## Set up Google Cloud

1. Connect via SSH
2. `sudo apt update && sudo apt upgrade`
3. `sudo apt install git python-pip python-opencv`
4. Answer OpenCV Install Questions
5. `git clone https://github.com/primus852/sc2-ai-docker`
6. `gsutil rsync gs://sc2-ai-data ~/sc2-ai-docker/sc2ai/agent/agent/data`
7. `cd sc2-ai-docker/sc2ai/agent`
8. `pip3 install -r requirements.txt`
9. `python3 setup.py install`
10. `screen -S train`
11. `cd agent && python3 model.py`
12. Detach `ctrl + a + d`



