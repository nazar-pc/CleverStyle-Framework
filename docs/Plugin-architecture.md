Every described below element is optional, even empty directory in **components/plugins** will be considered as plugin. You are free to use only features you need.

### File system structure of plugin
* includes
	* css
	* html
	* js
	* map.json
* meta
	* update
* fs.json
* index.php
* license.html / license.txt
* meta.json
* readme.html / readme.txt
* events.php

#### includes/css includes/html includes/js
CSS/Web Components (Polymer elements)/JS files in these directories will be automatically included on necessary pages of website (including dependencies between components), and compressed (if was chosen in configuration)

#### includes/map.json
Not all CSS/HTML/JS files need to be included on all pages of website. This file allows to specify which files and where should be included.
This file affects compressed version of CSS/HTML/JS files, and naturally accounts components dependencies before inclusion on pages.

Example:
```json
{
	"admin/Blogs"	: [
		"admin.css"
	],
	"Blogs"			: [
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

#### meta/update
Contains php files with names, that corresponds to versions of plugin. These files are executed during updating process.

Example of files structure:
* meta/update/1.0.2.php

#### fs.json
This file contains paths of all files of plugin. All paths are relative, relatively to the plugin directory. This file is used during plugin updating in order to make this process correct. File is created automatically during plugin building process.

#### index.php
This file is included on every page, if plugin is enabled. Actually, this is the main file of plugin, usually main work is done here.

#### license.html / license.txt
License file, may be of txt or html format.

#### meta.json
Main description file of plugin. This file is required for plugin building, in order to be able to build plugin package. Example of meta.json file for plugin:
```json
{
	"package"	: "TinyMCE",
	"category"	: "plugins",
	"version"	: "4.0.1-cs4",
	"description"	: "TinyMCE is a platform independent web based Javascript HTML WYSIWYG editor control.",
	"author"	: "Moxiecode Systems AB",
	"website"	: "www.tinymce.com",
	"license"	: "GNU Lesser General Public License 2.1",
	"provide"	: [
		"editor",
		"seditor",
		"ieditor"
	],
	"multilingual"	: [
		"interface",
		"content"
	],
	"languages"	: [
		"English",
		"Русский",
		"Українська"
	]
}
```
Some properties are not obvious:
* provide - allows to specify a set of features, provided by this plugin. If other plugin or module with such feature already installed - system will not allow to install another one in order to eliminate conflicts of functionality.
* multilingual - just hint, which level of multilingual capabilities is supported by plugin. Just interface translations, or event multilingual content support.

If this file exists it should have at least next properties:
* package - package name, should be the same as plugin directory name
* category - always *plugins* for plugins
* version - package version in order to distinguish different versions of the same plugin
* description - short plugin description in few words
* author - plugin author
* license - plugin license
* provide - what functionality plugin provides, might be an array

Other possible properties are:
* website
* multilingual
* languages
* require
* optional
* conflict

[Read about dependencies and conflicts](/docs/Components-dependencies-and-conflicts)

##### require
Specifies plugins and/or modules, that are needed for plugin workability. Format of parameter the same as for *conflict*.

#### readme.html / readme.txt
Readme file with extended description of plugin and some other additional information.

#### events.php
This file is included on every page, even when plugin is not enabled. It is used mainly for [events subscribing](/docs/Events#wiki-subscribing), but also may be used for other purposes.
