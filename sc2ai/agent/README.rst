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
13. `cd cudnn && sudo dpkg -i libcudnn7_7.4.2.24-1+cuda10.0_amd64.deb`
14. `git clone https://github.com/primus852/sc2-ai-docker`
15. `gsutil -m rsync gs://sc2-ai-data ~/sc2-ai-docker/sc2ai/agent/agent/data`
16. `cd sc2-ai-docker/sc2ai/agent/agent && mkdir logs && cd ..`
17. `pip3 install -r requirements.txt`
18. `sudo python3 setup.py install`
19. `screen -S train`
20. `cd agent && python3 model.py`
21. Detach `ctrl + a + d`




