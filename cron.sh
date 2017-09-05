#!/bin/sh
if [ -f src/update ];then
	git pull origin master > /dev/null
	rm -rf src/update > /dev/null
fi