#!/bin/sh
  prod="/etc/nginx/conf.d/"
  stage="/var/www/src/"
  conf="projects.vhost"
  if [ ! -f $prod$conf ];then 
    touch $prod$conf
  fi
  if [ ! -f $stage$conf ];then 
    touch $stage$conf
  fi
  # check changes production and stage config
  diff $prod$conf $stage$conf > /dev/null
  if [ $? -eq 1 ];then
    # changes have been made
    cache="/root/nginx/"
    if [ ! -d $cache ];then
      mkdir -p $cache
    fi
    touch $cache"log"
    # check last status
    tail -n1 $cache"log"|grep 'fail' > /dev/null
    if [ $? -eq 0 ];then
      # last status failed
      # check changes cache and stage config
      diff $stage$conf $cache"stage" > /dev/null
      if [ $? -eq 0 ];then
        # no changes have been made; lets not continue
        exit 2
      fi
    fi 
    # backup
    cp $prod$conf $cache"prod"
    cp $stage$conf $cache"stage"
    # check stage config
    cp $cache"stage" $prod$conf
    /usr/sbin/nginx -t > /dev/null 2>&1
    if [ $? -eq 1 ];then
      # test fail; revert config, log fail, exit
      echo `date +"[%d/%b/%Y:%H:%M:%S] fail"` >> $cache"log"
      cp $cache"prod" $prod$conf
      exit 2
    else
      # test success; log success, restart nginx
      echo `date +"[%d/%b/%Y:%H:%M:%S] success"` >> $cache"log"
      /etc/init.d/nginx reload
    fi
  fi