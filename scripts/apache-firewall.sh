#!/bin/bash

#MAKE A COPY OUTSIDE REPO AND CHANGE THESE THERE
RABBIT_IP=""

#Installs ufw if doesn't exsist
if ! command -v ufw &> /dev/null
then
    echo "ufw not found, installing..."

    sudo apt update
    sudo apt install -y ufw
fi
sudo ufw --force reset
sudo ufw default deny incoming
sudo ufw default allow outgoing

sudo ufw allow ssh
sudo ufw allow http
sudo ufw allow https

#rabbit specific rules
sudo ufw allow out to $RABBIT_IP to any port 5672
sudo ufw allow out to $RABBIT_IP to any port 5671


sudo ufw enable

sudo ufw status verbose
~                         
