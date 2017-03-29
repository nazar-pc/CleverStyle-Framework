To install CleverStyle Framework you have to have installation package, which has extension **\*.phar.php**.

Latest build of installer may be found on [downloads page](/docs/installation/Download-installation-packages.md) of this repository or you can [prepare your own](/docs/installation/Installer-builder.md) installation package.

### GUI way
Put installer to the root directory of future website. And open this file from web browser.

For example:
* Installation package is **CleverStyle_Framework.phar.php**
* Placed in directory **/web/my.website/public_html**
* This directory corresponds to website domain **my.website**
* Open web browser and go to **http\://my.website/CleverStyle_Framework.phar.php**
* Fill several fields and click **Install**
* That's it! You have installed CleverStyle Framework

If you are using Nginx - here is [Nginx config sample](/docs/installation/Nginx-config-sample.md) with necessary configuration for installation and usage.

### CLI way
Alternative way to install CleverStyle Framework is by using command line.

Put installer to the root directory of future website. Open command line (terminal), move to website directory and run installation in such way:
```bash
php CleverStyle_Framework.phar.php -sn Web-site -su http://web.site -dn web.site -du web.site -dp pass -ae admin@web.site -ap pass
```

To see all available parameters and details about they usage execute command without any parameters:
```bash
php CleverStyle_Framework.phar.php
```

### HHVM
Using HipHip Virtual Machine currently you can only install it using CLI way above, replacing `php` with `hhvm`:
```bash
hhvm CleverStyle_Framework.phar.php -sn Web-site -su http://web.site -dn web.site -du web.site -dp pass -ae admin@web.site -ap pass
```

But please, make sure you have supported version: HHVM 3.3.2+ LTS / HHVM 3.4.1+

### TROUBLESHOOTING:

#### If after successful installation you go to website and get *Internal Server Error* - most likely you have disabled "rewrite" module in Apache2 web-server.

On Ubuntu you can enable it by typing in terminal:
```bash
sudo a2enmod rewrite
```
If you are using shared hosting - ask support to enable `rewrite` module.

#### after installation you see page without styles and JavaScript not working (page looks broken and sign in doesn't work) : If most likely you are using Apache2 and have in virtual host such line:
```
AllowOverride None
```
To get system working allow CleverStyle Framework override some parameter by changing above line to:
```
AllowOverride All
```

#### If you get error like

> Parse error: syntax error, unexpected '~' in ../../CleverStyle_Framework.phar.php on line 123

This means you have too old version of PHP, please upgrade at least to 7.0 (minimum supported at the moment by CleverStyle Framework) or better latest stable.
