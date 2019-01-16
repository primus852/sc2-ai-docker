SC2 Agent for Reinforced Learning
=================================
todo: Fill up this Readme
-------------------------

## Set up Google Cloud

1. Connect via SSH
2. `sudo apt update && sudo apt upgrade`
3. `sudo apt install git python3-pip python3-opencv screen ubuntu-drivers-common`
4. Answer OpenCV Install Questions
5. `ubuntu-drivers devices`
6. Select the driver you need
7. `sudo ubuntu-drivers autoinstall`
8. `sudo reboot`
9. `sudo apt install nvidia-cuda-toolkit gcc-6`
10. Confirm Version `nvcc --version`
11. `cd ~ && mkdir cudnn`
12. `gsutil rsync gs://sc2-ai-cudnn ~/cudnn`
13. `git clone https://github.com/primus852/sc2-ai-docker`
14. `gsutil -m rsync gs://sc2-ai-data ~/sc2-ai-docker/sc2ai/agent/agent/data`
15. `cd sc2-ai-docker/sc2ai/agent/agent && mkdir logs && cd ..`
16. `pip3 install -r requirements.txt`
17. `sudo python3 setup.py install`
18. `screen -S train`
19. `cd agent && python3 model.py`
20. Detach `ctrl + a + d`




