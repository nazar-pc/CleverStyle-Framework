[![Download CleverStyle CMS](https://img.shields.io/sourceforge/dt/cleverstyle-cms.svg?label=Downloads)](https://sourceforge.net/projects/cleverstyle-cms/files/)
[![Try Docker demo](https://img.shields.io/docker/pulls/nazarpc/cleverstyle-cms.svg?label=Docker demo pulls)](https://registry.hub.docker.com/u/nazarpc/cleverstyle-cms/)
[![SensioLabsInsight](https://img.shields.io/sensiolabs/i/eacdd55b-4a0e-4429-add5-e6a01adb12af.svg?label=SLInsight)](https://insight.sensiolabs.com/projects/eacdd55b-4a0e-4429-add5-e6a01adb12af)
[![Scrutinizer](https://img.shields.io/scrutinizer/g/nazar-pc/CleverStyle-CMS.svg?label=Scrutinizer)](https://scrutinizer-ci.com/g/nazar-pc/CleverStyle-CMS/)
[![Build Status](https://img.shields.io/travis/nazar-pc/CleverStyle-CMS/master.svg?label=Travis CI)](https://travis-ci.org/nazar-pc/CleverStyle-CMS)
[![Join the chat at https://gitter.im/nazar-pc/CleverStyle-CMS](https://badges.gitter.im/Join%20Chat.svg)](https://gitter.im/nazar-pc/CleverStyle-CMS)
# What is this?

CleverStyle CMS is simple, scalable, and fast content management framework (CMF).

System is free, Open Source and is distributed under MIT license, see [license.txt](https://github.com/nazar-pc/CleverStyle-CMS/blob/master/license.txt)

Installation builds of core and components can be found on [downloads page](/docs/Download-installation-packages.md).

Author – Nazar Mokrynskyi <nazar@mokrynskyi.com>

Copyright (c) 2011-2016, Nazar Mokrynskyi

# Philosophy
There are thousands of other CMS and CMF, that is true, but this one is different from most of them because of ideas that lies in development.

### Small and simple
Really simple.

System provides only essential minimum of abstractions to make things working while leaving freedom for developer. This ensures that system itself is easy to understand, use and develop for.

System core doesn't contain any useful end-user functionality, so despite historical name this is a framework, not typical CMS.

### Standalone
System contains everything necessary inside in order to ease setup and to be independent from external tools services.

No external tools required for system installation and operating. Every essential dependency is bundled with system core to ensure that system works out of the box.

This doesn't mean, however, that system ignores available ecosystem - Composer, Bower and NPM are all supported out of the box and will be seamlessly picked by system when their presence is detected.

### Working
Fresh system just works.

Seriously, reasonable defaults allows you to use all system capabilities out of the box with freedom to change almost any aspect of the system when needed.

### Performing
Performance is critical.

Providing superior performance of system core is a key for the best possible performance of end product.

Out of the box CleverStyle CMS can render simple web page in just under 2ms. When installing standard Http server module, changing cache to APCu/Memcached and running it under HHVM you fall under 1ms for generating simple page or API response. All without additional system tweaks.

### Convention over configuration
Zero configuration whenever it is possible.

System tries to make all trivial configuration whenever it is possible. This is is essential to make system simple to use, standalone, working out of the box and reaching top performance for free.

Obviously, you always have access to internals if you need to.

### Cleanliness
System will track everything created in result of components operating (files, database tables, cache items, etc.).

This means that you can install component, use it for some time and remove completely without any overhead. After removal system will be exactly in the same state as before installation.

This results in constant and predictable performance, efficient storage usage (no forgotten files, cache items, configs and stuff like this) and full control over state of the system.

# Key features

* Components
 * Modules - for displaying main page content
 * Plugins - do not have dedicated pages and provide additional functionality
 * Blocks - are placed on around the page for displaying additional information
* Events - ability to capture, respond or even override behavior of different aspects of the system without changing system itself
* Users, groups and permissions for granular access to functionality
* Localization and internationalization (both interface and content)
* CSS, JavaScript and HTML processing with intelligent, high-performance and completely automatic minification, compression and caching
* HTTP/2 Server Push (via preload) works automatically
* Visual themes to personalize appearance
* Multiple Databases and mirrors awareness
* Multiple static content storages awareness
* Caching everything that makes sense to put into cache
* RESTful API and CLI interfaces
* First-class WebComponents support (Polymer)
* First-class AMD support (RequireJS)
* First-class Composer, Bower and NPM support

# Components
Some components are developed in parallel with core in this repository

#### Modules

 * **Blockchain payment** (payment method using Bitcoin as cryptocurrency and API of blockchain.info)
 * **Blogs** (simple blogging functionality)
 * **Comments** (adds comments functionality to other modules)
 * **Composer** ([Composer](https://github.com/composer/composer) integration into CleverStyle CMS, allows to specify composer dependencies in meta.json that will be installed automatically)
 * **Content** (simple content functionality. May be used by other components or stand-alone)
 * **Cron** (provides GUI for crontab, scheduled tasks)
 * **Deferred tasks** (Deferred tasks allows other components to create tasks, that can be executed not immediately, but little bit later)
 * **Disqus** (Integration of Disqus commenting service, adds comments functionality to other modules)
 * **Feedback** (simple feedback module, sends message to admin's email)
 * **Http server** (Http server based on [React](https://github.com/reactphp/react), potentially consumes less memory and works much faster that mod-php5 and php-fpm (and even pure HHVM too))
 * **HybridAuth** (integration of [HybridAuth](https://github.com/hybridauth/hybridauth) library for integration with social networks and other services)
 * **OAuth2** (provides realization of OAuth 2 authorization protocol (server side))
 * **Photo gallery** (simple photo gallery module, powered by Fotorama, Plupload and SimpleImage components)
 * **Polls** (provides polls functionality to other modules)
 * **Service Worker cache** (uses Service Worker to cache requests to static assets like CSS/JS/HTML/fonts/images to improve subsequent page loads, especially on bad internet connection)
 * **Shop** (provides simple, but highly extensible and customizable shopping functionality)
 * **Static pages** (allows to create static pages like About page or pages without interface, for example for site owner verification)
 * **Uploader** (provides files uploading functionality to other modules)
 * **WebSockets** (support for WebSockets connections utilizing [React](https://github.com/reactphp/react) and [Ratchet](https://github.com/ratchetphp/Ratchet))

#### Plugins

 * **Composer assets** (Bower and NPM packages support through Composer)
 * **Fotorama** (integration of [Fotorama](https://github.com/artpolikarpov/fotorama) jQuery gallery into CleverStyle CMS)
 * **Json_ld** (simplifies some parts of JSON-LD generation)
 * **Old IE** (brings frontend polyfills and hacks to provide support for older IE versions (10 currently))
 * **Picturefill** (integration of [Picturefill](https://github.com/scottjehl/picturefill) polyfill into CleverStyle CMS)
 * **Prism** (integration of [Prism](http://prismjs.com/index.html) syntax highlighter into CleverStyle CMS)
 * **SimpleImage** (integration of [SimpleImage](https://github.com/claviska/SimpleImage) class into CleverStyle CMS)
 * **Tags** (currently contains single trait, is used by other components in order to avoid code duplication)
 * **TinyMCE** (integration of [TinyMCE](https://github.com/tinymce/tinymce) WYSIWYG Editor for providing simpler content editing)

#### Themes

 * **DarkEnergy** (Dark theme used on CleverStyle.org)
 * **Tenebris** (Another dark theme designed by Dmitry Kirsanov)

# Requirements:

* Unix-like operating system
* or Windows (not tested regularly, but should also work, do not use in production)
* Apache2 with modules:
 * REQUIRED: rewrite, headers
 * OPTIONAL: expires
* or Nginx ([config sample](/docs/Nginx-config-sample.md))
* PHP 5.6+ with libraries:
 * REQUIRED: cURL
 * OPTIONAL: APCu, Memcached
* or HHVM 3.3.2+ LTS or HHVM 3.4.1+
* MySQL 5.6+
* or MariaDB 10.0.5+
* or PostgreSQL 9.5+
* or SQLite 3.6.4+

# How to try?

It is possible to try latest git version of CleverStyle CMS without even installing it using [Docker](https://www.docker.com/), just type in terminal:
```bash
$ docker run --rm -p 8888:8888 nazarpc/cleverstyle-cms
```
And go to `http://localhost:8888`, sign in using login `admin` and password `1111`.

`--rm` means that container will be removed after stopping (you can stop it with `Ctrl+C`).

If you want to play with live system - attach volume to container:
```bash
$ docker run --rm -p 8888:8888 -v /some_dir:/web nazarpc/cleverstyle-cms
```
Now in `/some_dir` you'll have source code of CleverStyle CMS from container which you can edit as you like and it will reflect on demo.

# How to install?

[Read simple instructions in our documentation](/docs/Installation.md)

# [Video tutorials for developers](https://www.youtube.com/watch?v=GVXHeCVbO_c&list=PLVUA3QJ02XIiKEzpD4dxoCENgzzJyNEnH)
