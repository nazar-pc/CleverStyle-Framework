Sample of Nginx config file for Ubuntu (should be the same or very similar on any other OS):
```
server {
	listen		80;
	server_name	example.com;
	root		/path/to/example.com/public_html;
	index		index.php;
	error_log	/path/to/example.com/error.log info;
	# Point any URI to index.php
	location / {
		try_files $uri $uri/ /index.php?$args;
	}
	# Allow access to root index.php
	location ~ ^/index\.php {
		include			fastcgi_params;
		# Uncomment next line for PHP-FPM
		# fastcgi_pass	unix:/var/run/php5-fpm.sock;
		# Uncomment next line for HHVM
		# fastcgi_pass	127.0.0.1:9000;
		fastcgi_index	index.php;
		fastcgi_param	SCRIPT_FILENAME $document_root$fastcgi_script_name;
	}
	location ~ ^/WebSockets$ {
		proxy_pass			http://127.0.0.1:8080;
		proxy_http_version	1.1;
		proxy_set_header	Upgrade $http_upgrade;
		proxy_set_header	Connection	"upgrade";
		proxy_buffering		off;
	}
	# For GUI installation with PHP-FPM, uncomment to allow installing (and comment back after installation!!!)
	#location ~ (.*\.phar\.php) {
	#	include			fastcgi_params;
	#	fastcgi_pass	unix:/var/run/php5-fpm.sock;
	#	fastcgi_param	SCRIPT_FILENAME $document_root$1;
	#}
	# Add necessary headers
	location /storage/pcache {
		location ~ \.(css|js|html)$ {
			add_header Cache-Control "max-age=2592000, public";
		}
		return 403;
	}
	# Some headers to improve security when dealing with downloaded content
	location /storage/public {
		add_header X-Frame-Options DENY;
		add_header Content-Type application/octet-stream;
	}
	# Exclude files inside temp directory and directory for public uploads from next rule
	location ~ ^/storage/(temp|public)/.*\.php$ {}
	# Deny access to all *.php files
	location ~ \.php$ {
		error_page 404 = /index.php;
		return 404;
	}
	# Deny access to hidden files
	location ~ /\. {
		deny all;
		access_log off;
		log_not_found off;
	}
	# Deny access to directories config, core, storage/cache
	location ~ ^/(config|core|storage/cache) {
		return 404;
	}
	# Exclude html imports (for Web Components) from next rule
	location ~ ^/modules/.*includes/html/.*\.html$ {}
	# Deny access to other *.json, *.html, *.txt files inside components directory
	location ~ ^/modules/.*\.(json|html|txt)$ {
		return 404;
	}
}
```
Change `example.com` and `/path/to/example.com/` to desired values.

Change `8080` to port where WebSockets server stating (if you do not use WebSockets module - corresponding section can be removed).

Uncomment in `location ~ ^/index\.php {` section line that corresponds to your interpreter (PHP-FPM or HHVM).
