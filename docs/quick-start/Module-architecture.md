Every described below element is optional, even empty directory in `components/modules` will be considered as module. You are free to use only features you need.

### File system structure of module
* admin
  * Controller.php
  * index.json
  * index.php
  * prepare.php
* api
  * Controller.php
  * index.json
  * index.php
  * index.{http_method_lowercase}.php
  * prepare.php
* includes
  * css
  * html
  * js
  * map.json
* meta
  * install_db
  * uninstall_db
  * update
  * update_db
* Controller.php
* fs.json
* index.html / index.php
* index.json
* license.html / license.txt
* meta.json
* readme.html / readme.txt
* events.php

#### admin/Controller.php
Controller with static methods that is used in controller-based routing.

#### admin/index.json
Describes module routing for administration pages. May have:
* one level

    ```json
    [
        "latest_posts",
        "section",
        "post",
        "tag",
        "new_post",
        "edit_post",
        "drafts"
    ]
    ```

* two levels

    ```json
    {
        "general"    : [
            "site_info",
            "system",
            "optimization",
            "appearance",
            "languages",
            "about_server"
        ],
        "components" : [
            "modules",
            "plugins",
            "blocks",
            "databases",
            "storages"
        ]
    }
    ```

* mixed

    ```json
    {
        "blank"      : [],
        "profile"    : [
            "info",
            "settings",
            "registration_confirmation",
            "restore_password_confirmation"
        ],
        "robots.txt" : []
    }
    ```

For example, if module name is *System* (real example), then urls will look as following:
* System/admin/general/site_info
* System/admin/general/system
* System/admin/components/plugins

If some part of URL is not specified, the first element of corresponding level will be taken. Next urls will open the same page:
* System/admin
* System/admin/general
* System/admin/general/site_info

Similarly:
* System/components
* System/components/modules

Route may be accessed and read/changed through `$Request->route`. This property contains array of route parts without module name and `admin|api` prefix.

If parts are specified, system will try to find corresponding files for each part.
For example, for route
> System/admin/general/site_info

system will try to find and include file `general.php` in the `admin` directory. Then system will try to find and include file `site_info.php` in the `admin/general`.

Both files are optional.

#### admin/index.php
Usually is used for simple administration page. It is included after `admin/prepare.php` (see below).

Before including of this file, system checks index.json file and corrects current route if it is not complete
> System/admin

will be changed into
> System/admin/general/site_info

(see second half of `admin/index.json` section above).

#### admin/prepare.php
First file of module, which will be called on any administration page. Usually is used to make some preparation procedures.

#### api/Controller.php
Completely the same as for `admin/Controller.php`, but for API.
The only difference if that methods also may have suffixes of http methods like `Controller::index_{http_method_lowercase}.php`.

#### api/index.json
Completely the same as for `admin/index.json`, but for API.
The only difference if that php files also may have suffixes of http methods like `api/index.{http_method_lowercase}.php`.

#### api/index.php
Usually is used for simple api. It is included after `api/prepare.php` (just like `admin/prepare.php`, see above).

#### api/index.{http_method_lowercase}.php
May be one or several files (like `api/index.get.php`, `api/index.post.php`, `api/index.put.php`, `api/index.delete.php`) that corresponds to request with different http methods. Methods can be standardized or custom, but always lowercase.
Every is included after `api/index.php` only for its http method.

#### api/prepare.php
First file of module, which will be called on any api request. Usually is used to make some preparation procedures. Similarly to `admin/prepare.php`.

#### includes/css includes/html includes/js
CSS/Web Components (Polymer elements)/JS files in these directories will be automatically included on necessary pages of website (including dependencies between components), and compressed (if was chosen in configuration)

#### includes/map.json
Not all CSS/HTML/JS files need to be included on all pages of website. This file allows to specify which files and where should be included.
This file affects compressed version of CSS/HTML/JS files, and naturally accounts components dependencies before inclusion on pages.

Example:
```json
{
    "admin/Blogs" : [
        "admin.css"
    ],
    "Blogs"       : [
        "general.css",
        "general.js",
        "my-component/index.html"
    ]
}
```

There is no need to mention `css`, `html` and `js` directories because files location is obvious from extension.

Please, note, that also there is no need to mention css and JS files in `html` directory that are used by Web Components (Polymer elements), since they will be included automatically.

Sometimes it might be necessary to include many files, so there is special wildcard syntax:
```json
{
    "Fotorama" : "*"
}
```
Example above will include all `css`, `html` and `js` files in their respective directories.

It is also possible to specify part of path:
```json
{
    "admin/Blogs" : [
        "admin.css"
    ],
    "Blogs"       : [
        "general.*",
        "cs-blogs-*"
    ]
}
```

And last trick here: `*` is not required, it is used purely for readability purpose.

#### meta/install_db
This directory contains subdirectories with files. Subdirectories are called the same as databases are identified in `db` parameter of `meta.json` file (see below). Files are called correspondingly to the names of database engines, supported by module with extension \*.sql, for example:
* meta/install_db/posts/MySQLi.sql

Files, that corresponds to configured database type, will be called at module installation. It is useful when module works with database, just put SQL with queries, separated by `;` that will initialize tables structure, needed for module operation. It is enough in most cases.

#### meta/uninstall_db
By analogy to `meta/install_db`, but is used to delete database structure of module at its uninstallation.

#### meta/update
Contains php files with names, that corresponds to versions of module. These files are executed during updating process.

Example of files structure:
* meta/update/1.0.2.php

#### meta/update_db
Contains sql files with names, that corresponds to versions of module. Queries from these files are executed during updating process after php files.

Example of files structure:
* meta/update_db/posts/1.0.2/MySQLi.sql

#### api/Controller.php
The same as `admin/Controller.php`.

#### fs.json
This file contains paths of all files of module. All paths are relative, relatively to the module directory. This file is used during module updating in order to make this process correct. File is created automatically during module building process.

#### index.json
The same as `admin/index.json`

#### index.php
Usually is used for simple pages. It is included after `prepare.php` (just like `admin/prepare.php`, see below).

#### license.html / license.txt
License file, may be of txt or html format.

#### meta.json
Main description file of module. A little bit extended in comparison with the same file for plugin. This file is required for module building, in order to be able to build module package. Example of meta.json file for module:
```json
{
	"package"             : "System",
	"category"            : "modules",
	"version"             : "0.171",
	"update_from_version" : "0.171",
	"description"         : "Base system module of CleverStyle CMS",
	"author"              : "Nazar Mokrynskyi",
	"website"             : "cleverstyle.org/cms",
	"license"             : "MIT License",
	"db"                  : [
		"keys",
		"users",
		"texts"
	],
	"db_support"          : [
		"MySQLi"
	],
	"provide"             : [
		"system"
	],
	"multilingual"        : [
		"interface",
		"content"
	],
	"languages"           : [
		"English",
		"Русский"
	],
	"hide_in_menu"        : 1
}
```

Some properties are not obvious:
* provide - allows to specify a set of features, provided by this module. If other plugin or module with such feature already installed - system will not allow to install another one in order to eliminate conflicts of functionality.
* multilingual - just hint, which level of multilingual capabilities is supported by module. Just interface translations, or event multilingual content support.

If this file exists it should have at least next properties:
* package - package name, should be the same as module directory name
* category - always *modules* for modules
* version - package version in order to distinguish different versions of the same module
* description - short module description in few words
* author - module author
* license - module license
* provide - what functionality module provides, might be an array
* hide_in_menu - usually if `index.php`, `index.html` or `index.json` is present - module will be shown in main menu - this option allows suppress such behavior

Other possible properties are:
* website
* db
* db_support
* storage
* storage_support
* multilingual
* languages
* require
* optional
* conflict

[Read about dependencies and conflicts](/docs/Components-dependencies-and-conflicts.md)

##### More about `db` property
Contains array with identifiers for databases. If some tables of module can be completely separated, it may be useful to use different identifiers. In such case if you have several configured databases in system (physically separated, or even with different engines, which is more reliable for some data), every identifier may be connected with any existing configured database.
Index of configured database may be obtained from global object `$Config`. Example for module *System*:
```php
<?php
$Config        = \cs\Config::instance();
$db        = \cs\DB::instance();
$users_db_id    = $Config->module('System')->db('users');
$result        = $db->$users_db_id->q(
    "SELECT `login`
    FROM `[prefix]users`"
);
```

#### More about `storage` property
Contains array with identifiers for storage, similarly to `db` property. This allows to use different storages for different data.

Index of configured storage may be obtained from system object `$Config`. Example for module *System*:
```php
<?php
$Config            = \cs\Config::instance();
$Storage        = \cs\Storage::instance();
$users_storage_id    = $Config->module('System')->storage('images');
$result            = $Storage->$users_storage_id->file_put_contents('test', 123);
```

#### readme.html / readme.txt
Readme file with extended description of module and some other additional information.

#### events.php
This file is included on every page, even when module is not installed or enabled. It is used mainly for [events subscribing](/docs/Events.md#wiki-subscribing), but also may be used for other purposes.
