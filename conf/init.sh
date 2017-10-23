#!/bin/sh

cron="/etc/cron.d/nginx-cron"
echo "* * * * * root sh /root/nginx.sh >> /var/log/cron.log 2>&1" >> $cron
echo "" >> $cron
chmod 0644 $cron
touch /var/log/cron.log

ssh="/var/www/.ssh/"
mkdir $ssh
chmod 0700 $ssh

conf="/var/www/.ssh/config"
echo "StrictHostKeyChecking no" >> $conf
echo "UserKnownHostsFile /dev/null" >> $conf
chmod 0600 $conf

chown -R www-data:www-data $ssh

mkdir /var/www/.cache/
chown -R www-data:www-data /var/www/.cache/

mkdir /var/www/src/audio/
mkdir /var/www/src/magic/
mkdir /var/www/src/users/
mkdir /var/www/src/repos/
mkdir /var/www/src/video/

touch /var/www/src/auth.log

chown -R www-data /var/www/src/
