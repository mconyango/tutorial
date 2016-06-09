# how to set-up & run the project
The following will guide you through setting up the project on your local PC. If you already have the project on your PC, check the #Other setup section below
# pre-requisites
+ php version 5.5+
+ Mysql version 5.5+
+ composer. (https://getcomposer.org/download/)
+ bower. (bower requires nodejs, so ensure that you have it first. Follow the steps below to install both)
```sh
curl -sL https://deb.nodesource.com/setup_5.x | sudo -E bash -
sudo apt-get install -y nodejs
sudo npm install bower -g
```
+ An empty mysql database. Make sure that the mysql user configured has access to it

## Perform the following in your terminal
```sh
# clone from bitbucket
git clone https://fredmconyango@bitbucket.org/btimillman/tutorial.git

# cd into the folder created. Defaults to tutorial
cd tutorial

# verify your database credentials, by editing the db config file
vi _protected/common/config/db.php

# import the mysql database dump. Ensure that you have the database named hop_db first
mysql -u root tutorial -p < _protected/data/tutorial.sql

# install composer dependencies
composer install

# install bower dependencies
bower install --allow-root

# Congatulations! you're done
```

### install/update dependencies
```sh
composer update
bower update
```