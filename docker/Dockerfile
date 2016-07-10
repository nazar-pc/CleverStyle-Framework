FROM phusion/baseimage:0.9.19
MAINTAINER Nazar Mokrynskyi <nazar@mokrynskyi.com>

RUN	export DEBIAN_FRONTEND=noninteractive && \

# Update packages list, upgrade installed packages

	apt-get update && \
	apt-get -y dist-upgrade && \

# Generate password for MySQL

	apt-get install -y pwgen && \
	test ! -z "$MYSQL_PASSWORD" || MYSQL_PASSWORD=`pwgen -s 20 1` && \
	echo mysql-server mysql-server/root_password password $MYSQL_PASSWORD | debconf-set-selections && \
	echo mysql-server mysql-server/root_password_again password $MYSQL_PASSWORD | debconf-set-selections && \

# Install MySQL, Nginx, PHP (both fpm and CLI)

	apt-get install -y --no-install-recommends \
		mariadb-server \
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
	mysql -uroot -p$MYSQL_PASSWORD -e 'CREATE DATABASE `cscms`;' && \

# Install CleverStyle Framework

	php dist.phar.php \
		--site_name      "CleverStyle Framework Demo (not for production, admin:1111)" \
		--site_url       http://localhost:8888 \
		--db_name        cscms \
		--db_user        root \
		--db_password    $MYSQL_PASSWORD \
		--admin_email    admin@localhost.loc \
		--admin_password 1111 && \

# Move to special directory for volume support on running

	mv /web /web_tmp

# Add Nginx config
ADD cscms /etc/nginx/sites-enabled/cscms

# Add services execution script
ADD start_webserver.sh /etc/my_init.d/start_webserver.sh

# Make startup script executable
RUN chmod +x /etc/my_init.d/start_webserver.sh
