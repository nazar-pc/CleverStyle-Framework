`$Config` - is system object, that responses for system configuration, instance can be obtained in such way:
```php
$Config = \cs\Config::instance();
```

This object allow to get configuration of any module/block, and also general system parameters, configured databases/storages and others.

### [Methods](#methods) [Properties](#properties) [Events](#events) [Constants](#constants) [\cs\Config\Module_Properties class](#module-properties-class)

<a name="methods" />
###[Up](#) Methods

`$Config` object has next public methods:
* base_url()
* core_url()
* apply()
* cancel()
* save()
* module()
* cancel_available()

#### base_url() : string
Method returns string with base URL of current mirror. For example:
* http://website.com
* https://website.com
* https://www.website.com

Usually, is used to generate full, non-relative URL to some page of site.

#### core_url() : string
Similar to base_url(), but returns URL of main mirror, not current.

#### apply() : bool
Is used for applying of system configuration without its saving. This is helpful sometimes, because in case, if changes broke your system - you can clean cache even manually, and restore site workability.

#### cancel()
Is used in pair with apply() to cancel applied changes.

#### save() : bool
Is used for saving changes in configuration.

#### module($module_name : string) : \cs\Config\Module_Properties
One of the most useful methods. Allows to store configuration of specified module. Method returns object instance of class `\cs\Config\Module_Properties`.

### bool cancel_available()
Whether configuration was applied (not saved) and can be canceled

<a name="properties" />
###[Up](#) Properties

`$Config` object has next public properties:

* core
* db
* storage
* components
* mirrors

#### core : array
Property with most general configuration properties. Example for newly installed system (JSON only for presentation, property returns regular php array):
```json
{
    "name"                              : "CleverStyle test",
    "url"                               : [
        "http://cs.test"
    ],
    "admin_email"                       : "admin@cscms.org",
    "closed_title"                      : "Site closed",
    "closed_text"                       : "<p>Site closed for maintenance<\/p>",
    "site_mode"                         : 1,
    "title_delimiter"                   : " | ",
    "title_reverse"                     : 0,
    "cache_compress_js_css"             : 0,
    "theme"                             : "CleverStyle",
    "language"                          : "English",
    "allow_change_language"             : "0",
    "multilingual"                      : 1,
    "db_balance"                        : "0",
    "db_mirror_mode"                    : "0",
    "active_languages"                  : [
        "English",
        "Russian",
        "Ukrainian"
    ],
    "cookie_domain"                     : [
        "cs.test"
    ],
    "inserts_limit"                     : 1000,
    "key_expire"                        : 120,
    "session_expire"                    : 2592000,
    "update_ratio"                      : 75,
    "sign_in_attempts_block_count"      : 0,
    "sign_in_attempts_block_time"       : 30,
    "cookie_prefix"                     : "",
    "timezone"                          : "Europe/Kiev",
    "password_min_length"               : 4,
    "password_min_strength"             : 0,
    "smtp"                              : "1",
    "smtp_host"                         : "",
    "smtp_port"                         : "465",
    "smtp_secure"                       : "ssl",
    "smtp_auth"                         : "1",
    "smtp_user"                         : "",
    "smtp_password"                     : "",
    "mail_from_name"                    : "Administrator of CleverStyle test",
    "allow_user_registration"           : 1,
    "require_registration_confirmation" : 1,
    "auto_sign_in_after_registration"   : 0,
    "registration_confirmation_time"    : 1,
    "mail_signature"                    : "",
    "mail_from"                         : "admin@cs.test",
    "rules"                             : "<p>Site rules<\/p>",
    "show_tooltips"                     : 1,
    "remember_user_ip"                  : 0,
    "simple_admin_mode"                 : 0,
    "default_module"                    : "System",
    "put_js_after_body"                 : 1,
    "vulcanization"                     : 1,
    "gravatar_support"                  : 0
}
```
Most of properties should be understandable from names, some properties are used by system only.

#### db : array
Property, that stores configuration of databases, except the main database, parameters of which are written in configuration file. Structure of this property is following (JSON only for presentation, property returns regular php array):
```json
{
	"0" : {
		"mirrors" : [
			{
				"mirror"   : "0",
				"host"     : "localhost",
				"type"     : "MySQL",
				"prefix"   : "prefix_",
				"name"     : "CleverStyle",
				"user"     : "CleverStyle",
				"password" : "1111",
				"mirrors"  : []
			}
		]
	},
	"2" : {
		"mirrors"  : [],
		"host"     : "localhost",
		"type"     : "MySQL",
		"prefix"   : "prefix_",
		"name"     : "CS3",
		"user"     : "CS3",
		"password" : "CS3",
		"mirror"   : "-1"
	},
	"3" : {
		"mirrors"  : [
			{
				"mirror"   : "3",
				"host"     : "localhost",
				"type"     : "MySQL",
				"prefix"   : "prefix_",
				"name"     : "CS2-mirror",
				"user"     : "CS2-mirror",
				"password" : "CS2-mirror",
				"mirrors"  : []
			},
			{
				"mirror"   : "3",
				"host"     : "localhost",
				"type"     : "MySQL",
				"prefix"   : "prefix_",
				"name"     : "CS2-mirror2",
				"user"     : "CS2-mirror2",
				"password" : "CS2-mirror2",
				"mirrors"  : []
			}
		],
		"host"     : "localhost",
		"type"     : "MySQL",
		"prefix"   : "prefix_",
		"name"     : "CS2",
		"user"     : "CS2",
		"password" : "CS2"
	}
}
```
#### storage : array
Property, that stores configuration of storages, except the main storage, parameters of which are written in configuration file. Structure of this property is following (JSON only for presentation, property returns regular php array):
```json
{
	"0" : "0",
	"1" : {
		"host"       : "cscms.org",
		"connection" : "HTTP",
		"user"       : "CleverStyle",
		"password"   : "CleverStyle",
		"url"        : "http:\/\/cscms.org"
	}
}
```
#### components : array[]
Internal structure of components parameters (JSON only for presentation, property returns regular php array):
```json
{
	"modules" : {
		"Blogs"        : {
			"active"  : 1,
			"db"      : {
				"posts"    : "0",
				"comments" : "0"
			},
			"storage" : [
			],
			"data"    : {
				"posts_per_page"  : "2",
				"max_sections"    : "3",
				"enable_comments" : "1"
			}
		},
		"Cron"         : {
			"active"  : 0,
			"db"      : [
			],
			"storage" : [
			]
		},
		"Static_pages" : {
			"active" : 1,
			"db"     : {
				"pages" : "0",
				"texts" : "0"
			}
		},
		"System"       : {
			"active" : 1,
			"db"     : {
				"keys"  : "0",
				"users" : "0",
				"texts" : "0"
			}
		},
		"Test"         : {
			"active"  : 1,
			"db"      : [
			],
			"storage" : [
			]
		}
	},
	"blocks"  : [
		{
			"position" : "top",
			"type"     : "html",
			"index"    : "7625906",
			"title"    : "Testing title",
			"active"   : 0,
			"start"    : 1337627340,
			"expire"   : 0,
			"update"   : 3600,
			"content"  : "<p>Testing content<\/p>"
		},
		{
			"position" : "left",
			"type"     : "html",
			"index"    : "7624258",
			"title"    : "Testing left",
			"active"   : "1",
			"start"    : 1337624220,
			"expire"   : 0,
			"update"   : 3600,
			"content"  : "<p>Testing content left<\/p>"
		}
	]
}
```

#### mirrors : array
Array of all domains, which allowed to access the site.

Contains keys:
* count - Total count
* http - Insecure (http) domains
* https - Secure (https) domains

<a name="events" />
###[Up](#) Events

*$Config* object supports next events:
* System/Config/init/before
* System/Config/init/after
* System/Config/changed

#### System/Config/init/before
This event is called right after loading of system configuration, but before system configuration loading and initialization

#### System/Config/init/after
This event is called right after system configuration initialization

#### System/Config/changed
Fired when system configuration changes

<a name="constants" />
###[Up](#) Constants
`\cs\Config` class have few constants:
* SYSTEM_MODULE
* SYSTEM_THEME

#### SYSTEM_MODULE
System module name (used by system itself)

#### SYSTEM_THEME
System theme name (used by system itself, used in administration interface regardless of system settings)

<a name="module-properties-class" />
###[Up](#) \cs\Config\Module_Properties class

This class has next public methods:
* get()
* set()
* db()
* storage()
* enabled()
* disabled()
* installed()
* uninstalled()

#### get($item : string|string[]) : bool|mixed|mixed[]
Method returns value of stored configuration parameter. If array of parameters is given - associative array of values will be returned. If parameter not found - boolean false will be returned (in case of array - for corresponding parameter).

Also there is simplified way to get single parameter - to get it as property of object:
```php
<?php
$Config         = \cs\Config::instance();
$module_conf    = $Config->module('News');
$posts_per_page = $module_conf->get('posts_per_page');
// Next line makes the same as previous, but looks more natural
$posts_per_page = $module_conf->posts_per_page;
```
#### set($item : array|string, $value = null : mixed|null) : bool
Available only for administrators, for other user will have no effect!

Opposite to `::get()` method, is used to store parameters. If `$item` is string - `$value` should contain value of specified parameter. Also, `$item` may be associative array, as it is returned by `::get()` method, in this case `$value` should be omitted. You are free to store strings, numbers and arrays:
```php
<?php
$Config      = \cs\Config::instance();
$module_conf = $Config->module('News');
$module_conf->set('posts_per_page', 10);
// Next line makes the same as previous, but looks more natural
$module_conf->posts_per_page = 10;
// Set several parameters at once
$module_conf->set([
    'pests_per_page'  => 10,
    'allow_comments'  => 1,
    'array_parameter' => [1, 2, 3]
]);
```
#### db($db_name : string) : int
Is used to get database index by associated identifier, as it is described in *meta.json* section of [Module architecture](/docs/quick-start/Module-architecture.md).
```php
<?php
$Config      = \cs\Config::instance();
$db          = \cs\DB::instance();
$users_db_id = $Config->module('System')->db('users');
$result      = $db->db($users_db_id)->q(
    "SELECT `login`
    FROM `[prefix]users`"
);
```
#### storage($storage_name : string) : int
Is used to get storage index by associated identifier, as it is described in *meta.json* section of [Module architecture](/docs/quick-start/Module-architecture.md).
```php
<?php
$Config           = \cs\Config::instance();
$Storage          = \cs\Storage::instance();
$users_storage_id = $Config->module('System')->storage('images');
$result           = $Storage->storage($users_storage_id)->file_put_contents('test', 123);
```
#### enabled() : bool
Returns boolean `true` if module is enabled, and `false` otherwise

#### disabled() : bool
Returns boolean `true` if module is disabled, and `false` otherwise

#### installed() : bool
Returns boolean `true` if module is installed, and `false` otherwise

#### uninstalled() : bool
Returns boolean `true` if module is uninstalled, and `false` otherwise
