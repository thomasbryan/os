#!/bin/sh
cron="/etc/cron.d/nginx-cron"
if [ ! -f $cron ];then
  echo "* * * * * root sh /root/nginx.sh >> /var/log/cron.log 2>&1" >> $cron
  echo "" >> $cron
fi
chmod 0644 $cron
touch /var/log/cron.log
www="/var/www/"
ssh=$www".ssh/"
if [ ! -d $ssh ];then
  mkdir $ssh
fi
chmod 0700 $ssh
conf=$ssh"config"
if [ ! -f $conf ];then
  echo "StrictHostKeyChecking no" >> $conf
  echo "UserKnownHostsFile /dev/null" >> $conf
  chmod 0600 $conf
fi
chown -R www-data:www-data $ssh
cache=$www".cache/"
if [ ! -d $cache ];then
  mkdir $cache
  chown -R www-data:www-data $cache
fi
src=$www"src/"
for d in "audio" "magic" "home" "repos" "video"; do
  if [ ! -d $src$d"/" ];then mkdir $src$d"/"; fi
done
touch $src"auth.log" $src"users.ini" $src"roles.ini"
sys=$src"sys.ini"
if [ ! -f $sys ];then
  key=`cat /dev/urandom|tr -dc 'a-zA-Z0-9'|fold -w 32|head -n 1`
  echo "key = "$key >> $sys
  echo "admin = " >> $sys
  echo "users = 1" >> $sys
fi
chown -R www-data $src
