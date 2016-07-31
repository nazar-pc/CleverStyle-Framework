# 1.0.0+build-749: First release 1.0.0

First release after more than 4 years of development.
[Installation instructions](/docs/installation/Installation.md)
Feel free to report bugs if any, feature requests and questions, they are highly appreciated!

# 1.1.3+build-757: New stable release 1.1.3

Better testing environment.
Improved and extended OAuth2 module:
* now supports `Authentication: Bearer {access_token}` from RFC 6750
* breaking change in OAuth2 module - `/OAuth2/token*` requests should be made with `POST` not `GET` method to follow RFC 6749
* improved documentation

Bunch of system constants removed.
Of course, some bug fixes to improve stability.
New builds are not attached anymore to releases:
* you can always find latest builds on [downloads page](/docs/installation/Download-installation-packages.md)
* or download source code and [build it yourself](/docs/installation/Installer-builder.md)

# 1.5.0+build-764: New stable release 1.5.0

Added 2 new triggers:
* `System/Config/before_init`
* `System/Config/after_init`

[wiki](/docs/%24Config.md#systemconfigbefore_init)

`cs\CRUD` trait can handle multilingual functionality automatically: [wiki](/docs/backend-system-traits/CRUD.md#ml-prefix-and-this-data_model_ml_group)

`cs\DB\_Abstract::insert()` now can accept single-dimensional array of parameters if needed so.

API requests fix (for certain cases).

Fixes in Blogs and Photo gallery modules.

Latest builds on [downloads page](/docs/installation/Download-installation-packages.md) or download source code and [build it yourself](/docs/installation/Installer-builder.md)

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

Latest builds on [downloads page](/docs/installation/Download-installation-packages.md) or download source code and [build it yourself](/docs/installation/Installer-builder.md)

# 1.16.0+build-790: The most stable, extensible and Web Components-ready version ever

New features:
* `wait` cursor while web components are cooking
* System styles are ShadowDOM-ready
* [Extremely flexible patching of system classes](/docs/System-classes-extension.md)
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

Latest builds on [downloads page](/docs/installation/Download-installation-packages.md) or download source code and [build it yourself](/docs/installation/Installer-builder.md)

# 1.22.2+build-811: More features and new Prism plugin

New features:
* Inverse dependency through `provide` property in `meta.json` like `Blog/post_patch` (crucial for same name extending of Web Components).
* Added controller-based routing support in addition to files-based ([documentation](/docs/backend-advanced/Routing.md))
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

Latest builds on [downloads page](/docs/installation/Download-installation-packages.md) or download source code and [build it yourself](/docs/installation/Installer-builder.md)

# 1.40.2+build-853: Huge leap forward to the future, WebSockets and Shop modules added

New components:
* New **WebSockets** module makes WebSockets usage with CleverStyle CMS ridiculously easy
* New **Shop** module, provides simple, but highly extensible and customizable shopping functionality

New features:
* `$_SERVER` superglobal is now [wrapped by object](/docs/$_SERVER.md) to provide simplified, more functional and secure alternative to raw elements (while keeping original array-like behavior for compatibility)
* `\cs\Language::init()` and `::url_language()` methods added
* `\cs\User::get_session()` refactored to `::get_session_id()` which much better explains what function actually do
* Possibility to attach volume to Docker container with demo
* Triggers are now Events, corresponding class `Event` [added](/docs/backend-system-objects/$Event.md), `Trigger` still exists for backward compatibility, but uses `Event` under the hood (transition is simple - `Trigger::register() -> Event::on()`, `Trigger::run() -> Event::fire()`, also `Event` have some new functionality with methods `::off()` and `::once()`, `events.php` is used now instead `trigger.php` which is deprecated now
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

Latest builds on [downloads page](/docs/installation/Download-installation-packages.md) or download source code and [build it yourself](/docs/installation/Installer-builder.md)

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

Latest builds on [downloads page](/docs/installation/Download-installation-packages.md) or download source code and [build it yourself](/docs/installation/Installer-builder.md)

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

Latest builds on [downloads page](/docs/installation/Download-installation-packages.md) or download source code and [build it yourself](/docs/installation/Installer-builder.md)

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
* `\cs\Config::$can_be_admin` property (use method with the same name instead)
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

Latest builds on [downloads page](/docs/installation/Download-installation-packages.md) or download source code and [build it yourself](/docs/installation/Installer-builder.md)

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

Latest builds on [SourceForge downloads page](/docs/installation/Download-installation-packages.md) ([details about installation process](/docs/installation/Installation.md)) or download source code and [build it yourself](/docs/installation/Installer-builder.md)

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

Latest builds on [SourceForge downloads page](/docs/installation/Download-installation-packages.md) ([details about installation process](/docs/installation/Installation.md)) or download source code and [build it yourself](/docs/installation/Installer-builder.md)

# 2.28.0+build-1076: Semantics matters

Significant amount of changes comparing to few previous releases.

Starting from this release and onwards we'll move components towards using JSON-LD and Web Components. First module that works in such way on user side is Blogs.

Also this release includes important fixes for some regressions that made it unable to install module that uses DB in simple mode because of incorrect dependencies resolution.

As usual many small fixes and improvements, especially exiting improvements happened in `\cs\CRUD` trait that now supports JSON type, html with iframes and automatically handles files uploads + introduces new simplified interface.

And the last important thing here: all new builds and git tags are [digitally signed](/docs/installation/Download-installation-packages.md#digital-signature) now!

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
* Sacrifice a bit of performance (loop will not be too big to cause any measurable performance issues) to make `\cs\DB\MySQLi::f()` code smaller and simpler
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

Latest builds on [SourceForge downloads page](/docs/installation/Download-installation-packages.md) ([details about installation process](/docs/installation/Installation.md)) or download source code and [build it yourself](/docs/installation/Installer-builder.md)

# 2.43.0+build-1113: Write less, do more

The main feature of this release is further extension of already functional `\cs\CRUD` trait. Now in addition to simple CRUD operations, files tags handling (was improved also), multilingual strings handling even more advanced feature was added: joined tables.
Joined tables allow to make CRUD operations on tables that are tightly connected to main table, such as:
* post tags
* shop item attributes
* shop item images

Now you can manage things of such kind automatically together with fields from main table just by specifying all such tables in common data model, read [updated documentation](/docs/backend-system-traits/CRUD.md) for more details of implementation.
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

Latest builds on [SourceForge downloads page](/docs/installation/Download-installation-packages.md) ([details about installation process](/docs/installation/Installation.md)) or download source code and [build it yourself](/docs/installation/Installer-builder.md)

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
* Additional protection against timing attacks when working with sessions
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

Latest builds on [SourceForge downloads page](/docs/installation/Download-installation-packages.md) ([details about installation process](/docs/installation/Installation.md)) or download source code and [build it yourself](/docs/installation/Installer-builder.md)

# 2.119.0+build-1286: No frontend on backend

CleverStyle CMS never had usual templates on backend, but used HTML generation instead. That worked to some degree, but now there are much better Web Components for that!
This release brings significantly extended API for administration actions, many thing now done purely on frontend with Web Components interacting with server through API, the rest of things will migrate to this approach in further releases.
Such transition not only clearly separates backend code from frontend, but also improves backend code quality, which in nice thing.

Also there are few nice features like associative arrays support in `\cs\CRUD`, wildcard syntax support in `map.json` and multiple improvements to JS helpers to deal with UIkit components and other things under Shadow DOM.

This is likely last release from 2.x series, 3.x is coming with new awesome features, large updates and dropped deprecated functionality!

New components:
* None

New features:
* Added automatic support for `OPTIONS` request method, even if it is not defined (works like for unsupported method, but responses with status code 200 instead of 501)
* New events:
  * System/Session/load
  * System/Session/add
* `\cs\CRUD` now can accept associative arrays of arguments, not only indexed with proper order
* Allow underscore in HTTP request method
* Wildcards support in `map.json` (more details in [updated documentation](/docs/quick-start/Module-architecture.md#includesmapjson))
* Composer assets: Ignore packages on earlier stage, this improves installation time and slightly simplifies runtime

Updates:
* New upstream version of WebComponents.js

Fixes and small improvements:
* Ignore templates in tabs helper
* UIkit hacked to ignore templates inside switcher component, reported upstream as https://github.com/uikit/uikit/pull/1405
* UIkit hacked to support `.uk-tab > li > a` without any attributes, reported upstream as https://github.com/uikit/uikit/pull/1406
* Added UIkit hack to make dropdown working under Shadow DOM
* User's permissions editing form is rendered purely on frontend, necessary API endpoints added
* Users list rendered on client side
* Groups list rendered on client side
* Dropdown flickering fix
* Permissions list rendered on client side using data from API
* New API endpoint for getting list of blocks
* UIkit modal buttons translation
* Permission deletion done via API on frontend
* Splitting API controller into more traits
* Permissions addition and editing now done through API on frontend
* `cs.prepare_attr_value()` function added on frontend similar to `h::cs.prepare_attr_value()` on backend
* Small fixes and tweaks in `cs\Groups::del()`
* Groups addition, editing and deletion now done through API on frontend
* Groups management API extended
* Nice load animations added to some Web Components
* Significantly simplified `\cs\Group` class using CRUD features
* Users and bots addition now done on frontend through API
* Status code for groups and permissions addition changed from 200 to 201
* Stricter check during changing users data, some refactoring
* Fix for login hash was remaining in cache after login change, which might cause unexpected issues
* Raw user data editing removed
* Proper handling of setting array of user's data
* Check on frontend not only strength, but also length of the password
* API for user data changing added
* Timezones api endpoint, formatted data added to users api endpoint
* Typos in translations
* Check if user is blocked at sign in time
* Languages getting API
* Fix for user password setting through API
* Users and bots editing now done on frontend through API
* If there is not handler for request method, respond `501 Not Implemented`, rather than `405 Method Not Allowed`
* Handling unknown HTTP methods decoupled into separate method
* `\cs\Session` now uses `\cs\CRUD` trait to simplify many things
* API endpoints for users searching in administration
* Missing slashes for inputs in HAML files
* Users API fixes and improvements
* Users list now rendered on frontend through API
* Http server: Refactoring of request class
* Fix for user's permissions getting and setting
* Refactoring of `cs\Index` class, routing methods decoupled into `cs\Index\Router` trait

Deprecations:
* None

Possible partial compatibility breaking (very unlikely, but still possible):
* `\cs\Group::get()` signature changed, rarely used argument dropped
* `\cs\Group::set()` method signature changed, group data will be dropped and not available anymore
* `data` column dropped from groups table

Latest builds on [SourceForge downloads page](/docs/installation/Download-installation-packages.md) ([details about installation process](/docs/installation/Installation.md)) or download source code and [build it yourself](/docs/installation/Installer-builder.md)

# 3.145.7+build-1787: Pure frontend

This release is a bit revolutionary. Few very important steps were made towards excellence and it took quite a lot of time to get here, but result is awesome!

First important step is migration from Polymer 0.5.x to Polymer 1.x. This is a big step, significant bc-break, but eventually improved performance, new features and improved API worth it.

Second big step is cleaning frontend from UI framework (historically, jQuery UI was used initially and UIkit later), now UIkit is removed and CleverStyle Widgets added instead.
This important step resulted in significant JS/CSS size reduction, awesome Shadow DOM and data-bindings integration (it was very buggy in UIkit and not maintained by upstream developers).

Another consequence of dropping UI framework is that NO STYLING IS APPLIED TO PAGE BY DEFAULT! Literally nothing!
There are some defaults that can be optionally used, but nothing is used by default. This gives developers complete freedom in styling without fighting with styling overrides and third-party themes can be ported without any conflicts in very short time.

And one more good thing on frontend - all system UI is now based on Web Components and interacts with server through API, so backend is cleaner and frontend can be modified if needed without any issues.

On backend side new Uploader module added that is radically simplified re-implementation of Plupload module (and compatible with it).

Also quality of backend code improved significantly according to Scrutinizer results.

Comprehensive change log is below and [documentation](https://github.com/nazar-pc/CleverStyle-CMS/tree/master/docs) is updated accordingly already (and extended to better cover frontend things)!

New components:
* **Uploader** module (replaces Plupload, which was deprecated and will be removed after release), is based on Plupload module, but uses simple standalone implementation of files uploading as wrapper around `$.ajax`, almost the same API as was in Plupload modules before (now API is the same in both)
* **Service Worker cache** module added as attempt to improve subsequent page loads with Service Worker (if available, still experimental)

New features:
* System and all components migrated from Polymer 0.5.x to Polymer 1.x
* Polymer behaviors added:
  * `cs.Polymer.behaviors.Language` for multilingual support in custom elements
  * `cs.Polymer.behaviors.cs` for access to `window.cs` as just `cs` in custom elements
* The whole administration interface now works through API purely on frontend side (including navigation between pages, there are API endpoints to everything configurable in administration)
* All user-facing interface like profile settings and password changing now works through API purely on frontend side
* Full PHP 7 support
* CleverStyle Widgets added instead of UIkit (which was removed entirely) with following custom elements:
  * `cs-button`
  * `cs-form`
  * `cs-icon`
  * `cs-input-text`
  * `cs-label-button`
  * `cs-label-switcher`
  * `cs-link-button`
  * `cs-nav-button-group`
  * `cs-nav-dropdown`
  * `cs-nav-pagination`
  * `cs-nav-tabs`
  * `cs-notify`
  * `cs-progress`
  * `cs-section-modal`
  * `cs-section-switcher`
  * `cs-select`
  * `cs-textarea`
  * `cs-tooltip`
* Jade is used instead of plain HTML for custom elements
* LiveScript is used for all new code instead of CoffeeScript
* Few useful methods added on frontend
  * `cs.ui.modal()`
  * `cs.ui.simple_modal()`
  * `cs.ui.notify()`
  * `cs.ui.alert()`
  * `cs.ui.confirm()`
* Flexy-plexy now included instead of UIkit's grid system
* Shared styles added (should be declared explicitly in order to be used, nothing is used by default, also usage on global level doesn't affect custom elements, each element should explicitly declare usage as well):
  * `normalize` provides custom elements-aware version of normalize.css
  * `basic-styles-alone` provides custom provide advanced basic styling (including sane typography) on top of `normalize`
  * `advanced-styles-alone` contains utility classes added instead of ones found in UIkit:
    * `.cs-text-primary`
    * `.cs-text-success`
    * `.cs-text-warning`
    * `.cs-text-error`
    * `.cs-text-center`
    * `.cs-text-left`
    * `.cs-text-right`
    * `.cs-text-lead`
    * `.cs-text-bold`
    * `.cs-text-italic`
    * `.cs-block-primary`
    * `.cs-block-success`
    * `.cs-block-warning`
    * `.cs-block-error`
    * `.cs-margin`
    * `.cs-margin-top`
    * `.cs-margin-bottom`
    * `.cs-margin-left`
    * `.cs-margin-right`
    * `.cs-margin-none`
    * `.cs-padding`
    * `.cs-padding-top`
    * `.cs-padding-bottom`
    * `.cs-padding-left`
    * `.cs-padding-right`
    * `.cs-padding-none`
    * `.cs-cursor-pointer`
    * `.cs-table`
  * `basic-styles` is `normalize` and `basic-styles-alone` together
  * `advanced-styles` is `basic-styles` and `advanced-styles-alone` together
* `\cs\ExitException` can now handle error codes and messages, used instead of `error_code()` + `Page::instance()->error()`
* JS polyfills are now separated and included only for IE/Edge browsers, since all other browsers already have support for all necessary features
* `cs.Event` is now Promise-based, accepts Promise as callback result
* New backend events:
  * `OAuth2/custom_allow_access_page`
  * `admin/System/components/modules/default` (replaces `admin/System/components/modules/default_module/process`)
  * `admin/System/components/plugins/update/before` (replaces `admin/System/components/plugins/update/process/before`)
  * `admin/System/components/plugins/update/after` (replaces `admin/System/components/plugins/update/process/after`)
  * `admin/System/components/modules/update/before` (replaces `admin/System/components/modules/update/process/before`)
  * `admin/System/components/modules/update/after` (replaces `admin/System/components/modules/update/process/after`)
  * `admin/System/components/modules/uninstall/before`
  * `admin/System/components/modules/uninstall/after`
  * `admin/System/components/modules/install/before`
  * `admin/System/components/modules/install/after`
  * `admin/System/components/modules/update_system/before` (replaces `admin/System/components/modules/update_system/process/before`)
  * `admin/System/components/modules/update_system/after` (replaces `admin/System/components/modules/update_system/process/after`)
  * `admin/System/components/modules/enable/before` (replaces `admin/System/components/modules/enable/process`)
  * `admin/System/components/modules/enable/after`
  * `admin/System/components/modules/disable/before` (replaces `admin/System/components/modules/disable/process`)
  * `admin/System/components/modules/disable/after`
  * `admin/System/components/plugins/enable/before` (replaces `admin/System/components/plugins/enable/process`)
  * `admin/System/components/plugins/enable/after`
  * `admin/System/components/plugins/disable/before` (replaces `admin/System/components/plugins/disable/process`)
  * `admin/System/components/plugins/disable/after`
  * `System/Page/display/before` (replaces `System/Page/pre_display`)
  * `System/Page/display/after` (replaces `System/Page/display`)
  * `admin/System/components/themes/current/before`
  * `admin/System/components/themes/current/after`
  * `admin/System/components/themes/update/before`
  * `admin/System/components/themes/update/after`
  * `System/Index/load/before` (replaces `System/Index/preload`)
  * `System/Index/load/after` (replaces `System/Index/postload`)
* New frontend events:
  * `admin/System/components/modules/default/before`
  * `admin/System/components/modules/default/after`
  * `admin/System/components/plugins/disable/before`
  * `admin/System/components/plugins/disable/after`
  * `admin/System/components/modules/disable/before`
  * `admin/System/components/modules/disable/after`
  * `admin/System/components/modules/enable/before`
  * `admin/System/components/modules/enable/after`
  * `admin/System/components/plugins/enable/before`
  * `admin/System/components/plugins/enable/after`
  * `admin/System/components/plugins/update/before`
  * `admin/System/components/plugins/update/after`
  * `admin/System/components/plugins/update/before`
  * `admin/System/components/plugins/update/after`
  * `admin/System/components/modules/update/before`
  * `admin/System/components/modules/update/after`
  * `admin/System/components/modules/uninstall/before`
  * `admin/System/components/modules/uninstall/after`
  * `admin/System/components/modules/install/before`
  * `admin/System/components/modules/install/after`
  * `admin/System/components/modules/update_system/before`
  * `admin/System/components/modules/update_system/after`
  * `admin/System/components/themes/current/before`
  * `admin/System/components/themes/current/after`
  * `admin/System/components/themes/update/before`
  * `admin/System/components/themes/update/after`
  * `cs-system-sign-in`
* No need for `session` in XHR request body (yay!)
* New constants
  * `\cs\Config::SYSTEM_MODULE`
  * `\cs\Config::SYSTEM_THEME`
  * `\cs\Config\Module_Properties::ENABLED`
  * `\cs\Config\Module_Properties::DISABLED`
  * `\cs\Config\Module_Properties::UNINSTALLED`
* Support for nested structures in JSON files with translations
* Added useful methods for `\cs\Config\Module_Properties`:
  * `enabled()` (replaces `active()`)
  * `disabled()`
  * `installed()`
  * `uninstalled()`
* Added prefix support to `cs.Language` like `cs.Language(prefix)`, works similarly to `new \cs\Language\Prefix($prefix)` on backend
* Also prefix support added to Language behavior for Polymer `cs.Polymer.behaviors.Language(prefix)`
* System, TinyMCE: New wrapper elements added in order to simplify working with WYSIWYG editor and provide Shadow DOM support (replaces helper functions `editor_deinitialization()`, `editor_reinitialization()` and `editor_focus()`, they are not needed anymore):
  * `cs-editor`
  * `cs-editor-inline`
  * `cs-editor-simple`
  * `cs-editor-simple-inline`
* Allow to capture only AJAX errors with specific status code using callbacks like `error_404`, other status codes will use generic error processing
* Added possibility to override implementation of Polymer element using `extends` or `overrides` with the same name as element similarly to what was present when using Polymer 0.5.x, but without actual extending custom elements (because it is not supported yet by Polymer 1.x)
* Make session and some other cookies HTTP only
* Do not make language redirects, we can modify page URL to include language prefix on frontend without any help from backend
* `\cs\Language\Prefix` now can return regular translation if prefixed version not found similarly to how it works on frontend
* Separate IE-specific polyfills and include them only for IE/Edge, will also simplify removal their support in future
* Custom build of WebComponents.js added with polyfills removed
* Use compressed CSS/JS/HTML in administration unless `?debug` is present in URL

Updates:
* New upstream version of Polymer 1.x with patches on top of it
* New upstream version of WebComponents.js
* New upstream version of autosize
* New git version of jQuery 3.0
* New upstream version of PHPMailer
* New upstream version of HybridAuth
  * New providers:
    * Slack
    * WarGaming
    * Xuite
  * Removed providers:
    * Viadeo
* New upstream version of TinyMCE with patches for Shadow DOM support and with standalone JS core instead of jQuery-based (with new `codesample` plugin added)
* New upstream version on Picturefill
* New upstream version on BananaHTML
* New git version on Composer
* New upstream version of UPF
* New upstream version of jsSHA

Fixes and small improvements:
* Users search API fix
* Dropped layout attributes from Polymer 0.5 in favor of iron-flex-layout
* Removing Polymer inclusion from Web Components, since Polymer is already here
* New upstream version of `run-tests.php` from PHP repository
* Refresh styling when all components are ready
* Improve includes processing to handle `<style is="custom-style">` and new CSS imports nicely `<link href="style.css" rel="import" type="css">`
* JS minification added
* Do not inline in CSS files bigger than 3 KiB
* Handle relative links with query string in CSS
* Add query string with part of md5 digest to track content changes
* Fixes for `\cs\CRUD` and `\cs\CRUD_helpers` traits
* Dropped common theme styles as unnecessary
* Cookie path configuration is not necessary, especially, since installation into subdirectory is not supported
* `cs-table` is only used as class since now
* `.cs-table` moved into advanced styles and doesn't penetrate Shadow DOM by default
* Fix for default configuration options were not set during modules installation
* Small tweak to prevent error in some cases
* Upload only release tags
* Handle nicely `style[include]` as special feature in Polymer
* Do not join various styles in minifier - it requires quite complex regexps and doesn't benefit that much
* Do not remove semicolons in minifier - this causes problems for Polymer with some rules
* Add hashes even to non-cached includes in order to avoid problems in production, when CDN caches files in administration interface
* Permissions API fix
* TinyMCE: TinyMCE now sets textarea value after each change
* Unused translations removed
* Packages manipulation functions are now in standalone class instead of trait
* Fix for dependencies resolution when there is multiple requirements/conflicts for the same package
* Generic API endpoint for files uploads in administration (for phar CleverStyle CMS-specific installers, simple checks are present)
* Fix for 501 status code not returned if HTTP method not implemented
* Using `rmdir_recursive()` in some places instead of some custom alternatives
* User block with sign-in/sign-out and other features are now custom element, no global event listeners for some classes
* Sign in, registration and restore password modal dialogs are pretty generic, so they are not in theme, but in system itself
* Fix for multilingual redirect and `link[rel=alternate][hreflang]`
* Remove redundant usages of `h::prepare_attr_value()` since with BananaHTML it will be applied to attributed automatically
* User's profile page removed
* Improving PhpDoc on `\cs\DB\_Abstract`, removed redundant `$indexed` argument for `::fs()`, `::fas()`, `::qfs()` and `::qfas()` methods since id does nothing in this context
* `\cs\Config::instance()->core['themes']` option removed since it is actually redundant
* OAuth2: Do not send 403 because Windows Phone in particular will have difficulties with this
* New method `\cs\Config::cancel_available()` added to easily check whether it is possible to cancel applied changes of configuration
* `\cs\Config::cancel()` now returns boolean result
* Hide Static pages module in main menu
* Composer: Do not run Composer's update when Composer itself is manipulated
* Composer: Do not use Composer while it is not actually enabled
* Blogs: Show head actions regardless on presence of posts in current list
* Fix for absolute path determination in FileSystem cache engine under OS Windows
* Fix for filling basic settings during modules installation because events now fired from API
* Improve security by not allowing jQuery to execute JS after AJAX responses with `Content-Type: application/javascript`
* Simplified `cs.Event` internals by utilizing advanced features of Promises
* Improved JS minification when complex comments structures present
* Fix for admin pages includes included on regular pages
* `cs.Language` source moved into own file
* Switch from `apc_*` functions to `apcu_*`
* Fix for includes from regular pages were not included in administration (inverse should not be true however)
* Do not force `box-sizing` on everything, this might and will break some third-party components
* Composer assets: Add `iron-flex-layout` to Bower packages provided by system
* `set_core_ml_text()` function added similarly to `get_core_ml_text()`
* Automatic cleanup of old unused options from core settings
* Plupload: Plupload module modified so that it can now be disabled and new uploaded files will be served by other module, but plupload's files will be still around
* Composer: Composer module fix for installing components
* Composer assets: UIkit is not provided by system anymore
* `\cs\Session::is_session_owner()` method added for checking session data against user agent, remote addr and IP to confirm that this is the same user tries to use session
* WebSockets: WebSockets module now uses actual headers to get user agent and other things about client rather that asking JS client to send them explicitly
* `\cs\Language` methods updated:
  * `get()` got `$prefix` argument
  * `format()` got `$language` and `$prefix` arguments
* `\cs\Language\Prefix` methods updated:
  * `format()` got `$language` argument
* Use native `Promise.all()` instead of jQuery's `$.when()`
* Administration navigation on frontend, no page-specific markup on backend
* Fix for Cookie domain detection during installation
* Fix for Docker image building
* GD package added to Dockerfile to enable Photo gallery module in demo
* Photo gallery: Fix for incorrect image deletion in Photo gallery module when preview was not yet created
* Raise MySQL and MariaDB versions requirements since Shop module requires FULLTEXT indexes on InnoDB table
* Use MariaDB 10.1 instead of MySQL 5.5 from default packages when building Docker image
* Shop: Small API fixes and nicer handling the case when user name was not specified during order completion
* Shop: Fix for total price number on orders page in Firefox
* Support id field other than `id` in `\cs\CRUD_helpers` trait since `\cs\CRUD` already supports this
* Gravatar support is now optional and configurable; it is disabled by default for new installations and enabled for upgraded installations to preserve existing behavior
* Do not delete backup fs and meta files, might be useful on later stages for upgrade scripts
* Some UI fixes, no forcing styling on `[hidden]` elements, target elements should care about this by themselves

Deprecations:
* Plupload module deprecated and is left only for smoother transition and will be removed after release, please, migrate to Uploader
* `\cs\Page::error()` usage is highly discouraged, use `\cs\ExitException` instead
* `\cs\Config\Module_Properties::active()` (replaced with `\cs\Config\Module_Properties::enable()`, will be removed after release)

Dropped backward compatibility:
* All deprecated functionality was dropped
* All components now require System of version >= 3.x, but < 4.0 since API will be backward-compatible for whole 3.x series
* Dropped update support from System versions older than previous release, the same for all components
* Dropped UIkit completely, CleverStyle Widgets are used instead
* All helpers in `$.cs` and `$().cs()` removed, they worked primarily with UIkit which is not present anymore
* `data-title` attribute support dropped from system, `tooltip` attribute can be used with the same result (only for regular elements, not custom)
* Tenebris theme was not actively used/maintainer, and thus removed:(
* `\ExitException` refactored into `\cs\ExitException` for better consistency
* Internally used JS functions removed:
  * `cs.blocks_toggle()`
  * `cs.async_call()`
  * `cs.observe_inserts_on()`
* `error_code()` function, use `\cs\ExitException` instead, it provides complete replacement
* Removed backend events:
  * `admin/System/components/modules/default_module/prepare`
  * `admin/System/components/modules/default_module/process` (replaced with `admin/System/components/modules/default`)
  * `admin/System/components/plugins/disable/prepare`
  * `admin/System/components/plugins/disable/process` (replaced with `admin/System/components/plugins/disable/before`)
  * `admin/System/components/modules/disable/prepare`
  * `admin/System/components/modules/disable/process` (replaced with `admin/System/components/modules/disable/before`)
  * `admin/System/components/modules/enable/prepare`
  * `admin/System/components/modules/enable/process` (replaced with `admin/System/components/modules/enable/before`)
  * `admin/System/components/plugins/enable/prepare`
  * `admin/System/components/plugins/enable/process` (replaced with `admin/System/components/plugins/enable/before`)
  * `admin/System/components/plugins/update/prepare`
  * `admin/System/components/plugins/update/process/before` (replaced with `admin/System/components/plugins/update/before`)
  * `admin/System/components/plugins/update/process/after` (replaced with `admin/System/components/plugins/update/after`)
  * `admin/System/components/modules/update/prepare`
  * `admin/System/components/modules/update/process/before` (replaced with `admin/System/components/modules/update/before`)
  * `admin/System/components/modules/update/process/after` (replaced with `admin/System/components/modules/update/after`)
  * `admin/System/components/modules/uninstall/prepare`
  * `admin/System/components/modules/uninstall/process` (replaced with `admin/System/components/modules/uninstall/before` and `admin/System/components/modules/uninstall/after`)
  * `admin/System/components/modules/install/prepare`
  * `admin/System/components/modules/install/process` (replaced with `admin/System/components/modules/install/before` and `admin/System/components/modules/install/after`)
  * `admin/System/components/modules/db/prepare`
  * `admin/System/components/modules/storage/prepare`
  * `admin/System/components/modules/db/process`
  * `admin/System/components/modules/storage/process`
  * `admin/System/components/modules/update_system/prepare`
  * `admin/System/components/modules/update_system/process/before` (replaced with `admin/System/components/modules/update_system/before`)
  * `admin/System/components/modules/update_system/process/after` (replaced with `admin/System/components/modules/update_system/after`)
  * `System/Page/pre_display` (replaced with `System/Page/display/before`)
  * `System/Page/display` (replaced with `System/Page/display/after`)
  * `System/profile/settings`
  * `System/profile/info`
  * `System/Route/pre_routing_replace`
  * `System/Index/preload` (replaced with `System/Index/load/before`)
  * `System/Index/postload` (replaced with `System/Index/load/after`)
* `cs\modules\System\admin\Controller\packages_manipulation::recursive_directory_removal()` method removed since there is `rmdir_recursive()` function already in latest version of UPF
* Generic settings page removed
* Methods `\cs\Config::reload_languages()` and `\cs\Config::reload_themes()` removed, since they are not used anymore
* Method `\cs\Config::can_be_admin()` removed because of corresponding functionality was removed from system core
* OAuth2: Dropped support for `Access-token` header or request parameter
* Helper functions on frontend removed since they are not needed anymore (replaced with elements `cs-editor`, `cs-editor-inline`, `cs-editor-simple` and `cs-editor-simple-inline`):
  * `editor_deinitialization()`
  * `editor_reinitialization()`
  * `editor_focus()`
* Replace and routing options removed from core settings as rarely used and such that can be easily implemented using third-party tools
* Removed following system core options and corresponding functionality:
  * ip_black_list
  * ip_admin_list_only
  * ip_admin_list
* `cs.getcookie()` and `cs.setcookie()` alongside with jQuery cookie plugin removed on frontend
* `\cs\Index` lost many ugly designed features which existing modules do not use anymore
  * Removed properties:
    * `Content`
    * `form`
    * `file_upload`
    * `form_attributes`
    * `buttons`
    * `save_button`
    * `apply_button`
    * `cancel_button_back`
    * `custom_buttons`
    * `action`
  * Removed methods:
    * `content()`
    * `in_admin()`
    * `apply()`
    * `save()`
    * `cancel()`
* Cookie prefix and domain are not needed on frontend anymore
* Also specifying protocol doesn't make sense since already available on frontend
* Do not enforce `POST` method in `$.ajax()` calls, it is explicitly specified where necessary

Latest builds on [SourceForge downloads page](/docs/installation/Download-installation-packages.md) ([details about installation process](/docs/installation/Installation.md)) or download source code and [build it yourself](/docs/installation/Installer-builder.md)

# 3.152.1+build-1808: Few things here and there

This release is incomparably smaller than previous.
It contains several fixes and few nice improvements, dropped upgrade support from versions older than latest stable, but overall nothing dramatic.

New components:
* None

New features:
* WOFF2 support
* Generic abstract class `\cs\Cache\_Abstract_with_namespace` now provides common abstraction for simplified cache engine implementation in cases when there is no native namespace support (APC, Memcached for now)
* Allow avatar uploading in profile settings if necessary module is present

Updates:
* New git version of jQuery 3.0
* New upstream version of HybridAuth
  * New providers:
    * DigitalOcean
    * GitLab
    * MixCloud
    * StackExchange
* New git version of TinyMCE with patches for Shadow DOM support
* Composer updated to 1.0.0-alpha11

Fixes and small improvements:
* Improved `cs-icon` performance
* Fix for including CSS/JS/HTML on administration pages because of compression used - always build public for installed but disabled modules
* Run cleanup on System update to clean caches
* Fix for getting cache item results in `false` being returned when caching is not working even if `$callback` argument was specified
* Optimized sessions table indexes
* Small form fix
* Remove `)` from safe symbols in JS minifier
* Improved mirror check, port number was ignored
* Better error messages handling
* Composer: Autoloader from Composer module now included as early as possible
* `update_versions` is not needed in `meta.json` anymore (but will be present in `meta.json` until 4.x release in order to keep possibility to upgrade from older 3.x versions that rely on `update_versions`)
* Old unused CSS removed
* Fix for `cs` behavior
* Small fix for optional System dependencies
* TinyMCE: `readme.md` added with instructions how to get custom TinyMCE build with Shadow DOM support
* Fix for `h::checkbox` rendering and functioning
* Few smaller fixes

Deprecations:
* None

Dropped backward compatibility:
* Dropped upgrade support from versions earlier than latest stable
* Dropped hack for upgrade from 2.x versions

Latest builds on [SourceForge downloads page](/docs/installation/Download-installation-packages.md) ([details about installation process](/docs/installation/Installation.md)) or download source code and [build it yourself](/docs/installation/Installer-builder.md)

# 3.155.0+build-1818: Security update

This release fixes important flaw in permissions system, update ASAP!

Besides extremely important security update, DarkEnergy theme now uses App Shell for better page load experience and WebComponents.js polyfill is only loaded in browsers that needs it, improving page load for browsers with native Shadow DOM support.

Security fixes:
* Security fix for permissions system to administration pages when no permissions present for target page

New components:
* None

New features:
* Added OTF fonts support
* App Shell approach added to DarkEnergy theme to improve load experience
* WebComponents.js polyfill is only included when needed (not included in Chrome browser on subsequent page opening; only on subsequent opening because of feature detection on frontend)

Updates:
* None

Fixes and small improvements:
* Fixes for modern mime types for font files
* Refactoring of some system components that resulted in improved code quality
* Generate `WebComponentsReady` even if WebComponents.js not included
* Shop: Fix in Shop module for situation when item removed, but order is still present
* Various small fixes and PhpDoc improvements

Deprecations:
* None

Possible partial compatibility breaking (very unlikely, but still possible):
* jQuery ready event is now completely standalone again (not likely, but potential BC-break)

Latest builds on [SourceForge downloads page](/docs/installation/Download-installation-packages.md) ([details about installation process](/docs/installation/Installation.md)) or download source code and [build it yourself](/docs/installation/Installer-builder.md)

# 3.162.4+build-1841: Towards sub-1ms page rendering performance

This release brings variety of performance tweaks starting from avoiding session creation for guests until necessary and finishing with tweaking third-party dependencies.
The goal of sub-1ms rendering time for regular page is not reached yet, but we now have 1.2ms rendering time which is quite close.

Generally, CleverStyle CMS core now works around 3x times faster in simple but common scenarios, which is amazing being already blazing fast!

Also IE10 support was dropped from system core since IE10 is not supported anymore, but new plugin `Old_IE` is here is you still need to support it.

Finally, take a look at [video tutorials for developers](https://www.youtube.com/watch?v=GVXHeCVbO_c&list=PLVUA3QJ02XIiKEzpD4dxoCENgzzJyNEnH) to start with CleverStyle CMS even faster.

Security fixes:
* Do not allow non-admin used to enter admin pages under any conditions even if permissions allow explicitly

New components:
* **Old_IE** plugin added to provide support for older IE versions (10 currently)

New features:
* Performance boost: do not create session for each visitor automatically, create only when it is necessary
* Added removing HTML comments in HTML files processing
* IE10 support dropped (use `Old_IE` plugin if IE10 support is still needed)
* APCu cache engine added and APC now is actually APC (which is important for HHVM which doesn't provide apcu_* functions)
* Small performance tweak: cache modules and plugins `events.php` file existence in order to avoid filesystem scan each time
* Composer: Composer module now states itself as replacement for some bundled system dependencies

Updates:
* New upstream version of UPF:
  * Refactoring of `get_files_list()` function resulted in improved performance
  * Always return array from `get_files_list()` function
  * * Fix for the case when `$prefix_path` is an empty string in `get_files_list()` function (unnecessary starting slash added)
* New upstream version of BananaHTML:
  * Small performance tweaks and refactoring
  * Naive implementation of `::is_array_indexed()` and `::is_array_assoc()` methods, but should be enough in most cases, 10x+ better performance
* New version of Polymer 1.2.4 + git with patches on top and removed Shady DOM support

Fixes and small improvements:
* `\cs\Session::get_user()` now returns integers only, which is simpler to handle
* Fix for infinite loader if there are no permissions
* Fixed APCu availability check, APC engine test unlocked for PHP 7
* Duplicated calls of `\cs\Page::canonical_url()` will not cause duplicated links in page's source code anymore
* Tiny hack to make sure styles properly upgraded with native Shadow DOM
* Tiny performance improvement in `cs\Language::check_locale_header()` method
* Tiny fix in administration navigation
* Composer: UI fix for horizontal scrolling in modal
* Composer: Fix for force update not working
* Composer: Fix for stability requirements not satisfied because of https://github.com/composer/composer/issues/4889

Deprecations:
* None

Possible partial compatibility breaking (very unlikely, but still possible):
* Potential BC break (very unlikely) is that `$user` argument in `\cs\Session::add()` is now mandatory
* Undocumented `\cs\Page::$link`, `::$Search` and `$Replace` properties now have protected access

Latest builds on [SourceForge downloads page](/docs/installation/Download-installation-packages.md) ([details about installation process](/docs/installation/Installation.md)) or download source code and [build it yourself](/docs/installation/Installer-builder.md)

# 3.174.2+build-1883: RequireJS integration, Bower/NPM integration with system and SQLite support

RequireJS is now bundled with system, it also includes aliases to all bundled third-party components like jQuery or jsSHA, so that they can be used transparently, some components are no longer loaded unconditionally, but are used as AMD modules instead only when needed.
Bower/NPM packages installed in website directory are now flawlessly picked by system and aliases to all modules created based on contents of `bower.json` or `package.json` files, so that they are all available as AMD modules for RequireJS.
Composer assets plugin reached 1.0 version and changed its behavior: no files included automatically (files should be now specified explicitly) and integration with system's RequireJS was added. Now all Bower/NPM packages installed using Composer assets plugin will be automatically available as AMD modules, moreover, direct files inclusion within paths `/bower_components` and `/node_modules` will be available to smooth integration even further (though it is not encouraged way of using it).

Another major feature is SQLite support. SQLite database engine was added alongside with ability to install system using SQLite database (no explicit SQLite support within components yet).

The last major change under the hood is translations namespacing. This will ease translations management and support in future.

There was also BananaHTML update which contains performance tweaks for real-world use-cases using simplified rendering path.

Security fixes:
* Small potential security fix in `\cs\Text:set()`

New components:
* None

New features:
* RequireJS bundled with system
  * jsSHA is not loaded by default, only on demand using RequireJS
  * autosize library moved to modules and is now loaded only when necessary
* Modules/plugins RequireJS aliases added, so that `Blogs/xyz` will be loaded by RequireJS as `components/modules/Blogs/includes/js/xyz.js` (functionality might also be used instead of direct module/plugin name)
* Support for automatic AMD modules detection for root `bower_components` and `node_modules` directories added to provide flawless integration with any third-party tools
* New event:
  * `System/Page/requirejs` added
* Composer assets: Backward incompatible version of Composer assets plugin with multiple improvements:
  * RequireJS support
  * Possibility to specify explicitly which files should be included from package
  * Handling `/bower_components/*` and `/node_modules/*` paths for installed packages which are not present in corresponding root directories
* All translations are now namespaced, system translations significantly restructured, unsed translation keys removed
* Basic SQLite support:
  * SQLite database engine
  * System installation and operation with SQLite database engine
  * Conversion of some certain MySQL queries to SQLite equivalents if possible for compatibility
  * Some SQL queries modified to be compatible with both MySQL and SQLite

Updates:
* New upstream version of sprintf.js
* Composer upgraded to Git version
* New upstream version of BananaHTML:
  * Separate, faster path for typical use case added to improve performance in real-world applications

Fixes and small improvements:
* Tiny fix for notification
* Prevent page scrolling in browsers where possible when modal is opened
* Fix for link to module's administration page
* Include CSS/JS/HTML files targeted for System module on all other pages
* Composer: `Composer/Composer` event now have one more argument called `Application`
* Composer: Usability improvement in Composer module: modal's content is scrolling as new data appearing if before update modal was scrolled till the end
* APC and APCu showed separately in about server information (for HHVM that only supports APC)
* Builder refactoring, using `getopt()` instead of manually inspecting CLI arguments
* All system code converted from CoffeeScript to LiveScript
* Builder class refactored to only contain methods related to builder itself, web and cli interfaces moved to separate files
* `bower.json` and `package.json` will now be included into distributive if present
* Guest's username returned from translations in `cs\User::username()` avoiding checking this manually
* Fix for Chromium now willing loading stylesheet with Content-Type `application/octet-stream`
* Fix for reporting incompatible databases and storages
* Create session for CSRF
* Fix for module's databases and storages settings
* Fix for setting value in `set_core_ml_text()` function
* Old redundant method removed
* Stop returning `false` from `\cs\DB\_Abstract::columns()` and `::tables()`, return empty array instead
* Old IE: URL polyfill removed from Old_IE plugin since it is already available in system itself
* Fix for URL polyfill was Template polyfill indeed in system core
* `api/System/blank` endpoint added, primarily for benchmarking purposes (to avoid UI overhead)
* Composer: Update Composer on package update in addition to installation/uninstallation
* Return correct error message when trying to register existing user in administration interface
* Added avatar uploading support when editing user profile in administration
* Tests:
  * Test for prefixed language usage added
  * Better event test
  * Improved MySQLi database engine tests added
  * Group tests added
  * Added permissions tests
* Normalized `\cs\Permission::get()` result (`false` or empty array on failure instead of `null`)
* Better fix for `WebComponentsReady` event in Chromium
* Various small fixes and tweaks

Deprecations:
* None

Possible partial compatibility breaking (very unlikely, but still possible):
* `cs.hash()` signature changes, requires jssha AMD module to be loaded first
* Composer assets plugin will not include any files completely automatically anymore, explicit files listing required or AMD module loading can be used
* Site rules configuration removed from system

Latest builds on [SourceForge downloads page](/docs/installation/Download-installation-packages.md) ([details about installation process](/docs/installation/Installation.md)) or download source code and [build it yourself](/docs/installation/Installer-builder.md)

# 3.212.0+build-1965: Sub-1ms performance achieved, PSR-7 compatibility, request/response abstractions

First of all, there is important psychological achievement: when using Http server under HHVM simple page generation is made in 0.81 ms or 0.00081 seconds - really impressive performance!

Secondly, this version brings a lot of backend improvements beause of introduction of `cs\Request` and `cs\Response` system classes that encapsulate all functionality necessary to handle these 2 important aspects of request processing.
Unification also allowed to make integration with PSR-7, so now it is possible to initialize system from PSR-7 compatible request object and put response into PSR-7 compatible response object, which greatly improves interoperability with third-party tools.
The last piece of this puzzle - bunch of existing functions and even classes to make request processing clear and consistent (backward compatibility still kept with the rest of 3.x versions).

There are many other smaller improvements below, so give it a try!

This is the last major version in 3.x series, see you soon in 4.x world:)

Security fixes:
* None

New components:
* None

New features:
* `cs\Config` doesn't initialize `cs\Language` and `cs\Page` directly, instead - they grab configuration by themselves and if t is not available yet - just subscribe to event and wait when configuration is ready
* New backend events:
  * `System/Config/changed`
  * `System/Request/routing_replace` (replaces `System/Route/routing_replace`)
  * `System/App/construct` (replaces `System/Index/construct`)
  * `System/App/render/before` (replaces `System/Index/load/before`)
  * `System/App/render/after` (replaces `System/Index/load/after`)
  * `System/App/block_render` (replaces `System/Index/block_render`)
  * `System/Page/render/before` (replaced with `System/Page/display/before`)
  * `System/Page/render/after` (replaced with `System/Page/display/after`)* 2 rejected Polymer patches reworked to be standalone and do not need to be injected into Polymer core anymore
* `cs\Request` class added to provide unified source of all needed request information
* `cs\Response` class added to provide unified target for all needed response data (not used everywhere yet)
* Correct class autoloader for installer package instead of manual files inclusion
* It is now possible to specify during registration API call not just email, but also following fields:
  * username
  * password
  * language
  * timezone
  * avatar
* `cs\App` class added (replaces `cs\Index`)
* Added `cs\Request::init_from_psr7()` method to initialize request from PSR-7-compatible object
* Added `cs\Response::output_to_psr7()` method to put response data into PSR-7-compatible response object
* Controller's methods now accept `cs\Request` and `cs\Response` instances as arguments instead of `cs\Request::$route_ids` and `cs\Request::$route_path` as it was before; however, basic compatibility kept by implementing `\ArrayAccess` and  `\Iterator`
* System will now support requests with following Content-Type regardless of system configuration:
  * `application/json`
  * `application/x-www-form-urlencoded`
  * `multipart/form-data`

  This means that files uploading will now work for any request method and all data in body will be parsed even if `php.ini` contains `enable_post_data_reading = Off` (files are only available through `cs\Request` object).
  2 stream wrappers added to make implementation memory efficient and do not duplicate any request data twice (we have single data stream and files are extracted from there on-fly only when needed and only those parts that are needed).
* New method `cs\Page::render()` as replacement for now deprecated `::__finish()`
* System now knows which classes store state that changes between requests and which doesn't, this provides simpler system integration with external tools

Updates:
* New Polymer version without additional patches (sic!), only Shady DOM removed (which is not used anyway), lazy loading is used in Polymer by default
* New upstream Composer 1.0.0-beta1
* New upstream version of RequireJS
* New upstream version of TinyMCE
* New upstream version of Promise polyfill

Fixes and small improvements:
* Http server: Async mode removed from Http module, since in order to benefit from this mode many things should be rewritten significantly and it doesn't seem to practically feasible to do so in most cases, thus such over complication doesn't make practical sense
* Reply with descriptive message if upload failed
* Fix for message during system upgrade
* Small fixes for `cs\Mail` class
* Drop `password_hash` index and change its size to be 255 in order to be ready for possible future algorithm changes (also column comment removed)
* Http server: Simplifications in Http server module because of recent changes in system core
* Normalized user deletion - always integer user id, less complexity
* Small fix, missing necessary re-initialization after configuration change
* Composer: Added automatic scrolling of modal's content when Composer finishes all operations like during regular progress
* Composer: Added firing `admin/Composer/updated` event even during forced Composer updating
* Composer assets: Composer assets dependencies update
* More translations refactoring
* WebSockets: Ratchet/Pawl dependency upgraded to new upstream version
* Remover method for input stream parsing in `cs\Core` class since this functionality is now covered by `cs\Request` class
* Redundant old`cs\Config::$core`'s key `allow_change_language` removed
* Http server: Http server module now reuses system functions for working with global state
* `cs\ExitException` thrown with some status code will set status code on `cs\Response`
* WebSockets: Fix for getting prefixed session cookie in WebSockets module
* Refactoring in order to unify and simplify errors processing
* Simplifications in `cs\User`, now all data are persisted immediately
* Ignore `User::$memory_cache` for guest user
* Provide cleanup of memory cache in `cs\User` object when calling `::disable_memory_cache()`
* Http server: Disable memory cache on `cs\User` object in Http server module
* Removed fix for `WebComponentsReady` event in Chrome since it doesn't actually work:(
* TinyMCE: Fix for content set incorrectly in empty inline editor
* Extract more details about server under HHVM using HHVM's custom ini settings
* Http sever: Much simpler and straightforward Http server integration, no classes overriding, thanks to latest changes in system core
* Http server: Page generation time is now shown correctly under Http server (not number of database queries yet)
* Tiny fix for loading language aliases
* Migrating from using deprecated Classes/functions
* Migrating from deprecated arguments in controllers
* Small refactoring, fix for bots detection
* Use new controller arguments in places where request/response object were created manually
* Http server: Fix for manual autoloader inclusion which is not necessary anymore
* Do not show memory limit under Http server, since in this case `ini_get()` returns -1
* Various small fixes and tweaks

Deprecations:
* `cs\_SERVER` is now just a wrapper around `cs\Request`, it will be around until 4.x, but marked as deprecated
* Deprecated functions:
  * `_setcookie()`
  * `_getcookie()`
  * `_header()`
  * `admin_path()`
  * `api_path()`
  * `current_module()`
  * `home_page()`
  * `shutdown_function()` (simplified and is not used anywhere anymore)
  * `errors_on()`
  * `errors_off()`
  * `interface_on()`
  * `interface_off()`
  * `system_version()`
* `cs\Route` class deprecated, `cs\Request` absorbed and unified all its data
* Deprecated events:
  * `System/Route/routing_replace` (replaced with `System/Request/routing_replace`)
  * `System/Index/block_render` (replaced with `System/App/block_render`)
  * `System/Index/construct` (replaced with `System/App/construct`)
  * `System/Index/load/before` (replaced with `System/App/render/before`)
  * `System/Index/load/after` (replaced with `System/App/render/after`)
  * `System/Page/display/before` (replaced with `System/Page/render/before`)
  * `System/Page/display/after` (replaced with `System/Page/render/after`)
* `cs\Index` class deprecated, use `cs\App` instead
* `cs\User::__finish()` method deprecated and does nothing
* `cs\Page::__finish()` method deprecated, use `::render()` instead
* OAuth2: Non-standard guest tokens deprecated in OAuth2 module
* Bots deprecated and will be removed in 4.x

Possible partial compatibility breaking (very unlikely, but still possible):
* Public methods used internally become protected:
  * `cs\Language::init()`
  * `cs\Page::init()`
* Partially breaking change: translation files renamed to latin names

Latest builds on [SourceForge downloads page](/docs/installation/Download-installation-packages.md) ([details about installation process](/docs/installation/Installation.md)) or download source code and [build it yourself](/docs/installation/Installer-builder.md)

# 4.78.5+build-2225: HTTP/2 Server Push, PostgreSQL support, docs with sources

First of all, project was renamed from CleverStyle CMS to CleverStyle Framework, which better describes this project.

This release introduces initial CLI support with few methods and components supporting it, more methods in system core and components expected in land future releases.

PostgreSQL in new database engine supported in system core out of the box in addition to already supported MySQLi and SQLite.

HTTP/2 Server Push and preload support added on system level, which alongside with another new feature `Frontend loading optimization` greatly improves first paint, especially on mobile devices with slow network.

A lot of performance tweaks were made both on backend (especially for Http server) and frontend (especially smaller Polymer build, less JS libraries loaded unconditionally).

Major refactoring happened to Blogs module, Comments and Disqus modules were hugely refactored and their integration now works very differently than it was before.

Documentation was moved to the same repository as source code, this makes updates easier and allows documentation versioning.

This is the first release of 4.x series, so it brings some BC-breaks, deprecations that will be removed in next 4.x release as well as all functionality declared as deprecated in any previous release.

PHP 5.6+ is now required (PHP 7 recommended) or HHVM 3.14+

WARNING: update to this version WILL remove `config/main.php`, `.htaccess`, `favicon.ico` and `.gitignore` files on update, make sure to have backup and restore them manually right after update.

Security fixes:
* None

New components:
* None

New features:
* Added support for returning value from controllers: value that is different than `null` will be passed to `cs\Page::json()` for API calls and to `::content()` for other calls
* `cs\CRUD` trait now will cast numbers to, basically, numbers (instead of leaving them as strings, which is what we get from database)
* Support for CLI interface added:
  * arguments parsing
  * routing
  * output
  * exit codes (400..510, all modulo 256)
  * `wp-cli/php-cli-tools` bundled with system as primary CLI output tool
  * support for `./cli cli:*` calls for printing available paths and methods
  * support for `help:` command on System module and few CLI paths/methods
* Register all events from the beginning unconditionally (this will cause some performance overhead during regular operating, but will increase performance under Http server)
* Added transactions support using new method `cs\DB\_Abstract::transaction()`
* `cs\CRUD::create()` and `::update()` now supports arguments to be passed directly instead of being passed as single argument in form of an array
* Convenient methods `cs\Cache::prefix()` and `cs\Language::prefix()` that are just wrappers around corresponding classes
* Homegrown PHPT Tests runner added with support for modified PHPT tests format, colored output and overall much simpler
* Preload support (via `Link: rel=preload` header) added, current includes to preload elements, this is typically used for automatic HTTP2 Server Push
* Added support for classes aliases: if package `Bar` provides functionality `Foo`, then class `cs\modules\Bar\Bar` is also available as `cs\modules\Foo\Bar`
* `cs\Installer` class created, used in modern installer
* Support for `cs\custom` classes for controller-based routing
* New backend events:
  * System/App/execute_router/before
  * System/App/execute_router/after
  * `` (replaced with ``)
* `cs.ui.ready` property with promise added, allows to conveniently run stuff when Web Components are ready
* PostgreSQL support added to system core
* Documentation added to `docs` subdirectory and will now be updated more actively as code changes within the same repository
* CleverStyle CMS > CleverStyle Framework
* Alternative `cs-unresolved` method supported for dealing with FOUC
* New feature `Frontend loading optimization`, it should help reaching faster initial page loads

Updates:
* Composer updated to latest stable version
* New upstream version of Font Awesome
* New upstream version of TinyMCE (with more patches)
* New upstream version of WebComponents.js
* Fresh git build of jQuery 3.x (very likely to be the final 3.0.0)
* New upstream version of Promise polyfill
* New upstream version of PHPMailer
* New upstream version of jsSHA
* New upstream version of UPF
* Alameda replaces original RequireJS as smaller, RequireJS-compatible alternative that works in IE10+ which is absolutely fine for us
* Imported changes from upstream normalized.css, moved IE10-specific hacks to Old IE plugin, Android 4.x-specific normalizations removed
* Even smaller Polymer build based on 1.5.0 with more Shady DOM-specific stuff removed, processed with Google Closure Compiler, source branch: https://github.com/nazar-pc/polymer/tree/no-shady-dom
* New upstream version of BananaHTML
* New upstream version of Prism

Fixes and small improvements:
* System:
  * Fix for `cs\Storage\Local::source_by_url()` method
  * `move_uploaded_file()` changed to `copy()`, since files might be abstracted from default PHP representation and therefore it will not be possible to move them
  * TinyMCE: Fixed TinyMCE integration (files uploading) and corresponding readme section in Uploader module
  * Build demo image with PHP 5.6
  * Even more convenient way to get arguments in `cs\Request::data()` and `::query()` methods, `::route()`, `::route_path()` and `::route_ids()` methods added
  * Forbid administrator to access contacts of other users
  * Some normalization of profile-specific APIs
  * Return `null` instead of `false` in some places, `null` is better compatible with some cases
  * System doesn't use superglobals directly except when intentionally need them
  * Fix for multipart body parsing and small refactoring
  * Added support for AJAX responses with specific status code using callbacks like `success_201` for convenience
  * Registration API returns status code 201 or 202 instead of string to indicate whether registration confirmation is needed or not
  * Annotated properties that are available through magic methods
  * Small tweak in order to avoid uncontrolled growing of memory consumption
  * Fix for showing error when system config not found (taking into account SAPI)
  * Tiny fix for `cs-select` element
  * Fix for TinyMCE integration, changes of `value` property on `cs-editor*` elements was not notified to the outside
  * Small fix for `selected` property change on `cs-select` element
  * Migrating to more convenient database interfaces
  * Fixes for `cs\CRUD_helper` trait when working with joined tables
  * Allow specify certain language for joined tables instead of using current one in `cs\CRUD_helpers` trait
  * Events caching that might potentially boost performance under Http server
  * Major fix for getting available request methods for controller routing
  * Removed unnecessary item from `index.json`
  * Very old unused file dropped
  * Show system's help message when executing `./cli` without arguments
  * CLI help for optimization commands, small simplification
  * Readme updated to better describe project purpose and features
  * Small fix for situation when error happens during `\cs\Request` initialization and `\cs\Response` was not initialized yet
  * Small fixes for SQLite database engine
  * Added test for installation with SQLite database
  * Added tests for SQLite database engine
  * Cache also failed autoloader results to decrease overhead caused by additional autoloaders (like Composer)
  * `cs\Permissions` class simplification using `cs\CRUD_helpers` trait
  * Casting `id` value in `cs\CRUD_helpers::search()`
  * Run all tests with both MySQLi and SQLite database engines separately (excluding database-specific tests when needed)
  * Added tests for nested transactions
  * Simplifications using modern features of `cs\CRUD` trait
  * Added checks in `cs\CRUD::update()` and `::delete()` to ensure item actually exists before doing something on it
  * Disallow search engines to scan files of modules, plugins and themes as well as uploaded files
  * Since now cache will not be disabled in debug mode (BlackHole engine exists for this purpose)
  * BlackHole cache engine tests added
  * Use simplified cache and language interface in system core
  * Few traits decoupled from `cs\App\Router`
  * Part of `cs\Page\Includes` decoupled into separate trait
  * It is not likely that modules, plugins, themes or even any other files will be located somewhere other that default paths and dropping absolute path of root will give incorrect relative path; which means that we can simplify certain things
  * Small tweaks and fixes for incorrect translation prefixes used which caused errors on submission of some forms and prevented showing success message
  * Changed structure of naming of cached includes to such that will always have common prefix
  * We can do even better - store not hashes, but actual map of includes with hashes
  * Be ready that something might modify cached includes in `System/Page/rebuild_cache` event
  * 2 more traits decoupled from `cs\Page\Includes`
  * Clean public cache when applying or saving any changes of optimization settings (vulcanization option change didn't trigger public cache cleaning, so lets do it always)
  * `cs\User\Data` trait split into 2 traits
  * `cs\modules\System\Packages_dependencies` class added (decoupled from `cs\modules\System\Packages_manipulation`)
  * Use implicit flush for efficient output of large content
  * Side effects removed from `cs\Core`
  * Refactored and centralized cache tests
  * Fix for FileSystem cache engine returned integer instead of boolean on set
  * Extended cache test suite with testing get with callback and namespaced keys
  * Fix for correction of relative paths in CSS when path contains whitespace
  * Use predefined array instead of switch/case for determining type by extension
  * Elimination of tab symbol usage in header comments
  * CoffeeScript > LiveScript
  * Make modal opening smooth
  * Improved builder UX
  * Refactoring of `cs\Singleton\Base`
  * Always show tooltips
  * Return integer instead of string with digits from `cs\CRUD_helpers::search()` when `total_count` is specified
  * Return numeric user id from API
  * Fix for handling components dependencies and functionalities when processing includes
  * Added support for `$_SERVER['REQUEST_SCHEME']`
  * Simplified `get_timezones_list()`, there is no big reason to cache timezones list
  * `-m` and `--mode` options added to CLI mode of installation
  * CLI and Web installation methods separated completely and both use `cs\Installer` to make actual system installation
  * Simplifications in pagination rendering
  * Translations fixes
  * Fix JS minifier when dealing with `</script>` inside of JS code and with vulcanization enabled
  * Fix for recursive dependencies includes priority was not preserved
  * Unified handling of `WebComponentsReady` event by all CleverStyle Widgets elements
  * Small fix for filling `cs\Config::$mirrors` property
  * Using `cs\CRUD` trait in `cs\Config` and some additional simplifications
  * Simplify includes handling by avoiding unnecessary replacing characters back and forth
  * Fix for error when numeric `$database` argument passed to `cs\Text` class methods
  * Set `X-UA-Compatible` as header
  * Multiple small adjustments to follow SQL standards closely and improve compatibility with other RDBMSs
  * Corrected DB primary keys set during installation for MySQLi and SQLite engines
  * Do not use `LIMIT` on `DELETE` in SQL queries
  * Unlock SQLite to affect total build success
  * Fix for supported languages in `meta.json` files
  * Module disabling operation extracted into separate method to allow its execution during module upgrade (otherwise it is not possible to upgrade current default module)
  * Do not cache events files paths, under Http server events handlers themselves will be cached
  * Do not remove directories during upgrade when ether files or directories are present, not just files
  * Clear internal caches of PHP during upgrade to avoid unexpected errors which should not happen normally
  * Add XML and mbstring extensions to the list of required
  * Handle the case when nothing was selected in Web UI of builder
  * Added basic tests for `cs\CRUD` trait
  * Fix for multi query in MySQLi DB engine
  * Fix for incorrect query when joined table doesn't contain any records in `cs\CRUD` trait
  * Fix for `null` argument during `cs\CRUD::create()` call caused error
  * Fix for potential infinite loop in `cs\CRUD::read_joined_table()`
  * Docs update
  * Small formatting tweaks to SQL scripts used during installation
  * SQLite version requirement increased to 3.7.11+ due to the need for multiple inserts support
  * `readme.html` removed
  * Project's own logo landed
  * `readme.md` restructuring
  * Web installer fixes and UI improvements
  * Small changes in about server administration page
  * Favicons removed from themes
  * 3 times smaller guest icon (it was tiny already, but why not?:))
  * Updated logo image, removed image with docker pulls count
  * Tenebris theme removed from readme, since it is not available in repository anymore
  * Slightly cleaned root directory and modified installer
  * Advanced CRUD tests added
  * Small fix in `cs\CRUD` trait
  * Fix for `text_md5` column was not set in `cs\Text` class
  * Never embed or preload fonts other than woff (woff2 will be used in future when browsers support will be better) because they are quite big, typically not critical and preloading new fonts types together is a waste of bandwidth
  * Fix for unresolved handling when JS inserted into `<head>`
  * Themes now use `cs-unresolved` attribute instead of `unresolved`, since it works better with `Frontend loading optimization`, which is enabled by default
  * Tweak default computed bindings for Polymer, so that `'0'` will be treated as `false`
  * 2-pass, more efficient HTML minification
  * Dropped CSS CSP-compatibility, since it wasn't complete anyway because Polymer only supports `style[is=custom-style]`, but no `link`-based counterpart
  * Fix for registered user getting system default language instead of automatically detected if not specified something explicitly
  * Fix in `cs-select` when currently selected value is falsy
  * Simplifications in `select[is=cs-select]` usage, some operations are simply redundant
  * Clean public cache on theme update
  * Store not only the keys, but also default values of `cs\Core::$core`
  * Normalize core installer's package internals with internals of components counterparts, so that system updating doesn't override certain files it shouldn't override
  * Set expiration headers for uploaded files
  * Fix for HTTPS detection
  * Add all CSS to frontend load optimized server push
  * Raise HHVM requirement to 3.14+ (which is not release yet); version might be lowered if required fixes backported to earlier releases
  * No errors suppressing when working with DB
  * html5sortable plugin moved to AMD modules, since it is only used in few cases
  * Small improvements in storing cached includes paths - now they are all prefixed with `/`
  * App shell for CleverStyle theme
  * Improved caching expiration for files outside of `storage/pcache`
  * Fix for closing modal by pressing Esc
  * Fix for returning `0` from API results, `null` being returned
  * Better handle concurrent deletion in FileSystem cache engine
  * Fix for configuration loading that caused performance issues
  * `cs\Config::cancel()` doesn't return any value anymore, just throws an exception if something goes wrong
  * https://github.com/Polymer/polymer/pull/3678 applied on top of minified Polymer to fix issue with `Polymer.cs.behaviors.value` behavior
  * Tiny UI performance tweak
  * Small tweak for `cs.Language`
  * Hack for HHVM compatibility until https://github.com/facebook/hhvm/issues/7087 is resolved
  * Few hacks removed since patches to HHVM bugs were upstreamed and we already require version that will include those fixes
  * Reduced complexity of `cs-notify`
  * Also transition duration in `cs-notify` is now determined imperatively instead of relying on `transitionend` event which had random bugs from time to time
  * Do not allow to upgrade system and components to the same version that already installed
  * Typo fix and small update to root `.gitignore`
  * Use modern ppa for PHP in Docker image instead of deprecated one
  * Do not preload stuff from CSS that have `?` in URL
  * Call includes addition before `Page::$Head` generation so that preload links might be generated in usual way
  * Added support for `favicon.png` in root directory with higher priority in addition to `favicon.ico`
  * Use transactions in `cs\CRUD` trait
  * Fix for enabling/update messages during dependencies check
  * Fix for CleverStyle Widgets, some widgets were able to override `[hidden]` attribute effect with own styles
  * To make things a bit nicer let's make headers names starting with capital letter
  * Added temporary workaround for Chromium that doesn't understand that preloaded JS file is utf-8 without BOM
  * `api/System/profile` is now allowed to be called by guest
  * Do not return `simple_admin_mode` in each admin API response, but rather use `api/System/admin/system`
  * Lowercase headers names for convenience in `cs\Request::header()` method
  * Fix for tricky bug in autoloader that caused caching `false` for classes, which are actually present
  * Allow overriding/extension of built-in system style modules
  * Fix error happened inside of jQuery, caused by jQuery being included after WebComponents.js polyfill in Firefox
  * Show notification during updating components that are not installed/used, but have the same version as such to which they are supposed to be updated
  * Small fix for `cs-notify`
* Blogs:
  * Blogs module switched to controller-based routing, no other changes made
  * Move Blogs API into controller
  * Posts addition/editing/deletion on frontend through API
  * Migration to LiveScript
  * Small UI fixes
  * Render preview as native post element, no logic duplication
  * Added public method for converting post array into JSON-LD structure
  * Do not force setting close tab handler during preview
  * Joined CRUD tables used to simplify posts manipulation
  * Simplification using CRUD trait
  * Simplify searches with `cs\CRUD_helpers` trait
  * All administration settings are now on frontend through API
  * Get settings through API instead of setting them as attributes on elements
  * `cs\modules\Blogs\Sections::get_structure()` usage was eliminated and method was removed
  * Fix for posts list rendering in Firefox
  * Comments count is not returned anymore by Blogs module API
  * Fix for post addition when there are no custom sections
* Comments:
  * Rewriting how comments work (simpler interfaces, utilizing new features of system core, events format changed)
  * Simplifying Comments class using `cs\CRUD_helpers` trait and new features from system core
  * Comment's API moved from files to Controller
  * Comments rendering/posting/editing/deletion on frontend through API using Web Components
  * `cs-comments` element is now used directly to insert comments block on frontend
  * `cs-comments-count` element for unified displaying of comments number
  * Using comments settings from API instead of though attributes in Blogs module
  * Added possibility for other modules to fire event that will cause removal of comments associated with some item
  * Small fix for Comments module, where cursor didn't show that reply is available, but click still worked
  * Improve username and avatar getting
* Composer:
  * Added CLI interface in Composer module
  * New event `Composer/update_progress` added
  * Do not use superglobals in Composer module
  * Composer modules switched to Controller-based routing
  * `nazar-pc/stream-slicer` added to Composer packages already included in system core
* Content:
  * Tiny fix in Content module, it wasn't prepared to optimized frontend loading
  * Tiny fix in Content module, it wasn't prepared for being disabled
* Composer assets:
  * Composer assets fails nicely if `fxp/composer-asset-plugin` package not installed
  * Optimize dependencies structure and assets packages naming to avoid potential conflicts
* DarkEnergy:
  * Remove big logo from theme - nothing to do there
  * Simpler CSS layout in DarkEnergy theme
  * Basic app shell for DarkEnergy theme
  * Use Google Fonts API for loading Open Sans font, it will be much more efficient than doing it in the way it is now, and making it efficiently ourselves it pretty complex topic
  * Do not use custom fonts in DarkEnergy theme when user agent advertises data saving
* Disqus:
  * Include Disqus's module JS only on those pages where needed
  * Script migrated to LiveScript
* Feedback:
  * Small fix for Feedback module, including CSS directly is not needed for a long time now
* Http server:
  * Remove from CLI help optional argument which is not supported for some time already
  * Small refactoring, avoid creating objects all the time
  * Log generic errors in Http server module
* Photo gallery:
  * Fix for Photo gallery not working, since jQuery ready event is not fired after Web Components are ready anymore
  * Tiny simplification in Photo gallery module
* Prism:
  * Prism integration fixes - wait for Web Components to initialize before initializing itself
  * Languages now loaded though autoloader plugin
* Shop:
  * Stop using already removed `hide.uk.modal` event
* TinyMCE:
  * Fix for duplicated `change` event capturing on each real change
  * Fix for editor when it is initialized before all Web Components are ready (for some reason, which is very difficult to debug, change events are not fired by TinyMCE in this case)
  * Use empty string instead of `undefined` to prevent error within TinyMCE
  * TinyMCE silently moved basic Shadow DOM events support under experimental flag
  * Fix for resize handle under Shadow DOM added to TinyMCE
  * Small tweak to TinyMCE (do not show textarea inside of editor element)
  * TinyMCE plugin reworked so that huge TinyMCE library is only loaded when instance of editor is used
  * Fix for files uploading integration with TinyMCE
  * Add small delay to not update content too often in TinyMCE which causes performance issues, especially during quick typing
  * Small fix on top of minified TinyMCE in order to allow links insertion when having inline editor (though, link insertion position is not correct yet:()
* Uploader:
  * Do not use superglobals in Uploader module

Deprecations (will be dropped right after release):
* Bunch of database-related methods in `cs\DB\_Abstract` deprecated as not very useful:
  * `::aq()`
  * `::n()`
  * `::fa()`
  * `::fs()`
  * `::fas()`
* `cs\DB\_Abstract::q*()` methods don't accept usual boolean arguments, but accepts array of arguments as `::q()` method

Possible partial compatibility breaking (very unlikely, but still possible):
* `$condition` argument removed from `cs\Permissions::get()` since it was not actually used and just complicates code
* Dropped undocumented `indexed` key from `cs\CRUD` trait as never used
* Partially breaking change, `cs\Page\Includes_processing::htmL()` method accepts slightly different arguments
* jQuery is only loaded after all main JS and HTML files are loaded (and components that are loaded globally should be aware of this)
* Stop supporting custom charsets for databases, always use proper UTF8 depending on database engine

Dropped backward compatibility:
* All deprecated functionality was dropped
* Dropped update support from System versions older than previous release, the same for all components
* HTTP storage engine removed since it was not ever used and thus not tested, better not offer it at all
* `POST api/System/user/*` endpoints moved to restful `* api/System/profile`
* Dropped unused frontend functions:
  * `cs.xor_string()`
  * `cs.prepare_attr_value()`
* Dropped support for `=>` in dependencies (use regular `>=` instead)
* `config/main.php` MUST be present
* `DEBUG` constant MUST be defined in `config/main.php`
* `DOMAIN` constant removed (use `Core::$domain` instead)
* Drop support for `prepare.php` in modules and themes as redundant
* Remove global properties on frontend:
  * `cs.base_url`
  * `cs.current_base_url`
  * `cs.module`
  * `cs.route`
  * `cs.route_path`
  * `cs.route_ids`
  * `cs.in_admin`
  * `cs.is_user`
  * `cs.is_guest`
  * `cs.public_key`
  * `cs.password_min_length`
  * `cs.password_min_strength`
  * `cs.is_admin`
  * `cs.debug`
  * `cs.simple_admin_mode`
* Behavior `cs.Polymer.behaviors.cs` removed from system
* Stop applying `shim-shadowdom` attribute to all styles automatically

Latest builds on [downloads page](/docs/installation/Download-installation-packages.md) ([details about installation process](/docs/installation/Installation.md)) or download source code and [build it yourself](/docs/installation/Installer-builder.md)

# 4.85.0+build-2240: Less jQuery, less plugins

This release contains incremental improvements and bug fixes over last major release with 2 important changes.

The first significant change is that jQuery is not used by system core and many components, it is still around, but will be removed in 5.x, so it is time to explicitly define dependency on jQuery NPM package and use it as AMD module exclusively.

The seconds change is deprecation of plugins in favor of using modules only. Plugins were always simpler version of modules and didn't bring any substantial benefit, while added a lot of complexity.
While migrating from plugins to modules you might want to disable simple administration mode temporarily (Administration > General > System :: Simple mode of administration) and ignore dependency conflicts while you disable/remove plugin and install/enable corresponding module.

Security fixes:
* None

New components:
* None

New features:
* System doesn't use jQuery anymore
* `cs.api` function added on frontend for convenient calls to API without dependency on jQuery
* `cs.ui.alert()` now returns promise
* `cs.ui.confirm()` also returns promise if `ok_callback` was not specified
* Standalone version of html5sortable added alongside with previously available
* All existing plugins converted into modules, since plugins are deprecated

Updates:
* Stable version of jQuery 3.0

Fixes and small improvements:
* System:
  * Added check for `origin` header
  * Small fix for getting permission for API
  * Fixed focus in sign in and password restoration modals
  * Fix for password changing on frontend
  * Fix for block deletion
  * Set `selected` property on `cs-select` during initialization from `value` property
  * Small fix for labels, when `active` property is set before element is attached to DOM
  * Fix for incorrect error message during failed API request on frontend
  * Support trailing slashes in administration pages
  * Return numeric user's groups ids
  * Fix jQuery version in packages, since `*` causes problems
* Blockchain payment
  * Small important fix for Blockchain payment module
  * Use NPM package with jQuery-independent QR code generator
* Blogs:
  * Stop using jQuery in Blogs module
* Comments:
  * Stop using jQuery in Comments module
* Composer:
  * Stop using jQuery in Composer module
* Content:
  * Stop using jQuery in Content module
* Disqus:
  * Disqus conflict with Shadow DOM polyfill was resolved, so hack is not needed anymore
* Feedback:
* Fotorama
  * Added AMD support to fotorama
  * Fotorama plugin now depends on `Composer assets` and NPM package jQuery
* Old IE
  * Dataset polyfill added to Old IE plugin
* Photo gallery:
  * Photo gallery consumes jQuery only as AMD module, declares dependency on corresponding NPM package and `Composer assets` plugin
* Shop:
  * Shop consumes jQuery only as AMD module, declares dependency on corresponding NPM package and `Composer assets` plugin
  * Shop module uses jQuery-free html5sortable from latest system version
* Static pages
  * Stop using jQuery in Static pages module
* TinyMCE:
  * Specify `display:` on editor elements
* Uploader:
  * Stop using jQuery in Uploader module (but still support jQuery objects if passed as argument)

Deprecations:
* jQuery is now deprecated in system core, but is still around till 5.x
* Plugins are deprecated, but still fully supported and will be removed in 5.x

Possible partial compatibility breaking (very unlikely, but still possible):
* Partially breaking change: second argument in `cs.file_upload()`'s `error` callback is now regular XHR object, rather than jqXHR

Dropped backward compatibility:
* All features that were deprecated in last release and kept for smooth upgrade were removed
* Removed possibility to upgrade from older versions than latest release

Latest builds on [downloads page](/docs/installation/Download-installation-packages.md) ([details about installation process](/docs/installation/Installation.md)) or download source code and [build it yourself](/docs/installation/Installer-builder.md)

# 4.94.6+build-2277: Better testing

This release is primarily focused on improving code quality of system core and testing.
Code coverage analysis was added into testing process, it helps to figure out which areas require more attention than others.

There are small fixes and improvements, but nothing major. However, there are some deprecations that will be removed in future 5.x release.

This release is likely to be the last one from 4.x series.

Security fixes:
* None

New components:
* None

New features:
* Code coverage analysis added to tests
* Add support for `/index.php`-prefixed requests
* Add support setting event handlers using scripts in `custom` directory
* Quick tests added that can be executed autonomously and concurrently

Updates:
* New upstream version of UPF
* New upstream version of PHPT-Tests-Runner
* New upstream version of Composer
* New upstream version of Polymer (Native custom CSS properties are not used yet because of `Polymer.updateStyles()` is not compatible with it yet)

Fixes and small improvements:
* System:
  * Change website protocol to https
  * Added support for multiple return values from methods when mocking objects in tests
  * Bind `$this` and `static` in mocked object's closures
  * Make autoloader ready for `CACHE` being not defined
  * Make `cli` file executable on system installation
  * Small tweaks for builder tests
  * Added badge with tests coverage
  * Small tweaks to autoloader and installer
  * Tiny refactoring in `cs\Event` class
  * Small fixes in `cs\CRUD` trait
  * Small fixes in `cs\Key` class
  * Fix for `false` return value was not working correctly in `cs\Event::once()`
  * Fixes for password restoration
  * Fix for closed site sign in
  * Allow overriding system core files constants upfront
  * Fixes for groups permissions inheritance and consistency of getting/setting user's groups
  * NOTE: User's groups priority has changed order in administration: now each next group have higher priority than previous
  * Control memory cache for user's permissions the same way as user's data
  * Fixes for user's permissions inheritance
  * Fix for infinite loop in `cs\Language` class
  * Improved sessions deletion
  * Small fixes and simplifications in `cs\User\Profile` trait
* Blockchain payment
  * Change blockchain payment functionality from `payment` to `payment/blockchain`, that will allow installation of multiple payment systems at the same time
* Blogs:
  * Small fix, Blogs module didn't use proper namespace for `Json ld` module
  * UI fix for editable area during posts addition or editing
  * Add language prefix to post edit URL so that it wouldn't confuse users in multilingual setup
* Composer assets:
  * Increase `fxp/composer-asset-plugin` dependency version

Deprecations:
* `ENGINES` constant is deprecated now
* User's contacts deprecated in system core
* Templates for blocks are deprecated now

Possible partial compatibility breaking (very unlikely, but still possible):
* None

Dropped backward compatibility:
* None

Latest builds on [downloads page](/docs/installation/Download-installation-packages.md) ([details about installation process](/docs/installation/Installation.md)) or download source code and [build it yourself](/docs/installation/Installer-builder.md)

# 4.95.0+build-2279: Forward-compatible version

Version for smooth upgrade to 5.x

# 5.15.3+build-2332: Focus on quality

This release is primarily focused on code quality with significantly increased tests coverage.

Another aspect is rectifying framework's API by removing redundant elements (like plugins, blocks templates, users contacts, some methods and properties), blocks and modules now moved to project root similarly to themes.

The last noteworthy aspect is slightly improved minimal request overhead, which improves page rendering under mod_php or php-fpm as well as under Http server (which can render pages in almost 0.5 ms).

This is the first release of 5.x series, so it brings some BC-breaks, as well as removed all functionality declared as deprecated in any previous release.

Security fixes:
* None

New components:
* Psr7 (decoupled into optional module from system core)

New features:
* Added serving static files in framework itself (without external web-server)
* Backend events added:
  * `System/Request/routing_replace/before`
  * `System/Request/routing_replace/after`

Updates:
* Normalize is now based on normalize.css 4.2.0
* New upstream version of PHPT-Tests-Runner
* New upstream version of autosize
* New upstream version of Composer

Fixes and small improvements:
* System:
  * Use `element.matches()` instead of `element.tagName`
  * Fixes for session data setting and deletion
  * Fix for making API requests for non-authorized user (essentially, initial sign in)
  * Updated Nginx config sample without compression headers
  * Small fix for streams in multiple files uploads as streams
  * Do not handle and request data and/or files for `GET`, `HEAD` and `OPTIONS` requests
  * Keep correct independent position in uploaded files that are opened multiple times
  * Docker image updated to newer base `phusion/baseimage` as well as using PHP7
  * Multiple DB query always returns boolean result
  * Print error messages to stderr in CLI mode
  * Fixes and small improvements for CSS minifier
  * Small fixes and improvements for processing nested HTML imports
  * Correct support for `Forwarded` HTTP header per spec
  * Improved HHVM support in some edge cases with errors
  * Fix for making multiple API calls on frontend: request body was sent where it shouldn't
  * Small fix for headers initialization in `cs\Response::init_with_typical_default_settings()` method
  * Small fix for `REQUEST_URI` decoding in `cs\Request\Server` trait
  * Stop using ETags for improved caching
  * Stop setting the same headers multiple times
  * Multiple `.htaccess` files are added to installation distributive instead of creating them in `core/bootstrap.php`
  * Fix permission for `cli`
  * More lightweight Language initialization, delays loading of translations until really needed
  * Website name is now added to `Page::instance()->Title` only right before page rendering (affects only manual changing of this property), which allows to avoid loading translations completely for certain requests
  * Only set `Content-Language` header for multilingual setups
  * Do not render title when interface turned off
  * Many tweaks and small fixes
* Deferred tasks
  * Hide deferred tasks from menu
* Http server
  * Fix for serving streams in Http server module
* HybridAuth
  * Contacts integration removed from HybridAuth module
  * Small fix in HybridAuth module when no providers enabled

Deprecations (will be dropped right after release):
* None (major release)

Possible partial compatibility breaking (very unlikely, but still possible):
* Minor potential BC-break (very unlikely), `cs\Text::get()` signature has changed and implementation largely simplified
* Request initialization should not happen after Response initialization, since exit might happen during Request initialization and there is a need for response to be ready by that time

Dropped backward compatibility:
* All deprecated functionality was dropped
* Dropped update support from System versions older than previous release, the same for all components
* jQuery was removed from system (including jQuery configuration)
* Plugins support removed with all corresponding backend and frontend events, UI elements, API and documentation.
* Removed backend events:
  * `System/App/construct`
  * `admin/System/components/plugins/enable/before`
  * `admin/System/components/plugins/enable/after`
  * `admin/System/components/plugins/disable/before`
  * `admin/System/components/plugins/disable/after`
  * `admin/System/components/plugins/update/before`
  * `admin/System/components/plugins/update/after`
  * `System/User/get_contacts`
  * `System/Request/routing_replace`
* Removed frontend events:
  * `admin/System/components/plugins/disable/before`
  * `admin/System/components/plugins/disable/after`
  * `admin/System/components/plugins/enable/before`
  * `admin/System/components/plugins/enable/after`
  * `admin/System/components/plugins/update/before`
  * `admin/System/components/plugins/update/after`
* `ENGINES` constant removed
* Contacts support removed from system
* Blocks templates removed together with corresponding configuration, UI elements, constants, files and documentation
* Major backward-compatibility break: blocks and modules moved to the root of the project (system tries to make sure process goes smooth by correcting `fs.json` files and creating temporary symlinks in original locations).
* Also events with `components/` in their name have lost this suffix, for instance, `admin/System/components/modules/default` become `admin/System/modules/default`
* Do not compress minified files i public cache, this should be better done by web/proxy server (also resolves problems with setting headers on some configurations)
* `cs\Text::search()` method removed as unused
* Dropped support for calling `DB::instance()->$db_index`, `DB::instance()->$db_index()`, `Storage::instance()->$storage_index`
* Dropped support for calling database methods directly on `DB::instance()` objects, now explicit getting database with `DB::instance()->db()` or `DB::instance()->db_prime()` is required
* Psr7 support moved from system core to new Psr7 module
* `cs\Response::$protocol` property removed as unused
* `clanguage_en` removed from translations and events as redundant
* `cs\Page::set_theme()` function removed

Latest builds on [downloads page](/docs/installation/Download-installation-packages.md) ([details about installation process](/docs/installation/Installation.md)) or download source code and [build it yourself](/docs/installation/Installer-builder.md)

# 5.21.2+build-2360: Better consistency

Small incremental release on top of last major update.
Release improves consistency in builder/installer and some interfaces, have significantly code coverage (>90% for core system classes).

Nothing major this time.

Security fixes:
* None

New components:
* None

New features:
* Allow any host in Docker demo

Updates:
* New upstream version of phpt-tests-runner
* New upstream Promise polyfill
* New upstream version of autosize
* New version of TinyMCE

Fixes and small improvements:
* System:
  * Add `.htaccess` files in `storage` directory to core files in builder, so that they'll be updated with system update
  * Remove mentioning `Storage.php` from builder
  * Reload page on module enabling, disabling and uninstallation
  * Fix for potential redirect use wih specially created subdomain
  * Fix for inheriting translations from core language, that was broken in 1183976
  * Installer is now covered by code coverage analysis (not 100%, but mostly)
  * Add nightly PHP to Travis CI, but allow it to fail
  * Use native code coverage merging
  * `PHP_SAPI` constant usage changed to `php_sapi_name()` function usage, which is easier to wrap in tests
  * Smaller code coverage overhead (bug bigger data file)
  * Lighter integration of code coverage into installer
  * Fix for instantiating classes from `cs\custom` namespace which do not extend original class
  * Fix for multiple class extension
  * Many tests added
  * Only compute code coverage on PHP 7.0
  * Tiny fix for bug found under PHP Nightly
  * Fix for Docker demo
  * Small fix for nav bar in administration
  * `icon` property on `cs-button` renamed to `icon-before`, `icon` property is still supported and will be, now it is just an alias to `icon-before`
  * Declare `public` visibility on methods explicitly
  * Clean upstream version of PHPMailer without any modifications is used
  * Tiny fix in mail settings in administration interface
  * Use smtp port in `cs\Mail` class directly as is
  * Small fix in `cs\Mail` class
  * Mark more tests as slow
  * Refactoring and simplifications in `cs\Storage` class
  * New constants:
    * `cs\Storage::CONNECTIONS_ACTIVE`
    * `cs\Storage::CONNECTIONS_SUCCESSFUL`
    * `cs\Storage::CONNECTIONS_FAILED`
  * Small refactorings and fixes

Deprecations (will be dropped right after release):
* `cs\Storage\_Abstract::move_uploaded_file()` method deprecated and is just an alias to `::copy()` now

Possible partial compatibility breaking (very unlikely, but still possible):
* `cs\modules\System\Packages_manipulation::move_uploaded_file_to_tmp()` method removed as unused, it remained from pre-API-based administration interface
* `cs\Mail` doesn't extend `PHPMailer` class anymore, but rather uses it internally
* `cs\DB::CONNECTIONS_ALL` constant renamed into `CONNECTIONS_MASTER`
* `cs\DB::CONNECTIONS_MIRRORS` constant renamed into `CONNECTIONS_MIRROR`
* `cs\DB::get_connections_list()` method now requires first argument to be specified explicitly
* `cs\Storage::get_connections_list()` method now requires first argument to be specified explicitly, also requires arguments to be `cs\Storage::CONNECTIONS_*` constants

Dropped backward compatibility:
* All features that were deprecated in last release and kept for smooth upgrade were removed
* Removed possibility to upgrade from older versions than latest release
* SimpleImage module removed, hacks in it were not actually used anywhere, so better use upstream version from Composer, it will not make any practical difference

Latest builds on [downloads page](/docs/installation/Download-installation-packages.md) ([details about installation process](/docs/installation/Installation.md)) or download source code and [build it yourself](/docs/installation/Installer-builder.md)
