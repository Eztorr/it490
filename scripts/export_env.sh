#!/bin/bash


#add the correct values to be exported as environment variables to be used in testRabbitMQ.ini
#copy this file into in your VM outside of the repository, DO NOT PUSH CHANGES TO THIS FILE UNLESS IT IS ADDING AN ADDITION VARIABLE
#run this script to make the changes
#EXAMPLE export rabbitmq_server_ip=111.111.111.111

export rabbitmq_server_ip=<rabbit-server-ip>

export rabbitmq_user=<username-for-rabbit>

export rabbitmq_password=<password-for-rabbit>

#These are for later use for the db access, commented out for now
#export db_user=<db-username>
#
#export db_password=<db-password>
#
#export db_database=<db-database-name>
