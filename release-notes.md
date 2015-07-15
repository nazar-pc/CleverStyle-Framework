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

# 2.4.2+build-1005: Code quality and bugs hunting

This release is not so huge as previous, but extremely important, because it drops backward compatibility, even, partially, with last release, read on.

Bad things first:
* if you're running any old version - you need to update system to `1.110.0+build-985` first, then update all components (otherwise they will not be able to uninstall completely) and ensure you've read all release notes about releases newer that you currently use, modified your code accordingly and there are no errors and deprecated messages in log file
* also right after upgrade to `2.4.2+build-1005` update all components

Now exciting good news:
* no redundant elements for backward compatibility!
* better performance!
* more static code analysis with SensioLabsInsights and Scrutinizer!
* PHP 5.5 is the minimum supported version now and we can use new features inside system core!

New components:
* None

New features:
* `cs\CRUD_helpers` trait added to reduce code duplicating for trivial additional operations when using `cs\CRUD` trait (currently includes `search` method)
* Scrutinizer badge added
* Block templates now have access to `$block` variable, so they can render block as they like

Updates:
* New upstream version of UPF, dropped PHP 5.4 support

Fixes and small improvements:
* OAuth2: Fix for refresh_token in OAuth2 module
* Deferred tasks: Split `\cs\modules\Deferred_tasks::run` method into two, because they both do not share any common code anyway
* OAuth2: Unnecessary return statement removed, fixed potential error in Http server mode because of function redeclaration
* Some refactoring of DB-related classes and usages
* Fix for removing empty gallery
* Simplification in `\cs\Page` class
* Tiny fixes in `\cs\Config\Module_Properties`
* Refactoring and fixes in `\cs\Session`, do not update `sign_in` time unless really sign in
* Reformatting and simplification of `\cs\Permissions`
* Refactoring and simplification of `\cs\User\Permission` trait
* Dropped deprecated internal method in `\cs\Core` class
* Huge PhpDoc update that will allow more precise static analysis:
  * `bool` in multiple return types of PhpDoc sections in many cases replaced by `false` as more specific type
  * some PhpDoc types corrected
* Refactoring and simplification of `\cs\Index` class
* Fixes for warnings on admin pages
* Generally big amount of PhpDoc improvements and small fixes

Deprecations:
* Well, this is backward incompatible version:)

Dropped backward compatibility:
* All deprecated functionality was dropped
* All components now require System of version >= 2.0, but < 3.0 since API will be backward-compatible for whole 2.x series
* Dropped update support from System versions older than previous release, the same for all components
* Dropped support for PHP 5.4
* Meta information unification (sorry, no transitional version this time):
  * `meta/db.json` contents of components now available as `db` field in `meta.json`
  * `meta/storage.json` contents of components now available as `storage` field in `meta.json`
  * `versions.json` contents of components now available as `update_versions` field in `meta.json`

Latest builds on [SourceForge downloads page](https://github.com/nazar-pc/CleverStyle-CMS/wiki/Download-installation-packages) ([details about installation process](https://github.com/nazar-pc/CleverStyle-CMS/wiki/Installation)) or download source code and [build it yourself](https://github.com/nazar-pc/CleverStyle-CMS/wiki/Installer-builder)

# 2.28.0+build-1076: Semantics matters

Significant amount of changes comparing to few previous releases.

Starting from this release and onwards we'll move components towards using JSON-LD and Web Components. First module that works in such way on user side is Blogs.

Also this release includes important fixes for some regressions that made it unable to install module that uses DB in simple mode because of incorrect dependencies resolution.

As usual many small fixes and improvements, especially exiting improvements happened in `\cs\CRUD` trait that now supports JSON type, html with iframes and automatically handles files uploads + introduces new simplified interface.

And the last important thing here: all new builds and git tags are [digitally signed](https://github.com/nazar-pc/CleverStyle-CMS/wiki/Download-installation-packages#digital-signature) now!

New components:
* New **Json_ld** plugin - simplifies some parts of JSON-LD generation
* New **Tags** plugin - currently contains single trait, is used by other components in order to avoid code duplication

New features:
* `\cs\CRUD::*()` methods now work like `\cs\CRUD::*_simple()` which are now deprecated
* New upstream version of BananaHTML:
  * If boolean value specified for some attribute - `false` means no attribute and `true` means attribute without value (or with value as name in case of XHTML tags style)
  * `$known_unit_atributes` had typo, refactored to `$known_unit_attributes`
  * Some small types corrections
* Blogs module now uses JSON-LD and Web Components to render semantic and highly customizable pages

Updates:
* New upstream version of UPF, `JSON_UNESCAPED_SLASHES` added to `_json_encode`
* New build of patched Polymer:
  * Applied patch for same name extending to latest upstream master
  * Fixed inheritance if Chromium
* New upstream version of UIkit

Fixes and small improvements:
* Rewrite of `\cs\DB` class
* `maindb_for_write` system option replaced with `db_mirror_mode` that allows too choose from Master-Master or Master-Slave configuration
* `\cs\False_class` updated to support more scenarios and reformatted
* Small fix and reformatting of `\cs\Mock_object`
* Restored buttons order in `\cs\Index` class
* Dropped micro-optimization in `\cs\Cache`, reformatted
* Do not show empty forms for db and storage selection during module installation
* Use simple admin mode by default
* Better performance of session creation
* Support for additional classes in `h::radio()` and `h::checkbox()`
* `\cs\Index::$apply_button` now defaults to `false`, as this value is used MUCH more often, not likely to cause backward incompatibility, but potentially can hide `apply` button somewhere
* Simplification because of new default value of `\cs\Index::$apply_button`
* Shop: Fixes for some header comments in Shop module's files
* Use `script[type=application/json]` instead of `template` for user-side configs, also eliminates need for some pre- and post-processing
* Fix for creation and deletion of items with not-numeric primary key in `\cs\CRUD` trait
* PhpDoc improvements in `\cs\CRUD` trait
* Fix for `\cs\User\Properties::avatar()` infinite loop
* Blogs: New format of `tags` item of returned post data in `Blogs` module
* Blogs: `\cs\modules\Blogs::get_as_json_ld()` method added
* Blogs: Post page now uses JSON-LD and Web Components instead of server-side HTML markup
* Blogs: More technical data returned with JSON-LD structure
* Transition from using deprecated methods of `\cs\CRUD`
* PhpDoc fixes, small tweaks and reformatting of `\cs\User\Data`
* Return strictly boolean in `\cs\User\Data::set_data()` and `::del_data()`
* More polishing with improved PhpDoc and reformatting for `\cs\User\*` traits
* Reformatting of `\cs\Group`
* `\cs\DB\_Abstract` trait refactored.
* Simplifications and PhpDoc improvements in `\cs\Language`
* PhpDoc fix and reformatting of `\cs\Language\Prefix`
* Sacrifice a bit of performance (loop will not be too big to cause any measurable performance issues)  to make `\cs\DB\MySQLi::f()` code smaller and simpler
* Huge refactoring of `\cs\Language::change()`, should be simpler to understand and easier to modify now
* Fix for showing/hiding smtp settings in administration
* Refactoring of `\cs\Mail::send_to()`
* Improved signature formatting in HTML emails
* Fixed attachments addition to emails sent
* Huge refactoring of `\cs\Session` class, should be simpler and a bit faster now
* Common code decoupled and simplified in `\cs\\h\Base::checkbox()` and `::radio()`
* Reformatting of `\cs\Base`
* Splitting `\cs\Core::constructor()` into 2 methods, reformatting
* Splitting `\cs\DB::connecting()` into 2 methods
* Fix for blocks were not rendered if previous block was rendered by event handler
* Decision whether to render block decoupled into separate method
* Splitting `\cs\Page\Meta::render()` into 2 methods
* `\cs\Route::process_route()` refactoring
* Fix in Builder that produced incorrect `fs.json` files for packages when building system core package with built-in components
* Fix for displaying System version during installation process
* Fix for database check during dependencies resolution at module installation
* Content: Fix for untranslated save button in Content module
* HybridAuth: Fix for HybridAuth catches `\ExitException` and unable to authenticate
* Some low-level error messages now untranslated in order to be properly shown in log file
* Old unused translations removed
* `\cs\CRUD::$data_model_files_tag_prefix` added, allows extremely simple solution to maintain tags for uploaded files automatically
* Added support for `html_iframe` in `\cs\CRUD` as possible type for data model
* Static pages: `\cs\modules\Static_pages\Static_pages` splitted into `\cs\modules\Static_pages\Pages` and `\cs\modules\Static_pages\Categories`, both now uses `\cs\CRUD` which simplified code a lot
* Static pages: Files uploaded through editor now are tagged by Static pages module
* Refactoring of `\cs\Page\Includes`
* `\cs\modules\Blogs\Blogs` splitted into `\cs\modules\Blogs\Posts`, `\cs\modules\Blogs\Sections` and `\cs\modules\Blogs\Tags`
* `functionality()` function now account components names as functionalities as well (for convenience)
* `pages()` function now sets canonical URL automatically, also it was reformatted and a bit simplified
* Blogs: Section page of Blogs module now renders posts as Web Components using JSON-LD
* Blogs: Section, tag, latest posts and drafts pages with posts list are now all rendered with JSON-LD and Web Components
* Blogs: Helper class added instead of plain functions
* Simplification in `\cs\DB\_Abstract`
* Fix for files tags were not changed during update in `\cs\CRUD` when multilingual interface is used
* Blogs: `\cs\modules\Blogs\Posts` uses `\cs\CRUD`
* Use `INSERT IGNORE` in `\cs\CRUD` which is helpful in some cases
* Tags plugin added - contains trait that will be used by other modules
* Blogs: Blogs module now uses Tags plugin for tags management
* Better PhpDoc types in `\cs\DB\_Abstract`
* Shop: Shop module now also uses Tags plugin for tags management
* Make more `\cs\CRUD` methods protected instead of private to allow their usage from outside
* JSON data type support added to `\cs\CRUD`
* Improved URLs detection in `\cs\CRUD`, now fields that contain URLs by themselves are supported
* Shop: Shop module now uses some new features of `\cs\CRUD` trait
* Shop: Few classes in Shop module had a lot of common functionality, now it is concentrated in single trait
* Shop: `\cs\CRUD` use moved to `cs\modules\Shop\Common_actions` in Shop module
* New upstream version of UIkit
* Fix for Blockchain_payment module package name in `meta.json`

Deprecations:
* `\cs\CRUD::*_simple()` methods now deprecated, `\cs\CRUD::*()` methods should be used instead with the same syntax, full backward compatibility is present

Possible partial compatibility breaking (very unlikely, but still possible):
* Dropped support for 2-level arrays in `\cs\DB\_Abstract::q()` (very unlikely someone used this construction since it was not documented and looks too complex)

Latest builds on [SourceForge downloads page](https://github.com/nazar-pc/CleverStyle-CMS/wiki/Download-installation-packages) ([details about installation process](https://github.com/nazar-pc/CleverStyle-CMS/wiki/Installation)) or download source code and [build it yourself](https://github.com/nazar-pc/CleverStyle-CMS/wiki/Installer-builder)

# 2.43.0+build-1113: Write less, do more

The main feature of this release is further extension of already functional `\cs\CRUD` trait. Now in addition to simple CRUD operations, files tags handling (was improved also), multilingual strings handling even more advanced feature was added: joined tables.
Joined tables allow to make CRUD operations on tables that are tightly connected to main table, such as:
* post tags
* shop item attributes
* shop item images

Now you can manage things of such kind automatically together with fields from main table just by specifying all such tables in common data model, read [updated documentation](https://github.com/nazar-pc/CleverStyle-CMS/wiki/CRUD) for more details of implementation.
Obviously, `\cs\CRUD_helpers::search()` supports joined tables as well, allowing creation of most common queries even including joined tables very easily and naturally.

Second important thing is basically rewrite of HybridAuth integration, now code is much simpler, better structured and reliable. Also some small fixes and new features were introduced.

There are also various other fixes and fresh upstream versions of third-party components, enjoy!

New components:
* Picturefill plugin added

New features:
* Support for nested arrays in `\cs\CRUD::find_urls()` method
* `\cs\CRUD` can now handle even more work with added joined tables support
* Extended search options (intervals and multiple options) in `\cs\CRUD_helpers` trait
* `search` method in `\cs\CRUD_helpers` trait now supports joined tables
* `search` method in `\cs\CRUD_helpers` trait now supports joined tables in `$order_by` using `:`

Updates:
* New upstream version of UIkit
* New upstream version of WebComponents.js
* New upstream version of PHPMailer
* New upstream version of jsSHA
* New upstream version of html5sortable
* New upstream version of fotorama
* New upstream build (no exact version) of Prism
* New upstream build of Composer
* New upstream version of TinyMCE
* New upstream version of Plupload

Fixes and small improvements:
* FileSystem cache doesn't support size limitation anymore - it caused huge performance hit and made code much more complex
* Also corresponding cache-related low-level error messages now untranslated in order to be properly shown in log file
* Blogs: Drop posts editing from administration, it just duplicates user-side interface where administrator already have access
* Code for rendering permissions form decoupled into separate method
* HybridAuth: `prepare.php` renamed into `Controller.php` into `::index()` method, no code logic changes
* HybridAuth: `\cs\modules\HybridAuth\Social_integration` class added, DB queries unified and moved there from controller
* HybridAuth: Session setting and contacts updating code decoupled into separate method because it was duplicated many times
* HybridAuth: Data updating decoupled into separate method because it is even more common
* HybridAuth: Some refactoring and rearrangement - check for user existence first before registration
* HybridAuth: Email sending code decoupled into separate method
* HybridAuth: Splitted long method into few separate, `goto` replaced by separate method, code logic shouldn't be affected
* HybridAuth: Redirect code decoupled into separate method, some comments reformatting
* HybridAuth: More tweaks, less arguments in methods
* HybridAuth: Simplification and generalization in merge confirmation
* HybridAuth: More simplification regarding adapter handling, profile information and contacts update
* HybridAuth: Simplifications, less plain functions
* Do not use `$_POST['login']` directly in `\cs\User` anymore, not likely to break anything, but still possible
* HybridAuth: More simplification - no need to store HybridAuth data in user's session
* HybridAuth: More common code decoupled
* HybridAuth: Allow to sign in, but not register or merge account if registration is disabled on system level
* Simplification in `\cs\CRUD`
* Some code rearrangement in `\cs\CRUD`
* Data model processing part of `\cs\CRUD` trait decoupled into separate trait
* Support for language fallback in `\cs\CRUD` when using joined tables
* Shop: `\cs\modules\Shop\Items` now uses latest features of `\cs\CRUD` to reduce boilerplate code and total code complexity
* Fix files tags setting for complex data models
* Make markup a bit nicer)
* Fix for `Commands out of sync; you can't run this command now` error which happened in Travis CI tests
* `search_do()` method decoupled from `search()` method in `\cs\CRUD_helpers` trait
* Shop: New `search_do()` method is now used by `\cs\modules\Shop\Items`
* Simplifications in Polls module using some new CRUD features
* Small other fixes
* Docker shield added
* Translations updates
* Fix for building and uploading packages on non-master branch
* Fix for `\cs\CRUD_helpers` methods sometimes didn't honour `$table_alias`
* Shop: Items search fix in Shop module

Deprecations:
* None

Possible partial compatibility breaking (very unlikely, but still possible):
* `$_POST['login']` is not used directly in `\cs\User` anymore, not likely to break anything, but still possible

Latest builds on [SourceForge downloads page](https://github.com/nazar-pc/CleverStyle-CMS/wiki/Download-installation-packages) ([details about installation process](https://github.com/nazar-pc/CleverStyle-CMS/wiki/Installation)) or download source code and [build it yourself](https://github.com/nazar-pc/CleverStyle-CMS/wiki/Installer-builder)

# 2.86.0+build-1210: Bower and NPM support, Emoji, powerful WebSockets

One cool thing in this release is transition from `utf8` to `uf8mb4`, which is correct version of UTF8 and now you can use whole range of symbols, including emoji without any problems.

Another thing is reducing amount of backend code that deals with UI, instead few WebComponents were created for administration UI alongside with new API endpoints, more work in this direction will be done in future releases.

Major advancements were done in WebSockets module bringing incredible flexibility, simple server start and reliable support for multi-servers setup.

Last but not least major feature of this release is new **Composer assets**, which brings extremely simple support for Bower and NPM packages that can now be specified as components dependencies for frontend alongside with Composer dependencies for backend.
Of course, automatic minification, compression, vulcanization and dependencies management works for Bower and NPM assets flawlessly!
LESS and SCSS styles are also supported, since Bower encourages to specify non-minified versions in package manifest.

New components:
* **Composer assets** plugin added for Bower and NPM packages support in CleverStyle CMS

New features:
* `utf8mb4` as DB encoding to support emoji and other symbols
* Modules list is not rendered on server anymore, everything moved to client
* Plugins list now also rendered on client side
* Block addition form is now rendered on client-side
* Block type changing is now possible
* Block editing form rendering is now also on client side
* New JS helpers added to work with form elements inside ShadowDOM nicely:
  * `$().cs().tooltips_inside()`
  * `$().cs().radio_buttons_inside()`
  * `$().cs().tabs_inside()`
  * `$().cs().connect_to_parent_form()`
* Infinite route nesting levels support implemented
* `_` stubs support in `index.json` for API endpoints
* Changing block's permissions is now in modal window instead of separate page, also it is generic and might be used in other places
* Small advancement: `api/{module}/admin/*` URLs are now restricted to administrators only by default just like `admin/*` (no need for additional checks anymore)
* Composer: Composer module now works in interactive mode, meaning it shows current progress almost real time
* Check for password strength on client size (during password change)

Updates:
* New upstream version of UPF
* New upstream version of jsSHA
* New upstream version of WebComponents.js

Fixes and small improvements:
* Simplification of permissions management, less DB request on change
* Fix for situation when user might have duplicated groups
* Simplification of users groups management
* CRUD trait fix when setting data model value as `Closure` object
* UIkit themes fixes
* `uk-modal-dialog-slide` class is not used in UIkit anymore
* More flexible modal wrapper
* Fix for inclusion order of UIkit and its components
* Using `file_exists_with_extension()` where relevant
* Switching from `openssl_random_pseudo_bytes()` to `random_bytes()`
* Unused JS function removed
* Translations initialization on earlier stage, allows to use in Polymer elements even on `created` stage
* Simpler access to translations in some components
* Now it is possible to get/delete multiple groups with single call
* Simplification in `\cs\Permissions` class
* Simplification of files extraction checks, fixes for HHVM in packages installation/extraction similarly to system installation
* Moved language initialization on frontend to even earlier stage, fixes Polymer elements in administration UI
* More API endpoints for administrator, will be used in future to move more features to UI instead of rendering on server side
* Fix for simple modal helper bug with non-pixel width size specified
* Small fix to System distributive package generation
* Some pieces of unused code removed
* Do not force to close connection
* Fix fo files-based routing
* Fotorama: Fix for fotorama and youtube video going fullscreen (reported upstream as https://github.com/artpolikarpov/fotorama/pull/410)
* Shop: Fix for only one image was shown on item page in Shop module
* https://github.com/uikit/uikit/pull/1304 applied on top of current minified build in order to fix UIkit tabs in ShadowDOM.
* https://github.com/uikit/uikit/pull/1364 applied on top of current minified build in order to fix UIkit tooltips with empty title
* Use UIkit.notify instead of alert to show AJAX errors
* `WebKitMutationObserver` dropped since it is supported by all modern webkit browsers
* Observing for DOM nodes inserting decoupled into separate function
* Fix for contents of `Allow` header when `405 Method Not Allowed` error happens with controller-based routing
* API for setting permissions of some item (by specified label and group)
* HAML added to code style config
* Experimental HAML as primary markup for Polymer elements, because it is too long otherwise
* Composer: Composer events added:
  * Composer/generate_package
  * Composer/generate_composer_json
  * Composer/Composer
  * Composer/updated
* Composer: Added hacked Composer application, how can catch and work with Composer instance
* Fix for `\cs\CRUD` not reading data if there is closure as element of data model
* Composer: Composer is now running in debug mode to output any errors that might happen
* Composer: Added possibility to configure `auth.json` for Composer (primary to raise GitHub's API rate limit)
* Composer: `auth.json` can be modified through `Composer/generate_composer_json` event
* Composer: `composer.json` now contains formatted source instead of single line
* New event added:
  * System/Page/includes_dependencies_and_map
* Some simplification and reducing of code duplication in `\cs\Page\Includes`
* Shop: Fixes for operating under Http server module
* Composer: Composer log verbosity depending on system configuration
* Static pages: Automatic Open Graph tags with images for Static Pages module
* Translations update
* Interactive installation mode added to CLI
* Help in installer with current file name instead of generic one
* Using information from controller render for frontend inclusions, since it gives more correct information about how page was rendered
* Encryption improvements, but will not be able to decode old encrypted data (not likely to have big impact):
  * much simple and straightforward implementation
  * using of OpenSSL instead of Mcrypt (we do not depend on it anymore as it is not maintained for a very long time)
  * configurable encryption method
  * default encryption method changed to `aes-256-cbc` (will not be able to decrypt previously encrypted data)
* HTTP request was incorrectly determined as HTTPS under HHVM
* WebSockets: Fixed connection between two servers
* WebSockets: Added option for specifying DNS server (defaults to 127.0.0.1)
* WebSockets: Updated DB structure, now we track order of new servers addition to servers pool
* WebSockets: Fixed frontend authentication because properties of `cs.Language` are objects, not strings
* WebSockets: Other tweaks, code reformatting
* WebSockets: Fix for message target parsing
* WebSockets: Event `WebSockets/register_actions` was dropped
* WebSockets: Events `WebSockets/$action` replaced by single `WebSockets/message`, also access to `$connection` was added
* WebSockets: Readme updated and extended
* WebSockets: Added possibility to send message for users, that are specified with filter (this feature is much more flexible than anything before)
* WebSockets: Dropped automatic server start on message sending - it makes no sense since no one listening
* WebSockets: Dropped `safe_mode` check since it was removed in PHP 5.4
* WebSockets: Added possibility to specify address for server so that other servers may reach it also (useful for multi-server setups)
* WebSockets: Changed format of server running, a bit simpler now
* WebSockets: WebSockets are no longer assumed to be running on `127.0.0.1`, real servers pool is used instead
* WebSockets: Duplicated addresses of single server in pool now handled nicely
* Additional check for host correctness in `$_SERVER` wrapper
* Additional protection against timing attacks  when working with sessions
* Fix for `Vary` header, should be `Accept-Language` instead of `Content-Language`
* WebSockets: Disconnection event added
* Added contributing file that points to new page on wiki with explanations how to setup everything and send patch back
* New functions `status_code()` and `status_code_string()` introduced, both doesn't interact with global environment directly, but through `_header()` instead
* Long keys in installer now uses two dashed, not one, namely `--help` instead of `-help`, however, old way is kept for backward compatibility until 3.0
* Long keys support for builder
* Other smaller fixes and improvements

Deprecations:
* `\cs\Encryption` class is deprecated now and will be dropped in 3.0: it is not used by any standard component or by system core itself, so there is no need for it here
* `code_header()` function deprecated (use `status_code()` instead, it does exactly the same and have the same arguments)

Possible partial compatibility breaking (very unlikely, but still possible):
* TinyMCE: TinyMCE methods for re-initialization accept DOM element as argument instead of ID

Latest builds on [SourceForge downloads page](https://github.com/nazar-pc/CleverStyle-CMS/wiki/Download-installation-packages) ([details about installation process](https://github.com/nazar-pc/CleverStyle-CMS/wiki/Installation)) or download source code and [build it yourself](https://github.com/nazar-pc/CleverStyle-CMS/wiki/Installer-builder)
