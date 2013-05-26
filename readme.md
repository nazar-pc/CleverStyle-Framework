What is this?
=

CleverStyle CMS is simple, scalable, and fast content management system.

Current version is Alpha.

System core contains only basic functionality, so, it looks more like CMF (content management framework) rather than CMS.

But in parallel with core some components are developed:

#### Modules

 * **Blogs** (simple blogging functionality)
 * **Comments** (adds comments functionality to other modules)
 * **Cron** (provides GUI for crontab, scheduled tasks)
 * **HybridAuth** (integration of [HybridAuth](https://github.com/hybridauth/hybridauth) library for integration with social networks and other services)
 * **OAuth2** (provides realization of OAuth 2 authorization protocol (server side))
 * **Static pages** (allows to create static pages like About page or pages without interface, for example for site owner verification)

#### Plugins

 * **Admin default theme** (forces default CleverStyle theme in all administration pages, that is helpful, because there is no need to adapt custom theme for admin panel)
 * **TinyMCE** (integration of [TinyMCE](https://github.com/tinymce/tinymce) WYSIWYG Editor for providing simpler content editing)

System is free, Open Source and is distributed under MIT license, see [license.txt](https://github.com/nazar-pc/CleverStyle-CMS/blob/master/license.txt)

Installation builds of core and components can be found on [downloads page](https://github.com/nazar-pc/CleverStyle-CMS/wiki/Download-installation-packages).

Author – Nazar Mokrynskyi <nazar@mokrynskyi.com>

Copyright (c) 2011-2013, Nazar Mokrynskyi

Main features
=

* Components support
 * Modules support (for displaying main page content)
 * Plugins support (are loaded on every page and provides additional functionality)
 * Blocks support (are placed on around the page for displaying additional information)
* Human readable addresses support
* Users groups and types support
* Users permissions control support
* Complete multilingual support (both interface and content)
 * Content autotranslation support
* CSS and JavaScript minification and autocompression support
* Site mirrors support
 * Domain mirrors support
 * Support of physical server mirrors for every domain name
* Themes and color schemes support
* Multiple Databases support
 * Multiple Databases mirrors support
* Multiple files storages support
* System cache support
* IP filtering and restriction support
* External site API support
* IPv6 support

Requirements:
=

* Unix-like operating system
* Apache2 web server
* PHP 5.4+
 * Mcrypt library (recommended for encryption)
 * cUrl library (recommended for autotranslation)
 * APC (Alternative PHP cache) module (recommended for system speed up)
 * Memcached module (recommended for caching)
* MySQL Database server
 * System may be extended to support other databases

How to install?
=

[Read simple instructions in our wiki](https://github.com/nazar-pc/CleverStyle-CMS/wiki/Installation)
