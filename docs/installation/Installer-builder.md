You can create own installation package with desired set of components, themes and probably some tweaks.

To do that, you can use installed CleverStyle Framework or even non-installed.

1a\. If you have installed system - just copy to the root directory of your website next files/folders from this repository:
 * **build**
 * **install**
 * **build.php**
 * **install.php**

1b\. If you haven't installed system - just clone this repository or copy its contents to the root directory of desired website

2\. Make sure, that `phar.readonly` option in `php.ini` is set to `Off` (this is needed in order to create installation package, that is phar file)

3a\. Allow opening of *build.php* file directly, for example by adding next lines into *.htaccess* file in root directory of the site:
```
<Files build.php>
    RewriteEngine Off
</Files>
```
Than go to **http\://my.website/build.php**.

On this page you can make installer of CleverStyle Framework. There are several possible modes:
* Core: creates installer of CleverStyle Framework; if no modules selected - it will be only core, but you can also create installer with built in components (several items can be selected) \*
* Module: creation of module installer; obtained package is used to install module in administration panel of CleverStyle Framework

Select desired mode and click **Build**.

3b\. Alternatively, instead of web interface you can use command line interface: `php build.php`, and read further instructions.

That's it! Created package corresponding name will appear in root directory of website.

\* Module should have correct meta.json file, otherwise you can't create its installer.
