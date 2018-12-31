FROM ubuntu:18.04

MAINTAINER puzzles852

# Get Client Version
ENV SC2PATH /sc2ai/StarCraftII/

RUN apt-get update && apt-get install -yq \
    apt-utils \
    curl \
    # Install git
    git \
	# Install Python
	python3-minimal \
	python3-pip \
    # Install tools
    nano \
    wget \
    unzip \
    locales \
    && apt-get clean && apt-get autoremove -yq

# Set locales
RUN locale-gen en_US.UTF-8 en_GB.UTF-8 de_DE.UTF-8 es_ES.UTF-8 fr_FR.UTF-8 it_IT.UTF-8 km_KH sv_SE.UTF-8 fi_FI.UTF-8

# Python Packages (specific for Custom Agent https://github.com/primus852/SC2-AI-Reinforced)
RUN pip3 install pandas
RUN pip3 install numpy
RUN pip3 install Pillow
RUN pip3 install matplotlib

# Install PySC2 https://github.com/deepmind/pysc2
RUN pip3 install pysc2

WORKDIR /sc2ai
