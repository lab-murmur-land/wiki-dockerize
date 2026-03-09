#! /usr/bin/bash

SITE='/var/www/wiki.murmur.land/'

if [ ! -d $SITE ]; then
	mkdir -p $SITE
else
	echo "$SITE dir already exist"
fi
