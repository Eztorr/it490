#!/bin/bash

#MAKE A COPY OUTSIDE REPO AND CHANGE THESE THERE
APACHE_IP=""
DB_IP=""
ADMIN_IP="" #ip of rabbit machine hosting admin panel

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
sudo ufw allow from $APACHE_IP to any port 5672
sudo ufw allow from $APACHE_IP to any port 5671
sudo ufw allow from $DB_IP to any port 5672
sudo ufw allow from $DB_IP to any port 5671

#this is for rabbit admin panel
sudo ufw allow from $ADMIN_IP to any port 15672

sudo ufw enable 

sudo ufw status verbose
