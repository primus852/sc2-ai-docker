FROM ubuntu:18.04

MAINTAINER puzzles852

ENV DEBIAN_FRONTEND noninteractive

# Get Client Version
ENV SC2PATH /sc2ai/StarCraftII/

RUN apt-get update && apt-get install -yq \
    apt-utils \
    curl \
    # Install git
    git \
	apache2 \
    # Install php 7.2
    libapache2-mod-php7.2 \
    php7.2-cli \
    php7.2-json \
    php7.2-curl \
    php7.2-sqlite3 \
    php7.2-xml \
    php7.2-zip \
    php7.2-intl \
    # Install Python
    python3-minimal \
    python3-pip \
    # Install tools
    nano \
    wget \
    unzip \
	nodejs \
    npm \
    locales \
    && apt-get clean && apt-get autoremove -yq
	
# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Set locales
RUN locale-gen en_US.UTF-8 en_GB.UTF-8 de_DE.UTF-8 es_ES.UTF-8 fr_FR.UTF-8 it_IT.UTF-8 km_KH sv_SE.UTF-8 fi_FI.UTF-8

# Apache Settings
# Update the PHP.ini file, enable <? ?> tags and quieten logging.
RUN sed -i "s/short_open_tag = Off/short_open_tag = On/" /etc/php/7.2/apache2/php.ini
RUN sed -i "s/error_reporting = .*$/error_reporting = E_ERROR | E_WARNING | E_PARSE/" /etc/php/7.2/apache2/php.ini
RUN sed -ie 's/memory_limit\ =\ 128M/memory_limit\ =\ 2G/g' /etc/php/7.2/apache2/php.ini
RUN sed -ie 's/\;date\.timezone\ =/date\.timezone\ =\ Europe\/Berlin/g' /etc/php/7.2/apache2/php.ini
RUN sed -ie 's/upload_max_filesize\ =\ 2M/upload_max_filesize\ =\ 200M/g' /etc/php/7.2/apache2/php.ini
RUN sed -ie 's/post_max_size\ =\ 8M/post_max_size\ =\ 200M/g' /etc/php/7.2/apache2/php.ini

# Manually set up the apache environment variables
ENV APACHE_RUN_USER www-data
ENV APACHE_RUN_GROUP www-data
ENV APACHE_LOG_DIR /var/log/apache2
ENV APACHE_LOCK_DIR /var/lock/apache2
ENV APACHE_PID_FILE /var/run/apache2.pid

# Python Packages (specific for Custom Agent https://github.com/primus852/SC2-AI-Reinforced)
RUN pip3 install pandas
RUN pip3 install numpy
RUN pip3 install Pillow
RUN pip3 install matplotlib
RUN pip3 install peewee

# Install PySC2 https://github.com/deepmind/pysc2
RUN pip3 install pysc2

WORKDIR /sc2ai
