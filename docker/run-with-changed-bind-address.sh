#!/bin/bash

# change bind-address in mysql config to make it available from outside
sed -i 's/bind-address/# bind-address/' /etc/mysql/mysql.conf.d/mysqld.cnf

echo "" >> /etc/mysql/mysql.conf.d/mysqld.cnf
echo "sql_mode = STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION" >> /etc/mysql/mysql.conf.d/mysqld.cnf

/run.sh

