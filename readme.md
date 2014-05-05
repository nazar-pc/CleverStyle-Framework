[![Build Status](https://travis-ci.org/nazar-pc/CleverStyle-CMS.png?branch=master)](https://travis-ci.org/nazar-pc/CleverStyle-CMS)
What is this?
=

CleverStyle CMS is simple, scalable, and fast content management system.

Current version is Beta.

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
 * **Static pages** (allows to create static pages like About page or pages without interface, for example for site owner verification)

#### Plugins

 * **Admin default theme** (forces default CleverStyle theme in all administration pages, that is helpful, because there is no need to adapt custom theme for admin panel)
 * **Fotorama** (integration of [Fotorama](https://github.com/artpolikarpov/fotorama) jQuery gallery into CleverStyle CMS)
 * **SimpleImage** (integration of [SimpleImage](https://github.com/claviska/SimpleImage) class into CleverStyle CMS)
 * **TinyMCE** (integration of [TinyMCE](https://github.com/tinymce/tinymce) WYSIWYG Editor for providing simpler content editing)

System is free, Open Source and is distributed under MIT license, see [license.txt](https://github.com/nazar-pc/CleverStyle-CMS/blob/master/license.txt)

Installation builds of core and components can be found on [downloads page](https://github.com/nazar-pc/CleverStyle-CMS/wiki/Download-installation-packages).

Author – Nazar Mokrynskyi <nazar@mokrynskyi.com>

Copyright (c) 2011-2014, Nazar Mokrynskyi

Main features
=

* Components
 * Modules (for displaying main page content)
 * Plugins (are loaded on every page and provides additional functionality)
 * Blocks (are placed on around the page for displaying additional information)
* Human readable addresses
* Users groups and types
* Users permissions control
* Localization and internationalization (both interface and content)
 * Content autotranslation
* CSS and JavaScript minification and autocompression
* Site mirrors
 * Domain mirrors
 * Physical server mirrors for every domain name
* Themes and color schemes
* Multiple Databases
 * Multiple Databases mirrors
* Multiple files storages
* System cache (FileSystem, APC, Memcached)
* IP filtering and restriction
* RESTful API
* IPv6

Requirements:
=

* Unix-like operating system or Windows (not well tested, but should work anyway)
* Apache2 or Nginx web server ([Nginx config sample](https://github.com/nazar-pc/CleverStyle-CMS/wiki/Nginx-config-sample))
* PHP 5.4+
 * cUrl library
 * Mcrypt library (recommended for encryption)
 * APC (Alternative PHP cache) module (recommended for system speed up with PHP 5.4)
 * Memcached (optionally for Memcached cache engine)
* MySQL Database server (MariaDB will work as well)
 * System may be extended to support other databases

How to install?
=

[Read simple instructions in our wiki](https://github.com/nazar-pc/CleverStyle-CMS/wiki/Installation)
