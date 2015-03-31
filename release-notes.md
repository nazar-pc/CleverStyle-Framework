# 1.0.0+build-749: First release 1.0.0

First release after more than 4 years of development.
[Installation instructions](https://github.com/nazar-pc/CleverStyle-CMS/wiki/Installation)
Feel free to report bugs if any, feature requests and questions, they are highly appreciated!
![logo mono](https://cloud.githubusercontent.com/assets/928965/5197643/de7aa1aa-7545-11e4-89a1-696b9ddfdba6.jpg)

# 1.1.3+build-757: New stable release 1.1.3

Better testing environment.
Improved and extended OAuth2 module:
* now supports `Authentication: Bearer {access_token}` from RFC 6750
* breaking change in OAuth2 module - `/OAuth2/token*` requests should be made with `POST` not `GET` method to follow RFC 6749
* improved documentation

Bunch of system constants removed.
Of course, some bug fixes to improve stability.
New builds are not attached anymore to releases:
* you can always find latest builds on [downloads page](https://github.com/nazar-pc/CleverStyle-CMS/wiki/Download-installation-packages)
* or download source code and [build it yourself](https://github.com/nazar-pc/CleverStyle-CMS/wiki/Installer-builder)

# 1.5.0+build-764: New stable release 1.5.0

Added 2 new triggers:
* `System/Config/before_init`
* `System/Config/after_init`

[wiki](https://github.com/nazar-pc/CleverStyle-CMS/wiki/%24Config#systemconfigbefore_init)

`cs\CRUD` trait can handle multilingual functionality automatically: [wiki](https://github.com/nazar-pc/CleverStyle-CMS/wiki/CRUD#ml-prefix-and-this-data_model_ml_group)

`cs\DB\_Abstract::insert()` now can accept single-dimensional array of parameters if needed so.

API requests fix (for certain cases).

Fixes in Blogs and Photo gallery modules.

Latest builds on [downloads page](https://github.com/nazar-pc/CleverStyle-CMS/wiki/Download-installation-packages) or download source code and [build it yourself](https://github.com/nazar-pc/CleverStyle-CMS/wiki/Installer-builder)

# 1.8.3+build-770: New stable release 1.8.3

Main changes:
* New upstream version of Sortable jQuery plugin
* New upstream version of UIkit
* Fix for `api/System/profiles/{id}` not working for single user id.
* Simplified UIkit's tab integration, fade animation added.

Fixes and small improvements in components:
* Comments
* Plupload
* TinyMCE

There is `shop` branch with WIP version of Shop module, will be merged into master as soon as will be ready (currently administration side is almost ready).

Latest builds on [downloads page](https://github.com/nazar-pc/CleverStyle-CMS/wiki/Download-installation-packages) or download source code and [build it yourself](https://github.com/nazar-pc/CleverStyle-CMS/wiki/Installer-builder)

# 1.16.0+build-790: The most stable, extensible and Web Components-ready version ever

New features:
* `wait` cursor while web components are cooking
* System styles are ShadowDOM-ready
* [Extremely flexible patching of system classes](https://github.com/nazar-pc/CleverStyle-CMS/wiki/System-classes-extension)
* Possibility to extend Polymer element with the same name like `<polymer-element name="some-element" extends="some-element">...`
* Service script to convert any CSS to ShadowDOM-ready (for instance, used for UIkit), `service_scripts/make_css_shadow_dom_ready.php` file

Updates:
* New upstream version of WebComponents.js
* New upstream version of Polymer
* New upstream version of BananaHTML
* New upstream version of UIkit
* New upstream version of Fotorama
* New upstream version of jQuery 3.0 (Development version)

Fixes and small improvements in components:
* Fixed multilingual functionality
* Better navigation with `tab` key in sign in block
* Better errors handling during files uploading in Plupload module
* Other small fixes

Important patches of upstream third-party libraries:
* jQuery patched to fix `$.fn.offset()` on elements inside ShadowsDOM ([pull request](https://github.com/jquery/jquery/pull/1976))
* WebComponents.js patched to handle properly relative path resolving in css ([pull request](https://github.com/webcomponents/webcomponentsjs/pull/135))
* Polymer patched to allow `<polymer-element name="some-element" extends="some-element">...` ([discussion](https://github.com/Polymer/polymer/issues/1032)) ([experimental git branch with patch applied](https://github.com/nazar-pc/polymer/tree/same-name-extending))
* UIkit styles converted to ShadowDOM-ready (using service script)
* Fotorama style also wrapped to work inside ShadowDOM

There is `shop` branch with WIP version of Shop module, will be merged into master as soon as will be ready (most of major features ready, need some supplementary features and triggers to be more hackable).

Latest builds on [downloads page](https://github.com/nazar-pc/CleverStyle-CMS/wiki/Download-installation-packages) or download source code and [build it yourself](https://github.com/nazar-pc/CleverStyle-CMS/wiki/Installer-builder)

# 1.22.2+build-811: More features and new Prism plugin

New features:
* Inverse dependency through `provide` property in `meta.json` like `Blog/post_patch` (crucial for same name extending of Web Components).
* Added controller-based routing support in addition to files-based ([documentation](https://github.com/nazar-pc/CleverStyle-CMS/wiki/Routing))
* Atom feed in Blogs module (for all posts, specific category, specific tag)
* New Prism plugin for source code highlighting on pages

Fixes and small improvements:
* Fix for `TypeError: (intermediate value).parentNode is null` in Polymer
* Fix for `body[unresolved]` was not actually working
* Removed hack for older versions HHVM that fixed installation/upgrade, now fix available upstream in HHVM itself
* Fix for content items list displaying when there are no items yet in Content module
* Small fix to improve customizations possibilities of user block
* Fix for Firefox freezing on modal opening
* Better code formatting (not in single line as before)
* Fixes and improvements in DarkEnergy theme
* Better control over drafts access in Blogs module
* Drop part of URL after `?` to comply with standards, otherwise it created unnecessary difficulties in many cases
* Builder fix: didn't include some files from `core` directory
* React on `X-Facebook-Locale` header and switch language regardless from URL

There is `shop` branch with WIP version of Shop module, will be merged into master as soon as will be ready (very likely to be included in next release).

Latest builds on [downloads page](https://github.com/nazar-pc/CleverStyle-CMS/wiki/Download-installation-packages) or download source code and [build it yourself](https://github.com/nazar-pc/CleverStyle-CMS/wiki/Installer-builder)

# 1.40.2+build-853: Huge leap forward to the future, WebSockets and Shop modules added

New components:
* New **WebSockets** module makes WebSockets usage with CleverStyle CMS ridiculously easy
* New **Shop** module, provides simple, but highly extensible and customizable shopping functionality

New features:
* `$_SERVER` superglobal is now [wrapped by object](https://github.com/nazar-pc/CleverStyle-CMS/wiki/$_SERVER) to provide simplified, more functional and secure alternative to raw elements (while keeping original array-like behavior for compatibility)
* `\cs\Language::init()` and `::url_language()` methods added
* `\cs\User::get_session()` refactored to `::get_session_id()` which much better explains what function actually do
* Possibility to attach volume to Docker container with demo
* Triggers are now Events, corresponding class `Event` [added](https://github.com/nazar-pc/CleverStyle-CMS/wiki/$Event), `Trigger` still exists for backward compatibility, but uses `Event` under the hood (transition is simple - `Trigger::register() -> Event::on()`, `Trigger::run() -> Event::fire()`, also `Event` have some new functionality with methods `::off()` and `::once()`, `events.php` is used now instead `trigger.php` which is deprecated now
* `cs.Event` object added on frontend similar to `cs\Event` on backend with the same methods

Updates:
* UIkit updated to latest upstream version + all components included since this version!
* New upstream version of Polymer and WebComponents.js
* New upstream version of jQuery (still from master branch)

Fixes and small improvements:
* Style fixes in DarkEnergy theme
* Do not pass user session to third-party services in HybridAuth, use md5 from provider and session instead
* Fix for warning during docker image building
* Make clickable license, readme and API icons look like buttons
* Better modals handling in module and plugin admin pages (and no colored text anymore)
* Do not use `TIME` constant for sessions, because it now may be used for long-living cases and constant will not reflect real current time
* Events `System/User/del_session/before` and `System/User/del_session/after` now passes session id to callback

Deprecations:
* `User::$user_agent`, `::$ip`, `::$forwarded_for` and `::$client` will trigger `E_USER_DEPRECATED`, `$_SERVER` should be used instead
* `\cs\User\get_session()` still exists and backward-compatible with old format (throws deprecated warning in log), but now is used to get all session details
* `trigger.php` is deprecated in favor of `events.php` with the same functionality

Possible partial compatibility breaking (very unlikely, but still possible):
* Reverse signature of `shutdown_function()` function
* `\cs\Config::update_clangs()` method removed
* `\cs\Language::reload_core_config()` method removed
* Encryption improvement, but will not be able to decode old encrypted data (not likely to have big impact):
  * Initial vector is random and returned with encrypted data
  * Blowfish changed in favor of Twofish
  * Good random key generated on installation with the help of `openssl_random_pseudo_bytes()`
* `\cs\User::get_session_user()` refactored to `::load_session()` which much better explains what function actually do

Latest builds on [downloads page](https://github.com/nazar-pc/CleverStyle-CMS/wiki/Download-installation-packages) or download source code and [build it yourself](https://github.com/nazar-pc/CleverStyle-CMS/wiki/Installer-builder)

# 1.54.0+build-880: What if we just make everything 5 times faster?

New components:
* New **Http server** module allows to run Http server written in PHP, thus everything lives much longer and works much faster!

New features:
* Static pages: Live editing of Static pages
* `DB::queries()` and `::time()` methods added (look at deprecations)
* Support for not only `application/json`, but also other `application/something+json` request content types
* New function `_header()` introduced to be used instead of `header()`, arguments are the same
* New `\ExitException` instead of just `exit` or `die` when there is no real need to stop whole process

Updates:
* UIkit updated to latest upstream version
* New upstream version of Polymer and WebComponents.js
* New upstream version of jQuery (still from master branch)
* New upstream version of UPF

Fixes and small improvements:
* Shop: Fix for characteristics (not shown because of some bug with fotorama and untranslated string)
* WebSockets: Tiny fix for simulating internal WebSockets event `register_actions` from client side
* Removing `global` used during installation process
* `cs\Singleton` is now based on `cs\Singleton\Base` in order to ease hackability and reduce code duplication
* Basic loader part that can be used for custom loaders during tests and in http server moved into separate file
* Fix for favicon path when there is icon in theme directory
* Singleton performance fix
* Remove unnecessary `_once` suffix for files including
* Fix potential repeated execution of the whole system (resolve duplicated functions declaration)
* Fix for session deletion with WebSockets enabled (missing session id during event firing)
* Functions that work with global state moved into separate file and are not included by base loader
* Fix for setting cookie to empty string didn't remove it from `$_COOKIE` superglobal
* Be ready for `$_GET`, `$_POST` and `$_REQUEST` being array-like objects instead of arrays
* WebSockets: Rename `prepare_cli.php` in WebSockets module to more logical name `start_cli.php`
* Event class simplification
* Improvement of server type detection
* `__invoke()` added to `\cs\False_class`
* Show information about module even if it is not installed 

Deprecations:
* `DB::instance()->queries` and `->time` properties

Possible partial compatibility breaking (very unlikely, but still possible):
* Update support for Blogs module from very old versions removed (not likely to be an issue)

Latest builds on [downloads page](https://github.com/nazar-pc/CleverStyle-CMS/wiki/Download-installation-packages) or download source code and [build it yourself](https://github.com/nazar-pc/CleverStyle-CMS/wiki/Installer-builder)

# 1.58.0+build-887: We love Composer!

New **Composer** module makes installing composer packages as easy as mentioning them in `meta.json` file, no need to use command line, no need to even have access to command line on shared hosting, etc.

New components:
* New **Composer** module - [Composer](https://github.com/composer/composer) integration into CleverStyle CMS, allows to specify composer dependencies in meta.json that will be installed automatically

New features:
* New events:
  * admin/System/components/modules/update/prepare
  * admin/System/components/modules/update_system/prepare
  * admin/System/components/modules/enable/prepare
  * admin/System/components/modules/disable/prepare
  * admin/System/components/modules/update/process/before
  * admin/System/components/modules/update/process/after
  * admin/System/components/modules/update_system/process/before
  * admin/System/components/modules/update_system/process/after
  * admin/System/components/modules/enable/process
  * admin/System/components/modules/disable/process
  * admin/System/components/plugins/update/prepare
  * admin/System/components/plugins/enable/prepare
  * admin/System/components/plugins/disable/prepare
  * admin/System/components/plugins/enable/process
  * admin/System/components/plugins/disable/process
  * admin/System/components/plugins/update/process/before
  * admin/System/components/plugins/update/process/before 
* Http server and WebSockets modules now depends on Composer module and does not include dependencies inside!

Updates:
* None

Fixes and small improvements:
* Http server: Fix for classes cache cleaning in Http server
* DB reconnection in long living process when DB server was disconnected
* Support simple modal width in any units, not only px
* `trigger.php` renamed into `events.php` everywhere
* Http server: Mentioning that in Http sever mode files uploading is not currently supported
* Http server: Set remote_addr in `$_SERVER` under Http server
* Do not change working directory when minifying css/js/html
* Removed synchronization of minified files between mirrors
* Do not change working directory in Local storage engine
* Photo gallery: Small fix in Photo gallery when all images failed to upload
* Constants renamed:
  * STORAGE -> PUBLIC_STORAGE
  * PCACHE -> PUBLIC_CACHE
* Constants added:
  * STORAGE (now points to /storage)

Deprecations:
* Deprecated events (use newer instead):
  * admin/System/components/modules/enable
  * admin/System/components/modules/disable
  * admin/System/components/plugins/enable
  * admin/System/components/plugins/disable

Possible partial compatibility breaking (very unlikely, but still possible):
* PCACHE and STORAGE constants renamed to new names, STORAGE constant now points to another directory (not likely to cause any problems)

Latest builds on [downloads page](https://github.com/nazar-pc/CleverStyle-CMS/wiki/Download-installation-packages) or download source code and [build it yourself](https://github.com/nazar-pc/CleverStyle-CMS/wiki/Installer-builder)

# 1.88.4+build-938: Better consistency in routing and improved security

Major change in this release happening to routing. `\cs\Route` class introduced and now covers all routing functionality instead of having part of it in `\cs\Config` and `\cs\Index` classes. Though, complete backward compatibility is present and will be kept until 2.0.

This is the last or pre-last release before 2.0, be sure to keep your components up to date for easy and smooth upgrade to next major release!

New components:
* None

New features:
* Planned transition to Controller-based routing in System module
* Big amount of code duplication removed using new generic methods for packages installation, updating and removal
* New events:
  * System/Route/pre_routing_replace
  * System/Route/routing_replace

Updates:
* New upstream version of TinyMCE:
  * new plugin `colorpicker` now included
  * table styling since now will be done with css rather than with attributes
* New upstream version of UPF (also with security improvements)

Fixes and small improvements:
* GZ page compression removed on system level, it should be rather done on higher level (WebServer or proxy) with better performance
* Protected constructor in `\cs\User` as in `\cs\Singleton` trait
* Improved security in case of allowing any user-submitted iframe elements
* Added support for removing nested empty directories during update process (only single directory was removed before)
* Database and storage testing modal is now rendered purely on frontend
* Refactoring in `cs\Config` class, protection was really dubious
* `cs\Config::instance()->can_be_admin` property refactored to method with the same name, backward compatibility still present
* `$_SERVER->protocol` added which is more useful in many cases instead of `->secure` property
* Fix for `cs\Page::config()` with scalar values didn't work as expected on frontend
* Installation config is now in form of regular array instead of parsing JSON string O_o

Deprecations:
* `\cs\Core::api_request()` method
* `\cs\User::system()` method
* `\cs\Config::$can_be_admin` property (use method with the same name instead
* `\cs\Config::$server` property
* `\cs\Config::$route` property (use `\cs\Route::$route` instead)
* `\cs\Config::process_route()` method (use `\cs\Route::process_route()` instead)
* `\cs\Index::$route_path` property
* `\cs\Index::$route_ids` property
* Deprecated events (use newer instead):
  * System/Config/pre_routing_replace
  * System/Config/routing_replace

Possible partial compatibility breaking (very unlikely, but still possible):
* None

Latest builds on [downloads page](https://github.com/nazar-pc/CleverStyle-CMS/wiki/Download-installation-packages) or download source code and [build it yourself](https://github.com/nazar-pc/CleverStyle-CMS/wiki/Installer-builder)

# 1.110.0+build-985: Bitcoin payment, automated builds and lots of polishing all over the place

Major change in this release happening to session management. `\cs\Session` class introduced and now covers all session management functionality instead of having everything in still large `\cs\User` class. Though, complete backward compatibility is present and will be kept until 2.0.

Also this release brings new module Blockchain payment, this is actually first payment module, it integrates nicely with Shop module and any other if needed, and allows to accept payments in Bitcoin!

This is not all yet, now all builds are automatic! This means that all stable builds will be kept as long, as you need them, also bleeding edge nightly builds are prepared after each commit just for you!

One more thing here - if you are using PHP 5.4 - it is encouraged to upgrade to 5.5 or even better 5.6, because 5.4 support will be dropped in 2.0 release together with all deprecated functionality.

Look at [SourceForge downloads page](https://sourceforge.net/projects/cleverstyle-cms/files/).

This is the last release before 2.0, be sure to keep your components up to date for easy and smooth upgrade to next major release!
Update to 2.0 will be available only from 1.110.0+, update older releases to 1.110 before moving forward!

New components:
* New **Blockchain payment** module, pay for anything in Bitcoin

New features:
* Shop: Notion of currency added to Shop module
* New events:
  * System/Config/init/before
  * System/Config/init/after
  * System/Session/init/before
  * System/Session/init/after
  * System/Session/del/before
  * System/Session/del/after
  * System/Session/del_all
* Now it is possible to use `.cs-table-*` classes in addition to custom elements with the same name
* Since now after each commit new builds will be published on SourceForge

Updates:
* New upstream version of BananaHTML
* New upstream version of WebComponents.js, thankfully, no patches needed this time
* New upstream version of UIkit
* New upstream version of UPF:
  * Do not allow custom elements (with dash in name or regular elements with `is` property)

Fixes and small improvements:
* Throwing deprecated error on PHP 5.4 with recommendation to update to 5.5+
* Run Travis CI tests on PHP 7, nightly builds currently, to be ready when it will be released officially as stable
* Shop: Some events now passes `currency` together with other arguments in order to provide better context
* Fix for `cs.config` when passing array there
* Shop: Do not show "Pay now" for cash payment method
* Shop: Additional parameter in payment confirmation event - callback, to avoid redirects when it is not desirable
* Shop: Repeated payment confirmation will have no effect, so can be freely executed as many times as needed
* Shop: Fix for "Pay later" button didn't work
* Shop: Fix for bug when after successful payment order status didn't change (paid property worked fine)
* Shop: Fix for wrong recalculation of available units
* Big changes again: `\cs\Session` class introduced, all session-related work moved from `\cs\User` to new class
* User-specific settings processing moved to event handler instead of doing it in session object
* System core and components switched to using `\cs\Session` for session-related things and it's events
* Http server: Http server updated according to new structure of System core
* `release-notes.md` added in oder to avoid relying purely on GitHub releases
* Http server: Disable memory cache for `\cs\User` class under http server
* Hugely refactored, simplified and improved dependencies check
* Now dependency check will account conflicts in both sides, not only from side of package that is going to be installed
* `package` item in some modules updated to reflect real name of package directory
* Fix for inclusion multiple inline Web Components
* Inline scripts inclusion placed near file includes (placement depends on configuration)
* Support of new `meta.json` option for hiding module in main menu
* Do not use `/dir` file in module/plugin/theme distributive package, use `package` from `meta.json` file instead, but still keep file until 2.0 for backward compatibility
* Force Travis CI to use container-based infrastructure
* Build scripts moved info single class
* DarkEnergy theme doesn't have hardcoded copyright anymore, `<!--bottom_blocks-->` might be used to specify it instead, also `<!--top_blocks-->` might be used to customize header
* Do not use `/version` file in system core package anymore, keep for backward compatibility till 2.0
* `meta.json` added to system core package root
* Significantly faster build creation
* Allowed building multiple modules, plugins and themes at once in corresponding mode
* Fix for admin page not opening because of missing `\cs\Route` class import
* Fixed forms for permissions addition and editing
* Http server: Move some code in Http_server module into namespace
* Actually no need for `exit` in Core class
* Get rid of `exit`, we can actually replace them with `echo` + `return` statements
* Files permissions updated
* SensioLabInsight badge added, existing renamed and switched to SVG
* Fix for incorrect detection of other components that provides the same functionality
* Some tweaks suggested by SensioLabsInsight
* `\cs\Language` instance caching removed from `__()` function
* User-specific directories removed from .gitignore
* Multiple unused variables removed
* Multiple unused `use` statements removed
* Photo gallery: Fix for potential bug with images deletion in Photo gallery module
* `\cs\Mail` class refactoring
* Avoid using `goto` in `\cs\Session`
* Refactoring of `\cs\DB\MySQLi`
* LOTS of smaller fixes all over the place

Deprecations:
* Deprecated events (use newer instead):
  * System/Config/before_init
  * System/Config/after_init
  * System/User/del_session/before
  * System/User/del_session/after
  * System/User/del_all_sessions

Possible partial compatibility breaking (very unlikely, but still possible):
* Set of functions removed from global namespace because were used in one or two places only, and there was no need to add them into global namespace.
  Removed functions:
  * check_mcrypt
  * curl
  * apc
  * memcached

Latest builds on [SourceForge downloads page](https://github.com/nazar-pc/CleverStyle-CMS/wiki/Download-installation-packages) ([details about installation process](https://github.com/nazar-pc/CleverStyle-CMS/wiki/Installation)) or download source code and [build it yourself](https://github.com/nazar-pc/CleverStyle-CMS/wiki/Installer-builder)
