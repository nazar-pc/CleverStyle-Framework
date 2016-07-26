#!/bin/bash

# If volume present - copy files there
if [ -d '/web' ]; then
	cp -a /web_tmp/* /web
	rm -rf /web_tmp
# If no volume - just rename
else
	mv /web_tmp /web
fi

chown www-data:www-data -R /web

service mysql start
service php7.0-fpm start
service nginx start
