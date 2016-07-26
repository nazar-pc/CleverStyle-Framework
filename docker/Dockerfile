FROM phusion/baseimage:0.9.19
MAINTAINER Nazar Mokrynskyi <nazar@mokrynskyi.com>

RUN	export DEBIAN_FRONTEND=noninteractive && \

# Update packages list, upgrade installed packages

	apt-get update && \
	apt-get -y dist-upgrade && \

# Install MySQL, Nginx, PHP (both fpm and CLI)

	apt-get install -y --no-install-recommends \
		mariadb-server-10.0 \
		nginx \
		php-cli php-curl php-gd php-fpm php-mbstring php-mysql php-xml \
		wget && \
	rm -rf /var/lib/apt/lists/* && \

# Download git master version of CleverStyle Framework

	cd /tmp && \
	wget https://github.com/nazar-pc/CleverStyle-Framework/archive/master.tar.gz && \
	tar -xf master.tar.gz && \
	rm master.tar.gz && \
	cd CleverStyle-Framework-master && \

# Get list of available modules and themes

	cd modules && \
	MODULES=`ls -md */ | tr -d '\n /'` && \
	cd ../themes && \
	THEMES=`ls -md */ | tr -d '\n /'` && \
	cd .. && \

# Allow to build phar archives

	sed -i 's/;phar.readonly = On/phar.readonly = Off/g' /etc/php/7.0/cli/php.ini && \

# Build distributive with all available modules and themes

	php build.php -M core -m $MODULES -t $THEMES && \

# Create directory for future website, move distributive there, remove unnecessary files

	mkdir /web && \
	mv *.phar.php /web/dist.phar.php && \
	cd /web && \
	rm -rf /tmp/CleverStyle-Framework-master && \

# Run MySQL server, create database

	service mysql start && \
	mysql -uroot -e 'CREATE DATABASE `cscms`;' && \

# Allow root user to authenticate from php

	mysql -uroot -e "UPDATE mysql.user SET plugin = '';" && \

# Install CleverStyle Framework

	php dist.phar.php \
		--site_name      "CleverStyle Framework Demo (not for production, admin:1111)" \
		--site_url       http://localhost \
		--db_name        cscms \
		--db_user        root \
		--db_password    '' \
		--admin_email    admin@localhost.loc \
		--admin_password 1111 && \

# Move to special directory for volume support on running

	mv /web /web_tmp

# Add custom file that will allow to open demo from any host
ADD any_host.php /web_tmp/custom/any_host.php

# Add Nginx config
ADD nginx.default /etc/nginx/sites-available/default

# Add services execution script
ADD start_webserver.sh /etc/my_init.d/start_webserver.sh

# Make startup script executable
RUN chmod +x /etc/my_init.d/start_webserver.sh
