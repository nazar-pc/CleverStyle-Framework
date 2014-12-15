[![Build Status](https://travis-ci.org/nazar-pc/CleverStyle-CMS.png?branch=master)](https://travis-ci.org/nazar-pc/CleverStyle-CMS)
# What is this?

CleverStyle CMS is simple, scalable, and fast content management system.

System core contains only basic functionality, so, it looks more like CMF (content management framework) rather than CMS.

But in parallel with core some components are developed:

#### Modules

 * **Blogs** (simple blogging functionality)
 * **Comments** (adds comments functionality to other modules)
 * **Content** (simple content functionality. May be used by other components or stand-alone)
 * **Cron** (provides GUI for crontab, scheduled tasks)
 * **Deferred tasks** (Deferred tasks allows other components to create tasks, that can be executed not immediately, but little bit later)
 * **Disqus** (Integration of Disqus commenting service, adds comments functionality to other modules)
 * **Feedback** (simple feedback module, sends message to admin's email)
 * **HybridAuth** (integration of [HybridAuth](https://github.com/hybridauth/hybridauth) library for integration with social networks and other services)
 * **OAuth2** (provides realization of OAuth 2 authorization protocol (server side))
 * **Photo gallery** (simple photo gallery module, powered by Fotorama, Plupload and SimpleImage components)
 * **Plupload** (integration of [Plupload](https://github.com/moxiecode/plupload) for files uploading, adds files uploading functionality to other modules)
 * **Polls** (provides polls functionality to other modules)
 * **Static pages** (allows to create static pages like About page or pages without interface, for example for site owner verification)

#### Plugins

 * **Fotorama** (integration of [Fotorama](https://github.com/artpolikarpov/fotorama) jQuery gallery into CleverStyle CMS)
 * **SimpleImage** (integration of [SimpleImage](https://github.com/claviska/SimpleImage) class into CleverStyle CMS)
 * **TinyMCE** (integration of [TinyMCE](https://github.com/tinymce/tinymce) WYSIWYG Editor for providing simpler content editing)

#### Themes

 * **DarkEnergy** (Dark theme used on CleverStyle.org)
 * **Tenebris** (Another dark theme designed by Dmitry Kirsanov)

System is free, Open Source and is distributed under MIT license, see [license.txt](https://github.com/nazar-pc/CleverStyle-CMS/blob/master/license.txt)

Installation builds of core and components can be found on [downloads page](https://github.com/nazar-pc/CleverStyle-CMS/wiki/Download-installation-packages).

Author – Nazar Mokrynskyi <nazar@mokrynskyi.com>

Copyright (c) 2011-2014, Nazar Mokrynskyi

# Why?
There are thousands of other CMS and CMF, that is true, but this one is different from many because of ideas that lies in development.

### Simple
Really simple.

There is no need to make it more complicated, add more abstractions that is necessary, or write code to support some architecture.

Install only what you really need, write only code that you'll really use.

### Standalone
System contains everything necessary inside to ease setup and to be independent from outside uncontrolled services.

No CDNs for JS and fonts in system core, no composer, bower or any other packages needed to be installed from command line.

Just take SINGLE installation file, and open it from web browser or run from command line to install system.

However, if you add some composer packages to your project - system will understand that and all your packages become available immediately in any place of system.

### Working
Fresh system just works.

Reasonable defaults allows you to use all system capabilities out of the box.

There is no need to configure cache, storages, anything else.

You can start from default, and when you're ready - you can switch to another cache engine, storage engine, add another database, or redefine almost every aspect of System.

You can do that at any time, keeping possibility of system upgrade.

But prior to that moment you do not need to change anything - it will work just fine.

### Customizable
You can subscribe to some events in system and do some operations at the same time or change default flow of things as needed.

You can redefine how it looks to any imaginable extent.

You can write code in any style you like, there are recommendations how to do that, but actually you do not forced to create classes on any small stupid thing, or avoid classes when you want them to be.

You can!

### Small
System core in extracted form have around 1.8 MiB (distributive file is up to 400 KiB), that is comparable to other projects that are called just "libraries".

There are no huge list of external dependencies, there are no external dependencies at all.

Nevertheless, system includes a set of thirdparty components inside to make system complete and in order to not reinvent a good wheel.

### Clean
System track all files, DB tables and other things created in result of components functioning.

This means that if you installed some component, used it for a year, and then completely remove - system will be exactly in the same state as before installation.

This results in constant and predictable performance (no degradation with time), efficient disk space usage (no forgotten files, cache items, configs and stuff like this) and full control over system state.

# Main features

* Components
 * Modules (for displaying main page content)
 * Plugins (are loaded on every page and provides additional functionality)
 * Blocks (are placed on around the page for displaying additional information)
* Human readable addresses
* Users groups and types
* Users permissions control
* Localization and internationalization (both interface and content)
* CSS and JavaScript minification and autocompression
* Site mirrors
 * Domain mirrors
 * Physical server mirrors for every domain name
* Themes
* Multiple Databases
 * Multiple Databases mirrors
* Multiple files storages
* System cache (FileSystem, APC, Memcached)
* IP filtering and restriction
* RESTful API
* IPv6

# Requirements:

* Unix-like operating system or Windows (not well tested, but should work anyway)
* Apache2 or Nginx web server ([Nginx config sample](https://github.com/nazar-pc/CleverStyle-CMS/wiki/Nginx-config-sample))
* PHP 5.4+
 * Mcrypt library (recommended for encryption)
 * APC (Alternative PHP cache) module (recommended for system speed up with PHP 5.4)
 * Memcached (optionally for Memcached cache engine)
* or HHVM 3.3.2+ LTS / HHVM 3.4.1+
* MySQL Database server (MariaDB will work as well)
 * System may be extended to support other databases
 
# How to try?

It is possible to try latest git version of CleverStyle CMS without even installing it using [Docker](https://www.docker.com/), just type in terminal:
```bash
$ docker run --rm -p 8888:8888 nazarpc/cleverstyle-cms
```
And go to `http://localhost:8888`, sign in using login `admin` and password `1111`.

`--rm` means that container will be removed after stopping (you can stop it with `Ctrl+C`).

# How to install?

[Read simple instructions in our wiki](https://github.com/nazar-pc/CleverStyle-CMS/wiki/Installation)
